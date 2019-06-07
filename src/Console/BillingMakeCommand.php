<?php namespace Ghobaty\Billing\Console;

use Illuminate\Console\Command;

class BillingMakeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:billing
                    {--views : Only scaffold the billing views}
                    {--force : Overwrite existing views by default}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scaffold basic login views and routes';

    /**
     * The views that need to be exported.
     *
     * @var array
     */
    protected $views = [
        'billing/index.stub'     => 'billing/index.blade.php',
        'billing/invoices.stub'  => 'billing/invoices.blade.php',
        'billing/layout.stub'    => 'billing/layout.blade.php',
        'billing/subscribe.stub' => 'billing/subscribe.blade.php',
    ];

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->exportViews();

        if (! $this->option('views')) {
            file_put_contents(
                base_path('routes/web.php'),
                file_get_contents(__DIR__ . '/stubs/make/routes.stub'),
                FILE_APPEND
            );
        }

        $this->info('Billing scaffolding generated successfully.');
    }

    /**
     * Export the authentication views.
     *
     * @return void
     */
    protected function exportViews()
    {
        foreach ($this->views as $key => $value) {
            if (file_exists($view = $this->getViewPath($value)) && ! $this->option('force')) {
                if (! $this->confirm("The [{$value}] view already exists. Do you want to replace it?")) {
                    continue;
                }
            }

            copy(
                __DIR__ . '/stubs/make/views/' . $key,
                $view
            );
        }
    }

    /**
     * Get full view path relative to the app's configured view path.
     *
     * @param string $path
     * @return string
     */
    protected function getViewPath($path)
    {
        return implode(DIRECTORY_SEPARATOR, [
            config('view.paths')[0] ?? resource_path('views'), $path,
        ]);
    }
}
