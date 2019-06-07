<?php namespace Ghobaty\Billing\Tests;

use Ghobaty\Billing\BillingServiceProvider;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return [BillingServiceProvider::class];
    }
}
