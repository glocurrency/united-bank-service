<?php

namespace GloCurrency\UnitedBank\Tests\Fixtures;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use GloCurrency\UnitedBank\Tests\Database\Factories\BankFixtureFactory;
use GloCurrency\MiddlewareBlocks\Contracts\BankInterface as MBankInterface;
use BrokeYourBike\BaseModels\BaseUuid;

class BankFixture extends BaseUuid implements MBankInterface
{
    use HasFactory;

    protected $table = 'banks';

    public function getId(): string
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getSwiftCode(): string
    {
        return $this->code;
    }

    public function getCountryCode(): string
    {
        return $this->country_code;
    }

    public function getName(): string
    {
        return $this->id;
    }

    public function getShortName(): string
    {
        return $this->id;
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return BankFixtureFactory::new();
    }
}
