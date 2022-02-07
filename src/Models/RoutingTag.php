<?php

namespace GloCurrency\UnitedBank\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use GloCurrency\UnitedBank\Database\Factories\RoutingTagFactory;
use GloCurrency\MiddlewareBlocks\Enums\TransactionTypeEnum as MTransactionTypeEnum;
use BrokeYourBike\BaseModels\BaseUuid;

/**
 * GloCurrency\UnitedBank\Models\RoutingTag
 *
 * @property string $id
 * @property string $tag
 * @property string $country_code
 * @property \GloCurrency\MiddlewareBlocks\Enums\TransactionTypeEnum $transaction_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class RoutingTag extends BaseUuid
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'uba_routing_tags';

    /**
     * @var array<mixed>
     */
    protected $casts = [
        'transaction_type' => MTransactionTypeEnum::class,
    ];

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return RoutingTagFactory::new();
    }
}
