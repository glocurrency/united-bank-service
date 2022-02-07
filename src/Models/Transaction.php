<?php

namespace GloCurrency\UnitedBank\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use GloCurrency\UnitedBank\UnitedBank;
use GloCurrency\UnitedBank\Events\TransactionUpdatedEvent;
use GloCurrency\UnitedBank\Events\TransactionCreatedEvent;
use GloCurrency\UnitedBank\Enums\TransactionStateCodeEnum;
use GloCurrency\UnitedBank\Database\Factories\TransactionFactory;
use GloCurrency\MiddlewareBlocks\Contracts\ModelWithStateCodeInterface as MModelWithStateCodeInterface;
use BrokeYourBike\UnitedBank\Interfaces\TransactionInterface;
use BrokeYourBike\UnitedBank\Enums\StatusCodeEnum;
use BrokeYourBike\UnitedBank\Enums\ErrorCodeEnum;
use BrokeYourBike\HasSourceModel\SourceModelInterface;
use BrokeYourBike\BaseModels\BaseUuid;

/**
 * GloCurrency\UnitedBank\Models\Transaction
 *
 * @property string $id
 * @property string $transaction_id
 * @property string $processing_item_id
 * @property \GloCurrency\UnitedBank\Enums\TransactionStateCodeEnum $state_code
 * @property string|null $state_code_reason
 * @property \BrokeYourBike\UnitedBank\Enums\ErrorCodeEnum|null $error_code
 * @property string|null $error_code_description
 * @property \BrokeYourBike\UnitedBank\Enums\StatusCodeEnum|null $status_code
 * @property string|null $status_code_description
 * @property string $reference
 * @property string $uba_destination_id
 * @property string|null $uba_request_id
 * @property string $destination_swift_code
 * @property string $destination_account_number
 * @property string $source_swift_code
 * @property string $sender_name
 * @property string|null $recipient_name
 * @property string $routing_tag
 * @property string|null $description
 * @property float $amount
 * @property string $country_code
 * @property string $currency_code
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class Transaction extends BaseUuid implements MModelWithStateCodeInterface, SourceModelInterface, TransactionInterface
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'uba_transactions';

    /**
     * @var array<mixed>
     */
    protected $casts = [
        'state_code' => TransactionStateCodeEnum::class,
        'error_code' => ErrorCodeEnum::class,
        'status_code' => StatusCodeEnum::class,
        'amount' => 'double',
    ];

    /**
     * @var array<mixed>
     */
    protected $dispatchesEvents = [
        'created' => TransactionCreatedEvent::class,
        'updated' => TransactionUpdatedEvent::class,
    ];

    public function getStateCode(): TransactionStateCodeEnum
    {
        return $this->state_code;
    }

    public function getStateCodeReason(): ?string
    {
        return $this->state_code_reason;
    }

    public function getReference(): string
    {
        return $this->reference;
    }

    public function getCurrencyCode(): string
    {
        return $this->currency_code;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getSenderName(): string
    {
        return $this->sender_name;
    }

    public function getRecipientName(): ?string
    {
        return $this->recipient_name;
    }

    public function getSourceSwiftCode(): string
    {
        return $this->source_swift_code;
    }

    public function getDestinationId(): string
    {
        return $this->uba_destination_id;
    }

    public function getDestinationAccountNumber(): string
    {
        return $this->destination_account_number;
    }

    public function getDestinationSwiftCode(): string
    {
        return $this->destination_swift_code;
    }

    public function getRoutingTag(): string
    {
        return $this->routing_tag;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * The ProcessingItem that Transaction belong to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function processingItem()
    {
        return $this->belongsTo(UnitedBank::$processingItemModel, 'processing_item_id', 'id');
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return TransactionFactory::new();
    }
}
