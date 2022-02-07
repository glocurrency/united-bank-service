<?php

namespace GloCurrency\UnitedBank;

use BrokeYourBike\UnitedBank\Interfaces\ConfigInterface;

final class Config implements ConfigInterface
{
    private function getAppConfigValue(string $key): string
    {
        $value = \Illuminate\Support\Facades\Config::get("services.united_bank.api.$key");
        return is_string($value) ? $value : '';
    }

    public function getUrl(): string
    {
        return $this->getAppConfigValue('url');
    }

    public function getToken(): string
    {
        return $this->getAppConfigValue('token');
    }

    public function getClientId(): string
    {
        return $this->getAppConfigValue('client_id');
    }

    public function getClientName(): string
    {
        return $this->getAppConfigValue('client_name');
    }

    public function getUsername(): string
    {
        return $this->getAppConfigValue('username');
    }

    public function getPassword(): string
    {
        return $this->getAppConfigValue('password');
    }
}
