<?php

namespace GloCurrency\UnitedBank\Tests\Feature\Jobs;

use Money\Money;
use Money\Currency;
use Illuminate\Support\Facades\Event;
use GloCurrency\UnitedBank\Tests\Fixtures\BankFixture;
use GloCurrency\UnitedBank\Tests\FeatureTestCase;
use GloCurrency\UnitedBank\Models\Transaction;
use GloCurrency\UnitedBank\Models\RoutingTag;
use GloCurrency\UnitedBank\Jobs\CreateTransactionJob;
use GloCurrency\UnitedBank\Exceptions\CreateTransactionException;
use GloCurrency\UnitedBank\Events\TransactionCreatedEvent;
use GloCurrency\UnitedBank\Enums\TransactionStateCodeEnum;
use GloCurrency\MiddlewareBlocks\Enums\TransactionTypeEnum as MTransactionTypeEnum;
use GloCurrency\MiddlewareBlocks\Enums\TransactionStateCodeEnum as MTransactionStateCodeEnum;
use GloCurrency\MiddlewareBlocks\Contracts\TransactionInterface as MTransactionInterface;
use GloCurrency\MiddlewareBlocks\Contracts\SenderInterface as MSenderInterface;
use GloCurrency\MiddlewareBlocks\Contracts\RecipientInterface as MRecipientInterface;
use GloCurrency\MiddlewareBlocks\Contracts\ProcessingItemInterface as MProcessingItemInterface;

