<?php

namespace Kennofizet\PackagesCore;

use Illuminate\Support\ServiceProvider;
use Kennofizet\PackagesCore\Middleware\ValidateRewardPlayToken;
use Kennofizet\PackagesCore\Middleware\EnsureUserIsManager;
use Kennofizet\PackagesCore\Middleware\ValidatorRequestMiddleware;
use Kennofizet\PackagesCore\Commands\ManageCoreCommand;

class PackagesCoreServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/Config/packages-core.php',
            'packages-core'
        );
    }

    public function boot()
    {
        // Publish config
        $this->publishes([
            __DIR__ . '/Config/packages-core.php' => config_path('packages-core.php'),
        ], 'packages-core-config');

        // Publish migrations
        $this->publishes([
            __DIR__ . '/Migrations' => database_path('migrations'),
        ], 'packages-core-migrations');

        // Migrations (auto-load)
        $this->loadMigrationsFrom(__DIR__ . '/Migrations');

        // Routes
        $this->loadRoutesFrom(__DIR__ . '/Routes/api.php');

        // Register middleware aliases
        $router = $this->app['router'];
        $router->aliasMiddleware('rewardplay.token', ValidateRewardPlayToken::class);
        $router->aliasMiddleware('rewardplay.manager', EnsureUserIsManager::class);
        $router->aliasMiddleware('rewardplay.validator', ValidatorRequestMiddleware::class);

        // Register core commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                ManageCoreCommand::class,
            ]);
        }
    }
}
