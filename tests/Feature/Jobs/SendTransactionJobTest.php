<?php

namespace GloCurrency\UnitedBank\Tests\Feature\Jobs;

use Illuminate\Support\Facades\Event;
use GloCurrency\UnitedBank\Tests\FeatureTestCase;
use GloCurrency\UnitedBank\Models\Transaction;
use GloCurrency\UnitedBank\Jobs\SendTransactionJob;
use GloCurrency\UnitedBank\Exceptions\CreditTransactionException;
use GloCurrency\UnitedBank\Events\TransactionCreatedEvent;
use GloCurrency\UnitedBank\Enums\TransactionStateCodeEnum;
use BrokeYourBike\UnitedBank\Enums\ErrorCodeEnum;
use BrokeYourBike\UnitedBank\Client;

class SendTransactionJobTest extends FeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([
            TransactionCreatedEvent::class,
        ]);
    }

    private function makeAccountInfoResponse(ErrorCodeEnum $errorCode): \GuzzleHttp\Psr7\Response
    {
        return new \GuzzleHttp\Psr7\Response(200, [], '{
            "hsTransactionId": "TPTEST190313021",
            "UBATransactionId": "NGKPYI210203124016234",
            "accountInformation": {
                "responseMessage": "Name Enquiry successful",
                "balanceCurrency": "GHS",
                "responseCode": "' . $errorCode->value . '"
            }
        }');
    }

    /** @test */
    public function it_will_throw_if_state_not_LOCAL_UNPROCESSED(): void
    {
        /** @var Transaction */
        $targetTransaction = Transaction::factory()->create([
            'state_code' => TransactionStateCodeEnum::PAID,
        ]);

        $this->expectExceptionMessage($targetTransaction::class . " state_code `{$targetTransaction->state_code->value}` not allowed");
        $this->expectException(CreditTransactionException::class);

        SendTransactionJob::dispatchSync($targetTransaction);
    }

    /**
     * @test
     * @dataProvider errorCodesProvider
     */
    public function fetch_uba_destination_id_and_receive(ErrorCodeEnum $errorCode, bool $shouldThrow): void
    {
        /** @var Transaction */
        $targetTransaction = Transaction::factory()->create([
            'state_code' => TransactionStateCodeEnum::LOCAL_UNPROCESSED,
        ]);

        [$httpMock, $stack] = $this->mockApiFor(Client::class);

        $httpMock->append($this->makeAccountInfoResponse($errorCode));

        $httpMock->append(new \GuzzleHttp\Psr7\Response(200, [], '{
            "transactionId": "' . $targetTransaction->uba_destination_id . '",
            "hsTransactionId": "' . $targetTransaction->id . '",
            "UBATransactionId": "NGGLCI1234567890",
            "provisionalResponse": {
                "state": "' . ErrorCodeEnum::SUCCESS->value . '",
                "label": "Funds Transfer successful"
            }
        }'));

        try {
            SendTransactionJob::dispatchSync($targetTransaction);
        } catch (CreditTransactionException $e) {
            $this->assertEquals(TransactionStateCodeEnum::ACCOUNT_INFORMATION_RESPONSE_CODE_NOT_SUCCESSFUL, $e->getStateCode());
        } catch (\Throwable $th) {
            $this->fail('Unexpected exception');
        }

        if ($shouldThrow) {
            $this->assertEquals(TransactionStateCodeEnum::ACCOUNT_INFORMATION_RESPONSE_CODE_NOT_SUCCESSFUL, $targetTransaction->fresh()->state_code);
        } else {
            $this->assertEquals(TransactionStateCodeEnum::PAID, $targetTransaction->fresh()->state_code);
        }
    }

    public function errorCodesProvider(): array
    {
        $states = collect(ErrorCodeEnum::cases())
            ->filter(fn($c) => !in_array($c, [
                ErrorCodeEnum::SUCCESS,
            ]))
            ->flatten()
            ->map(fn($c) => [$c, true])
            ->toArray();

        $states[] = [ErrorCodeEnum::SUCCESS, false];

        return $states;
    }

    /** @test */
    public function it_will_throw_if_response_code_is_unexpected(): void
    {
        /** @var Transaction */
        $targetTransaction = Transaction::factory()->create([
            'state_code' => TransactionStateCodeEnum::LOCAL_UNPROCESSED,
        ]);

        [$httpMock, $stack] = $this->mockApiFor(Client::class);
        $httpMock->append($this->makeAccountInfoResponse(ErrorCodeEnum::SUCCESS));
        $httpMock->append(new \GuzzleHttp\Psr7\Response(200, [], '{
            "transactionId": "' . $targetTransaction->uba_destination_id . '",
            "hsTransactionId": "' . $targetTransaction->id . '",
            "UBATransactionId": "NGGLCI1234567890",
            "provisionalResponse": {
                "state": "lol-code",
                "label": "Funds Transfer successful"
            }
        }'));

        try {
            SendTransactionJob::dispatchSync($targetTransaction);
        } catch (\Throwable $th) {
            $this->assertEquals('Unexpected ' . ErrorCodeEnum::class . ': `lol-code`', $th->getMessage());
            $this->assertInstanceOf(CreditTransactionException::class, $th);
        }

        /** @var Transaction */
        $targetTransaction = $targetTransaction->fresh();

        $this->assertEquals(TransactionStateCodeEnum::UNEXPECTED_ERROR_CODE, $targetTransaction->state_code);
    }

    /** @test */
    public function it_can_credit_transaction(): void
    {
        /** @var Transaction */
        $targetTransaction = Transaction::factory()->create([
            'state_code' => TransactionStateCodeEnum::LOCAL_UNPROCESSED,
        ]);

        [$httpMock, $stack] = $this->mockApiFor(Client::class);
        $httpMock->append($this->makeAccountInfoResponse(ErrorCodeEnum::SUCCESS));
        $httpMock->append(new \GuzzleHttp\Psr7\Response(200, [], '{
            "transactionId": "' . $targetTransaction->uba_destination_id . '",
            "hsTransactionId": "' . $targetTransaction->id . '",
            "UBATransactionId": "NGGLCI1234567890",
            "provisionalResponse": {
                "state": "' . ErrorCodeEnum::SUCCESS->value . '",
                "label": "Funds Transfer successful"
            }
        }'));

        SendTransactionJob::dispatchSync($targetTransaction);

        /** @var Transaction */
        $targetTransaction = $targetTransaction->fresh();

        $this->assertEquals(TransactionStateCodeEnum::PAID, $targetTransaction->state_code);
        $this->assertEquals(ErrorCodeEnum::SUCCESS, $targetTransaction->error_code);
        $this->assertSame('Funds Transfer successful', $targetTransaction->error_code_description);
        $this->assertSame('NGGLCI1234567890', $targetTransaction->uba_request_id);
    }
}
