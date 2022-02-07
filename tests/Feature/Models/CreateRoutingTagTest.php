<?php

namespace GloCurrency\UnitedBank\Tests\Feature\Models\RoutingTag;

use GloCurrency\UnitedBank\Tests\FeatureTestCase;
use GloCurrency\UnitedBank\Models\RoutingTag;
use GloCurrency\MiddlewareBlocks\Enums\TransactionTypeEnum as MTransactionTypeEnum;

class CreateRoutingTagTest extends FeatureTestCase
{
    /** @test */
    public function it_cannot_be_created_with_the_same_country_code_and_transaction_type()
    {
        RoutingTag::factory()->create([
            'country_code' => 'NGA',
            'transaction_type' => MTransactionTypeEnum::BANK,
        ]);

        try {
            RoutingTag::factory()->create([
                'country_code' => 'NGA',
                'transaction_type' => MTransactionTypeEnum::BANK,
            ]);
        } catch (\Throwable $th) {
            $this->assertInstanceOf(\PDOException::class, $th);
            $this->assertCount(1, RoutingTag::where([
                'country_code' => 'NGA',
                'transaction_type' => MTransactionTypeEnum::BANK,
            ])->get());
            return;
        }

        $this->fail('Exception was not thrown');
    }
}
