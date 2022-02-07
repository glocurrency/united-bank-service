<?php

namespace GloCurrency\UnitedBank\Tests\Unit\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use GloCurrency\UnitedBank\Tests\TestCase;
use GloCurrency\UnitedBank\Models\RoutingTag;
use GloCurrency\MiddlewareBlocks\Enums\TransactionTypeEnum as MTransactionTypeEnum;
use BrokeYourBike\BaseModels\BaseUuid;

class RoutingTagTest extends TestCase
{
    /** @test */
    public function it_extends_base_model(): void
    {
        $parent = get_parent_class(RoutingTag::class);

        $this->assertSame(BaseUuid::class, $parent);
    }

    /** @test */
    public function it_uses_soft_deletes(): void
    {
        $usedTraits = class_uses(RoutingTag::class);

        $this->assertArrayHasKey(SoftDeletes::class, $usedTraits);
    }

    /** @test */
    public function it_returns_transaction_type_as_enum(): void
    {
        $routingTag = new RoutingTag();
        $routingTag->setRawAttributes([
            'transaction_type' => MTransactionTypeEnum::BANK->value,
        ]);

        $this->assertEquals(MTransactionTypeEnum::BANK, $routingTag->transaction_type);
    }
}
