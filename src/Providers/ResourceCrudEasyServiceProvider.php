<?php

namespace Gsferro\ResourceCrudEasy\Providers;

use Illuminate\Support\ServiceProvider;

class ResourceCrudEasyServiceProvider extends ServiceProvider
{

    public function register() {}
    public function boot()
    {
        /*
        |---------------------------------------------------
        | Publish
        |---------------------------------------------------
        */

        $this->loadViewsFrom(__DIR__.'/resources/views', 'responseview');
        $this->publishes([
            __DIR__.'/resources/views' => resource_path('views/vendor/gsferro/responseview'),
        ]);
    }
}
