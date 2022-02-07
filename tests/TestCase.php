<?php

namespace GloCurrency\UnitedBank\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use GloCurrency\UnitedBank\Tests\Fixtures\TransactionFixture;
use GloCurrency\UnitedBank\Tests\Fixtures\ProcessingItemFixture;
use GloCurrency\UnitedBank\Tests\Fixtures\BankFixture;
use GloCurrency\UnitedBank\UnitedBankServiceProvider;
use GloCurrency\UnitedBank\UnitedBank;

abstract class TestCase extends OrchestraTestCase
{
    protected function getEnvironmentSetUp($app)
    {
        UnitedBank::useTransactionModel(TransactionFixture::class);
        UnitedBank::useProcessingItemModel(ProcessingItemFixture::class);
        UnitedBank::useBankModel(BankFixture::class);
    }

    protected function getPackageProviders($app)
    {
        return [UnitedBankServiceProvider::class];
    }

    /**
     * Create the HTTP mock for API.
     *
     * @return array<\GuzzleHttp\Handler\MockHandler|\GuzzleHttp\HandlerStack> [$httpMock, $handlerStack]
     */
    protected function mockApiFor(string $class): array
    {
        $httpMock = new \GuzzleHttp\Handler\MockHandler();
        $handlerStack = \GuzzleHttp\HandlerStack::create($httpMock);

        $this->app->when($class)
            ->needs(\GuzzleHttp\ClientInterface::class)
            ->give(function () use ($handlerStack) {
                return new \GuzzleHttp\Client(['handler' => $handlerStack]);
            });

        return [$httpMock, $handlerStack];
    }
}
