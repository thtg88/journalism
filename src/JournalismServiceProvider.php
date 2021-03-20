<?php

namespace Thtg88\Journalism;

use Illuminate\Support\ServiceProvider;

final class JournalismServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Config
        $this->publishes([
            __DIR__.'/../config/journalism.php' => $this->app->configPath(
                'journalism.php'
            ),
        ], 'journalism-config');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/journalism.php',
            'journalism'
        );
    }
}
