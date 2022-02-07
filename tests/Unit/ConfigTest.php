<?php

namespace GloCurrency\UnitedBank\Tests\Unit;

use Illuminate\Foundation\Testing\WithFaker;
use GloCurrency\UnitedBank\Tests\TestCase;
use GloCurrency\UnitedBank\Config;
use BrokeYourBike\UnitedBank\Interfaces\ConfigInterface;

class ConfigTest extends TestCase
{
    use WithFaker;

    /** @test */
    public function it_implemets_config_interface(): void
    {
        $this->assertInstanceOf(ConfigInterface::class, new Config());
    }

    /** @test */
    public function it_will_return_empty_string_if_value_not_found()
    {
        $configPrefix = 'services.united_bank.api';

        // config is empty
        config([$configPrefix => []]);

        $config = new Config();

        $this->assertSame('', $config->getUrl());
        $this->assertSame('', $config->getToken());
        $this->assertSame('', $config->getClientId());
        $this->assertSame('', $config->getClientName());
        $this->assertSame('', $config->getUsername());
        $this->assertSame('', $config->getPassword());
    }

    /** @test */
    public function it_can_return_values()
    {
        $url = $this->faker->url;
        $token = $this->faker->uuid;
        $clientId = $this->faker->uuid;
        $clientName = $this->faker->name();
        $username = $this->faker->userName;
        $password = $this->faker->password();

        $configPrefix = 'services.united_bank.api';

        config(["{$configPrefix}.url" => $url]);
        config(["{$configPrefix}.token" => $token]);
        config(["{$configPrefix}.client_id" => $clientId]);
        config(["{$configPrefix}.client_name" => $clientName]);
        config(["{$configPrefix}.username" => $username]);
        config(["{$configPrefix}.password" => $password]);

        $config = new Config();

        $this->assertSame($url, $config->getUrl());
        $this->assertSame($token, $config->getToken());
        $this->assertSame($clientId, $config->getClientId());
        $this->assertSame($clientName, $config->getClientName());
        $this->assertSame($username, $config->getUsername());
        $this->assertSame($password, $config->getPassword());
    }
}
