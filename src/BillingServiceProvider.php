<?php namespace Ghobaty\Billing;

use Ghobaty\Billing\Console\BillingMakeCommand;
use Illuminate\Support\ServiceProvider;

class BillingServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @throws \Exception
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/billing.php' => config_path('billing.php'),
        ], 'config');

        \Laravel\Cashier\Cashier::useCurrency(config('billing.currency.code'), config('billing.currency.symbol'));

        if ($this->app->runningInConsole()) {
            $this->commands([
                BillingMakeCommand::class,
            ]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/billing.php', 'billing');
    }
}
