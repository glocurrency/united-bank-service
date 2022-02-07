<?php

namespace GloCurrency\UnitedBank\Tests\Unit\Enums;

use GloCurrency\UnitedBank\Tests\TestCase;
use GloCurrency\UnitedBank\Enums\TransactionStateCodeEnum;
use GloCurrency\UnitedBank\Enums\ErrorCodeFactory;
use BrokeYourBike\UnitedBank\Enums\ErrorCodeEnum;

class ErrorCodeFactoryTest extends TestCase
{
    /** @test */
    public function it_can_return_transaction_state_code_from_all_values()
    {
        foreach (ErrorCodeEnum::cases() as $value) {
            $this->assertInstanceOf(TransactionStateCodeEnum::class, ErrorCodeFactory::getTransactionStateCode($value));
        }
    }
}
