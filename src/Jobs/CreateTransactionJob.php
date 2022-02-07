<?php

namespace GloCurrency\UnitedBank\Jobs;

use Money\Formatter\DecimalMoneyFormatter;
use Illuminate\Support\Facades\App;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Bus\Queueable;
use GloCurrency\UnitedBank\UnitedBank;
use GloCurrency\UnitedBank\Models\Transaction;
use GloCurrency\UnitedBank\Models\RoutingTag;
use GloCurrency\UnitedBank\Exceptions\CreateTransactionException;
use GloCurrency\UnitedBank\Enums\TransactionStateCodeEnum;
use GloCurrency\MiddlewareBlocks\Enums\TransactionTypeEnum as MTransactionTypeEnum;
use GloCurrency\MiddlewareBlocks\Enums\TransactionStateCodeEnum as MTransactionStateCodeEnum;
use GloCurrency\MiddlewareBlocks\Enums\QueueTypeEnum as MQueueTypeEnum;
use GloCurrency\MiddlewareBlocks\Enums\ProcessingItemStateCodeEnum as MProcessingItemStateCodeEnum;
use GloCurrency\MiddlewareBlocks\Contracts\ProcessingItemInterface as MProcessingItemInterface;
use GloCurrency\MiddlewareBlocks\Contracts\BankInterface as MBankInterface;

class CreateTransactionJob implements ShouldQueue, ShouldBeUnique, ShouldBeEncrypted
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

    private MProcessingItemInterface $processingItem;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(MProcessingItemInterface $processingItem)
    {
        $this->processingItem = $processingItem;
        $this->afterCommit();
        $this->onQueue(MQueueTypeEnum::SERVICES->value);
    }

    /**
     * The unique ID of the job.
     *
     * @return string
     */
    public function uniqueId()
    {
        return $this->processingItem->getId();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $transaction = $this->processingItem->getTransaction();

        if (!$transaction) {
            throw CreateTransactionException::noTransaction($this->processingItem);
        }

        if (MTransactionTypeEnum::BANK !== $transaction->getType()) {
            throw CreateTransactionException::typeNotAllowed($transaction);
        }

        if (MTransactionStateCodeEnum::PROCESSING !== $transaction->getStateCode()) {
            throw CreateTransactionException::stateNotAllowed($transaction);
        }

        /** @var Transaction|null $targetTransaction */
        $targetTransaction = Transaction::firstWhere('transaction_id', $transaction->getId());

        if ($targetTransaction) {
            throw CreateTransactionException::duplicateTargetTransaction($targetTransaction);
        }

        $transactionSender = $transaction->getSender();

        if (!$transactionSender) {
            throw CreateTransactionException::noTransactionSender($transaction);
        }

        $transactionRecipient = $transaction->getRecipient();

        if (!$transactionRecipient) {
            throw CreateTransactionException::noTransactionRecipient($transaction);
        }

        if (!$transactionRecipient->getBankCode()) {
            throw CreateTransactionException::noBankCode($transactionRecipient);
        }

        if (!$transactionRecipient->getBankAccount()) {
            throw CreateTransactionException::noBankAccount($transactionRecipient);
        }

        $bank = (UnitedBank::$bankModel)::firstWhere([
            'country_code' => $transactionRecipient->getCountryCode(),
            'code' => $transactionRecipient->getBankCode(),
        ]);

        if (!$bank instanceof MBankInterface) {
            throw CreateTransactionException::noBank(
                $transactionRecipient->getCountryCode(),
                $transactionRecipient->getBankCode()
            );
        }

        /** @var RoutingTag|null $routingTag */
        $routingTag = RoutingTag::firstWhere([
            'country_code' => $transactionRecipient->getCountryCode(),
            'transaction_type' => $transaction->getType(),
        ]);

        if (!$routingTag) {
            throw CreateTransactionException::noTargetRoutingTag(
                $transactionRecipient->getCountryCode(),
                $transaction->getType()
            );
        }

        /** @var DecimalMoneyFormatter $moneyFormatter */
        $moneyFormatter = App::make(DecimalMoneyFormatter::class);

        Transaction::create([
            'transaction_id' => $transaction->getId(),
            'processing_item_id' => $this->processingItem->getId(),
            'amount' => $moneyFormatter->format($transaction->getOutputAmount()),
            'currency_code' => $transaction->getOutputAmount()->getCurrency()->getCode(),
            'country_code' => $transactionRecipient->getCountryCode(),
            'state_code' => TransactionStateCodeEnum::LOCAL_UNPROCESSED,
            'reference' => $transaction->getReferenceForHumans(),
            'destination_swift_code' => $bank->getSwiftCode(),
            'destination_account_number' => $transactionRecipient->getBankAccount(),
            'source_swift_code' => $bank->getSwiftCode(), // TODO: set source swift code to config
            'sender_name' => $transactionSender->getName(),
            'recipient_name' => $transactionRecipient->getName(),
            'routing_tag' => $routingTag->tag,
        ]);
    }

    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        report($exception);

        if ($exception instanceof CreateTransactionException) {
            $this->processingItem->updateStateCode($exception->getStateCode(), $exception->getStateCodeReason());
            return;
        }

        $this->processingItem->updateStateCode(MProcessingItemStateCodeEnum::EXCEPTION, $exception->getMessage());
    }
}
