<?php

namespace GloCurrency\UnitedBank\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use GloCurrency\UnitedBank\UnitedBank;
use GloCurrency\UnitedBank\Models\Transaction;
use GloCurrency\UnitedBank\Enums\TransactionStateCodeEnum;

class TransactionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Transaction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string,mixed>
     */
    public function definition()
    {
        return [
            'id' => $this->faker->uuid(),
            'transaction_id' => (UnitedBank::$transactionModel)::factory(),
            'processing_item_id' => (UnitedBank::$processingItemModel)::factory(),
            'state_code' => TransactionStateCodeEnum::LOCAL_UNPROCESSED,
            'reference' => $this->faker->uuid(),
            'destination_swift_code' => $this->faker->swiftBicNumber(),
            'destination_account_number' => $this->faker->numerify('##########'),
            'source_swift_code' => $this->faker->swiftBicNumber(),
            'sender_name' => $this->faker->name(),
            'routing_tag' => $this->faker->word(),
            'amount' => $this->faker->randomFloat(2, 1),
            'country_code' => $this->faker->countryISOAlpha3(),
            'currency_code' => $this->faker->currencyCode(),
        ];
    }
}
