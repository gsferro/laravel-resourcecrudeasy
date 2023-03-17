<?php

namespace Gsferro\ResourceCrudEasy\Providers;

use Gsferro\ResourceCrudEasy\Commands\ResourceCrudEasyCommand;
use Gsferro\ResourceCrudEasy\Commands\ResourceCrudEasyModelCommand;
use Gsferro\ResourceCrudEasy\Commands\ResourceCrudEasyModelRecursiveCommand;
use Illuminate\Support\ServiceProvider;

class ResourceCrudEasyServiceProvider extends ServiceProvider
{
    public function boot()
    {
        /*
        |---------------------------------------------------
        | commands
        |---------------------------------------------------
        */
        if ($this->app->runningInConsole()) {
            $this->commands([
                ResourceCrudEasyCommand::class,
                ResourceCrudEasyModelCommand::class,
                ResourceCrudEasyModelRecursiveCommand::class,
            ]);
        }
        /*
        |---------------------------------------------------
        | Publish
        |---------------------------------------------------
        */
        /*
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'responseview');
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/gsferro/responseview'),
        ]);
        */

        $this->publishes([
            __DIR__.'/../tests' => base_path('tests'),
        ]);
    }
}
