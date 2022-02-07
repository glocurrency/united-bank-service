<?php

namespace GloCurrency\UnitedBank;

use Illuminate\Support\ServiceProvider;
use GloCurrency\UnitedBank\Console\FetchTransactionsUpdateCommand;
use GloCurrency\UnitedBank\Config;
use BrokeYourBike\UnitedBank\Interfaces\ConfigInterface;

class UnitedBankServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerMigrations();
        $this->registerPublishing();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->configure();
        $this->bindConfig();
    }

    /**
     * Setup the configuration for UnitedBank.
     *
     * @return void
     */
    protected function configure()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/united_bank.php', 'services.united_bank'
        );
    }

    /**
     * Bind the UnitedBank logger interface to the UnitedBank logger.
     *
     * @return void
     */
    protected function bindConfig()
    {
        $this->app->bind(ConfigInterface::class, Config::class);
    }

    /**
     * Register the package migrations.
     *
     * @return void
     */
    protected function registerMigrations()
    {
        if (UnitedBank::$runsMigrations && $this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }
    }

    /**
     * Register the package's publishable resources.
     *
     * @return void
     */
    protected function registerPublishing()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/united_bank.php' => $this->app->configPath('united_bank.php'),
            ], 'united-bank-config');

            $this->publishes([
                __DIR__.'/../database/migrations' => $this->app->databasePath('migrations'),
            ], 'united-bank-migrations');
        }
    }
}
