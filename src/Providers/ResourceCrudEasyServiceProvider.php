<?php

namespace Gsferro\ResourceCrudEasy\Providers;

use Gsferro\ResourceCrudEasy\Commands\ResourceCrudEasyCommand;
use Illuminate\Support\ServiceProvider;

class ResourceCrudEasyServiceProvider extends ServiceProvider
{
    public function boot()
    {
        /*
        |---------------------------------------------------
        | command
        |---------------------------------------------------
        */
        if ($this->app->runningInConsole()) {
            $this->commands([
                ResourceCrudEasyCommand::class,
            ]);
        }
        /*
        |---------------------------------------------------
        | Publish
        |---------------------------------------------------
        */
     /*   $this->loadViewsFrom(__DIR__.'/resources/views', 'responseview');
        $this->publishes([
            __DIR__.'/resources/views' => resource_path('views/vendor/gsferro/responseview'),
        ]);*/
    }
}
