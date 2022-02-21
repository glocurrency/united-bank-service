<?php

namespace GloCurrency\UnitedBank\Jobs;

use Illuminate\Support\Facades\App;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Bus\Queueable;
use GloCurrency\UnitedBank\Models\Transaction;
use GloCurrency\UnitedBank\Exceptions\CreditTransactionException;
use GloCurrency\UnitedBank\Enums\TransactionStateCodeEnum;
use GloCurrency\UnitedBank\Enums\ErrorCodeFactory;
use GloCurrency\MiddlewareBlocks\Enums\QueueTypeEnum as MQueueTypeEnum;
use BrokeYourBike\UnitedBank\Enums\ErrorCodeEnum;
use BrokeYourBike\UnitedBank\Client;

class SendTransactionJob implements ShouldQueue, ShouldBeUnique, ShouldBeEncrypted
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

    private Transaction $targetTransaction;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Transaction $targetTransaction)
    {
        $this->targetTransaction = $targetTransaction;
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
        return $this->targetTransaction->id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (TransactionStateCodeEnum::LOCAL_UNPROCESSED !== $this->targetTransaction->state_code) {
            throw CreditTransactionException::stateNotAllowed($this->targetTransaction);
        }

        $destinationId = $this->getDestinationId($this->targetTransaction);
        $this->targetTransaction->uba_destination_id = $destinationId;

        try {
            /** @var Client */
            $api = App::make(Client::class);
            $response = $api->creditTransaction($this->targetTransaction);
        } catch (\Throwable $e) {
            report($e);
            throw CreditTransactionException::apiRequestException($e);
        }

        // TODO: if state is `UNKNOWN`, dispatch FetchStateJob
        $errorCode = ErrorCodeEnum::tryFrom((string) $response->state);

        if (!$errorCode) {
            throw CreditTransactionException::unexpectedErrorCode($response->state);
        }

        $transactionStateCode = ErrorCodeFactory::getTransactionStateCode($errorCode);

        $this->targetTransaction->error_code = $errorCode;
        $this->targetTransaction->state_code = $transactionStateCode;
        $this->targetTransaction->uba_request_id = $response->requestId;
        $this->targetTransaction->error_code_description = $response->stateLabel;
        $this->targetTransaction->save();
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

        if ($exception instanceof CreditTransactionException) {
            $this->targetTransaction->update([
                'state_code' => $exception->getStateCode(),
                'state_code_reason' => $exception->getStateCodeReason(),
            ]);
            return;
        }

        $this->targetTransaction->update([
            'state_code' => TransactionStateCodeEnum::LOCAL_EXCEPTION,
            'state_code_reason' => $exception->getMessage(),
        ]);
    }

    /**
     * Retrieve destination ID from the UBA API.
     *
     * @param Transaction $targetTransaction
     * @return string
     *
     * @throws CreditTransactionException
     * @todo consider using multistage jobs, with ExceptionRate limiter
     */
    private function getDestinationId(Transaction $targetTransaction): string
    {
        try {
            /** @var Client */
            $api = App::make(Client::class);
            $response = $api->fetchAccountInformationForTransaction($targetTransaction);
        } catch (\Throwable $e) {
            report($e);
            throw CreditTransactionException::apiRequestException($e);
        }

        if (ErrorCodeEnum::SUCCESS->value === $response->responseCode && !empty($response->requestId)) {
            return $response->requestId;
        }

        throw CreditTransactionException::accountInformationResponseCodeNotSuccessful($response);
    }
}