class CreateTransactionJobTest extends FeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([
            TransactionCreatedEvent::class,
        ]);
    }

    /** @test */
    public function it_will_throw_without_transaction(): void
    {
        $processingItem = $this->getMockBuilder(MProcessingItemInterface::class)->getMock();
        $processingItem->method('getTransaction')->willReturn(null);

        $this->expectExceptionMessage("transaction not found");
        $this->expectException(CreateTransactionException::class);

        CreateTransactionJob::dispatchSync($processingItem);
    }

    /** @test */
    public function it_will_throw_if_target_transaction_already_exist(): void
    {
        $transaction = $this->getMockBuilder(MTransactionInterface::class)->getMock();
        $transaction->method('getType')->willReturn(MTransactionTypeEnum::BANK);
        $transaction->method('getStateCode')->willReturn(MTransactionStateCodeEnum::PROCESSING);

        $processingItem = $this->getMockBuilder(MProcessingItemInterface::class)->getMock();
        $processingItem->method('getTransaction')->willReturn($transaction);

        /** @var MTransactionInterface $transaction */
        $targetTransaction = Transaction::factory()->create([
            'transaction_id' => $transaction->getId(),
        ]);

        $this->expectExceptionMessage("cannot be created twice, `{$targetTransaction->id}`");
        $this->expectException(CreateTransactionException::class);

        CreateTransactionJob::dispatchSync($processingItem);
    }

    /** @test */
    public function it_will_throw_without_transaction_sender(): void
    {
        $transaction = $this->getMockBuilder(MTransactionInterface::class)->getMock();
        $transaction->method('getType')->willReturn(MTransactionTypeEnum::BANK);
        $transaction->method('getStateCode')->willReturn(MTransactionStateCodeEnum::PROCESSING);
        $transaction->method('getSender')->willReturn(null);

        $processingItem = $this->getMockBuilder(MProcessingItemInterface::class)->getMock();
        $processingItem->method('getTransaction')->willReturn($transaction);

        $this->expectExceptionMessage('sender not found');
        $this->expectException(CreateTransactionException::class);

        CreateTransactionJob::dispatchSync($processingItem);
    }

    /** @test */
    public function it_will_throw_without_transaction_recipient(): void
    {
        $sender = $this->getMockBuilder(MSenderInterface::class)->getMock();

        $transaction = $this->getMockBuilder(MTransactionInterface::class)->getMock();
        $transaction->method('getType')->willReturn(MTransactionTypeEnum::BANK);
        $transaction->method('getStateCode')->willReturn(MTransactionStateCodeEnum::PROCESSING);
        $transaction->method('getSender')->willReturn($sender);
        $transaction->method('getRecipient')->willReturn(null);

        $processingItem = $this->getMockBuilder(MProcessingItemInterface::class)->getMock();
        $processingItem->method('getTransaction')->willReturn($transaction);

        $this->expectExceptionMessage('recipient not found');
        $this->expectException(CreateTransactionException::class);

        CreateTransactionJob::dispatchSync($processingItem);
    }

    /** @test */
    public function it_will_throw_without_bank_code_in_transaction_recipient(): void
    {
        $sender = $this->getMockBuilder(MSenderInterface::class)->getMock();
        $recipient = $this->getMockBuilder(MRecipientInterface::class)->getMock();
        $recipient->method('getBankCode')->willReturn(null);

        $transaction = $this->getMockBuilder(MTransactionInterface::class)->getMock();
        $transaction->method('getId')->willReturn('1234');
        $transaction->method('getType')->willReturn(MTransactionTypeEnum::BANK);
        $transaction->method('getStateCode')->willReturn(MTransactionStateCodeEnum::PROCESSING);
        $transaction->method('getSender')->willReturn($sender);
        $transaction->method('getRecipient')->willReturn($recipient);

        $processingItem = $this->getMockBuilder(MProcessingItemInterface::class)->getMock();
        $processingItem->method('getTransaction')->willReturn($transaction);

        /** @var MRecipientInterface $recipient */
        $this->expectExceptionMessage("`{$recipient->getId()}` has no `bank_code`");
        $this->expectException(CreateTransactionException::class);

        CreateTransactionJob::dispatchSync($processingItem);
    }

    /** @test */
    public function it_will_throw_without_bank_account_in_transaction_recipient(): void
    {
        $sender = $this->getMockBuilder(MSenderInterface::class)->getMock();
        $recipient = $this->getMockBuilder(MRecipientInterface::class)->getMock();
        $recipient->method('getBankCode')->willReturn($this->faker()->word());
        $recipient->method('getBankAccount')->willReturn(null);

        $transaction = $this->getMockBuilder(MTransactionInterface::class)->getMock();
        $transaction->method('getId')->willReturn('1234');
        $transaction->method('getType')->willReturn(MTransactionTypeEnum::BANK);
        $transaction->method('getStateCode')->willReturn(MTransactionStateCodeEnum::PROCESSING);
        $transaction->method('getSender')->willReturn($sender);
        $transaction->method('getRecipient')->willReturn($recipient);

        $processingItem = $this->getMockBuilder(MProcessingItemInterface::class)->getMock();
        $processingItem->method('getTransaction')->willReturn($transaction);

        /** @var MRecipientInterface $recipient */
        $this->expectExceptionMessage("`{$recipient->getId()}` has no `bank_account`");
        $this->expectException(CreateTransactionException::class);

        CreateTransactionJob::dispatchSync($processingItem);
    }

    /** @test */
    public function it_will_throw_if_bank_not_found_for_transaction_recipient(): void
    {
        $sender = $this->getMockBuilder(MSenderInterface::class)->getMock();
        $recipient = $this->getMockBuilder(MRecipientInterface::class)->getMock();
        $recipient->method('getBankCode')->willReturn($this->faker()->word());
        $recipient->method('getBankAccount')->willReturn($this->faker->numerify('##########'));

        $transaction = $this->getMockBuilder(MTransactionInterface::class)->getMock();
        $transaction->method('getId')->willReturn('1234');
        $transaction->method('getType')->willReturn(MTransactionTypeEnum::BANK);
        $transaction->method('getStateCode')->willReturn(MTransactionStateCodeEnum::PROCESSING);
        $transaction->method('getSender')->willReturn($sender);
        $transaction->method('getRecipient')->willReturn($recipient);

        $processingItem = $this->getMockBuilder(MProcessingItemInterface::class)->getMock();
        $processingItem->method('getTransaction')->willReturn($transaction);

        /** @var MRecipientInterface $recipient */
        $bank = BankFixture::factory()->create([
            'country_code' => $recipient->getCountryCode(),
            'code' => $recipient->getBankCode(),
        ]);
        $bank->delete();

        $this->expectExceptionMessage("for {$bank->country_code}/{$bank->code} not found");
        $this->expectException(CreateTransactionException::class);

        CreateTransactionJob::dispatchSync($processingItem);
    }

    /** @test */
    public function it_will_throw_if_routing_tag_not_found(): void
    {
        $sender = $this->getMockBuilder(MSenderInterface::class)->getMock();
        $recipient = $this->getMockBuilder(MRecipientInterface::class)->getMock();
        $recipient->method('getBankCode')->willReturn($this->faker()->word());
        $recipient->method('getBankAccount')->willReturn($this->faker->numerify('##########'));

        $transaction = $this->getMockBuilder(MTransactionInterface::class)->getMock();
        $transaction->method('getId')->willReturn('1234');
        $transaction->method('getType')->willReturn(MTransactionTypeEnum::BANK);
        $transaction->method('getStateCode')->willReturn(MTransactionStateCodeEnum::PROCESSING);
        $transaction->method('getSender')->willReturn($sender);
        $transaction->method('getRecipient')->willReturn($recipient);

        $processingItem = $this->getMockBuilder(MProcessingItemInterface::class)->getMock();
        $processingItem->method('getTransaction')->willReturn($transaction);

        /**
         * @var MRecipientInterface $recipient
         * @var MTransactionInterface $transaction
        */

        BankFixture::factory()->create([
            'country_code' => $recipient->getCountryCode(),
            'code' => $recipient->getBankCode(),
        ]);

        /** @var RoutingTag */
        $routingTag = RoutingTag::factory()->create([
            'country_code' => $recipient->getCountryCode(),
            'transaction_type' => $transaction->getType(),
        ]);
        $routingTag->delete();

        $this->expectExceptionMessage("RoutingTag for {$recipient->getCountryCode()}/{$transaction->getType()->value} not found");
        $this->expectException(CreateTransactionException::class);

        CreateTransactionJob::dispatchSync($processingItem);
    }

    /** @test */
    public function it_can_create_transaction(): void
    {
        $sender = $this->getMockBuilder(MSenderInterface::class)->getMock();

        $recipient = $this->getMockBuilder(MRecipientInterface::class)->getMock();
        $recipient->method('getBankCode')->willReturn($this->faker()->word());
        $recipient->method('getBankAccount')->willReturn($this->faker->numerify('##########'));

        $transaction = $this->getMockBuilder(MTransactionInterface::class)->getMock();
        $transaction->method('getId')->willReturn('1234');
        $transaction->method('getType')->willReturn(MTransactionTypeEnum::BANK);
        $transaction->method('getStateCode')->willReturn(MTransactionStateCodeEnum::PROCESSING);
        $transaction->method('getSender')->willReturn($sender);
        $transaction->method('getRecipient')->willReturn($recipient);
        $transaction->method('getOutputAmount')->willReturn(new Money('200', new Currency('NGN')));

        $processingItem = $this->getMockBuilder(MProcessingItemInterface::class)->getMock();
        $processingItem->method('getTransaction')->willReturn($transaction);

        /**
         * @var MSenderInterface $sender
         * @var MRecipientInterface $recipient
         * @var MTransactionInterface $transaction
         * @var MProcessingItemInterface $processingItem
        */

        $bank = BankFixture::factory()->create([
            'country_code' => $recipient->getCountryCode(),
            'code' => $recipient->getBankCode(),
        ]);

        /** @var RoutingTag */
        $routingTag = RoutingTag::factory()->create([
            'country_code' => $recipient->getCountryCode(),
            'transaction_type' => $transaction->getType(),
        ]);

        $this->assertNull(Transaction::first());

        CreateTransactionJob::dispatchSync($processingItem);

        $this->assertNotNull($targetTransaction = Transaction::first());
        $this->assertSame($transaction->getId(), $targetTransaction->transaction_id);
        $this->assertSame(2.0, $targetTransaction->amount);
        $this->assertSame($transaction->getOutputAmount()->getCurrency()->getCode(), $targetTransaction->currency_code);
        $this->assertSame($recipient->getCountryCode(), $targetTransaction->country_code);
        $this->assertEquals(TransactionStateCodeEnum::LOCAL_UNPROCESSED, $targetTransaction->state_code);
        $this->assertSame($routingTag->tag, $targetTransaction->routing_tag);
        $this->assertSame($sender->getName(), $targetTransaction->sender_name);
        $this->assertSame($recipient->getName(), $targetTransaction->recipient_name);
        $this->assertSame($bank->getSwiftCode(), $targetTransaction->source_swift_code);
        $this->assertSame($recipient->getBankAccount(), $targetTransaction->destination_account_number);
        $this->assertSame($bank->getSwiftCode(), $targetTransaction->destination_swift_code);
        $this->assertSame($transaction->getReferenceForHumans(), $targetTransaction->reference);
    }
}
