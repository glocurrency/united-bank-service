<?php

namespace GloCurrency\UnitedBank\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use GloCurrency\UnitedBank\Models\RoutingTag;
use GloCurrency\MiddlewareBlocks\Enums\TransactionTypeEnum as MTransactionTypeEnum;

class RoutingTagFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = RoutingTag::class;

    /**
     * Define the model's default state.
     *
     * @return array<string,mixed>
     */
    public function definition()
    {
        return [
            'id' => $this->faker->uuid(),
            'tag' => $this->faker->word(),
            'country_code' => $this->faker->countryISOAlpha3(),
            'transaction_type' => MTransactionTypeEnum::BANK,
        ];
    }
}
