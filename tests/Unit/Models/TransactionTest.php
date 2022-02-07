<?php

namespace GloCurrency\UnitedBank\Tests\Unit\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use GloCurrency\UnitedBank\Tests\TestCase;
use GloCurrency\UnitedBank\Models\Transaction;
use GloCurrency\UnitedBank\Enums\TransactionStateCodeEnum;
use BrokeYourBike\UnitedBank\Enums\StatusCodeEnum;
use BrokeYourBike\UnitedBank\Enums\ErrorCodeEnum;
use BrokeYourBike\HasSourceModel\SourceModelInterface;
use BrokeYourBike\BaseModels\BaseUuid;
use App\Traits\HasStateHistoryTrait;

class TransactionTest extends TestCase
{
    /** @test */
    public function it_extends_base_model(): void
    {
        $parent = get_parent_class(Transaction::class);

        $this->assertSame(BaseUuid::class, $parent);
    }

    /** @test */
    public function it_uses_soft_deletes(): void
    {
        $usedTraits = class_uses(Transaction::class);

        $this->assertArrayHasKey(SoftDeletes::class, $usedTraits);
    }

    /** @test */
    public function it_implemets_source_model_interface(): void
    {
        $this->assertInstanceOf(SourceModelInterface::class, new Transaction());
    }

    /** @test */
    public function it_returns_amount_as_float(): void
    {
        $transaction = new Transaction();
        $transaction->amount = '10';

        $this->assertIsFloat($transaction->amount);
    }

    /** @test */
    public function it_returns_state_code_as_enum(): void
    {
        $transaction = new Transaction();
        $transaction->setRawAttributes([
            'state_code' => TransactionStateCodeEnum::PAID->value,
        ]);

        $this->assertEquals(TransactionStateCodeEnum::PAID, $transaction->state_code);
    }

    /** @test */
    public function it_returns_error_code_as_enum(): void
    {
        $transaction = new Transaction();
        $transaction->setRawAttributes([
            'error_code' => ErrorCodeEnum::SUCCESS->value,
        ]);

        $this->assertEquals(ErrorCodeEnum::SUCCESS, $transaction->error_code);
    }

    /** @test */
    public function it_returns_status_code_as_enum(): void
    {
        $transaction = new Transaction();
        $transaction->setRawAttributes([
            'status_code' => StatusCodeEnum::SUCCESS->value,
        ]);

        $this->assertEquals(StatusCodeEnum::SUCCESS, $transaction->status_code);
    }
}
