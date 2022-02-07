<?php

namespace GloCurrency\UnitedBank\Tests\Unit\Enums;

use GloCurrency\UnitedBank\Tests\TestCase;
use GloCurrency\UnitedBank\Enums\TransactionStateCodeEnum;
use GloCurrency\MiddlewareBlocks\Enums\ProcessingItemStateCodeEnum as MProcessingItemStateCodeEnum;

class TransactionStateCodeTest extends TestCase
{
    /** @test */
    public function it_can_return_processing_item_state_code_from_all_values()
    {
        foreach (TransactionStateCodeEnum::cases() as $value) {
            $this->assertInstanceOf(MProcessingItemStateCodeEnum::class, $value->getProcessingItemStateCode());
        }
    }
}
