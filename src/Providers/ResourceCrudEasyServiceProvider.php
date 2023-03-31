<?php

namespace Gsferro\ResourceCrudEasy\Providers;

use Gsferro\ResourceCrudEasy\Commands\ResourceCrudEasyCommand;
use Gsferro\ResourceCrudEasy\Commands\ResourceCrudEasyModelCommand;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

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
            ]);
        }

        /*
        |---------------------------------------------------
        | Route Datatable
        |---------------------------------------------------
        */
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

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
            __DIR__ . '/../public/datatables' => public_path('vendor/datatables'),
        ], 'plugins');

        // Alias blade
        Blade::directive("DatatablesPlugin", function(){
            return "
                <link   href=". asset('vendor/datatables/responsive/dataTables.responsive.min.css') ." rel='stylesheet'/>
                <script src=". asset('vendor/datatables/dataTables.min.js') ."></script>
                <script src=". asset('vendor/datatables/dataTables.bootstrap.min.js') ."></script>
                <script src=". asset('vendor/datatables/responsive/dataTables.responsive.min.js') ."></script>
                <script src=". asset('vendor/datatables/DataTableLangBR.js') ."></script>
                <script src=". asset('vendor/datatables/DataTableProccessSS.js') ."></script>       
            ";
        });

        $this->publishes([
            __DIR__.'/../tests' => base_path('tests'),
        ]);
    }
}
