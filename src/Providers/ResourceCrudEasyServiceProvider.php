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

        $this->publishes([
            __DIR__ . '/../public/font-awesome-v4.7.0' => public_path('vendor/font-awesome-v4.7.0'),
        ], 'plugins');

        $this->publishes([
            __DIR__.'/../views/components' => resource_path('views/components/datatables'),
        ], 'views');

        $this->publishes([
            __DIR__.'/../tests' => base_path('tests'),
        ], 'tests');

        /*
        |---------------------------------------------------
        | Alias blade
        |---------------------------------------------------
        */
        Blade::component('components.datatables.datatable-process',  'datatable-process');
        Blade::component('components.datatables.side-right-filters',  'side-right-filters');
        Blade::directive("DatatablesPlugin", function(){
            return "
                <link   href=". asset('vendor/datatables/extra/pagination.css') ." rel='stylesheet' type='text/css'/>
                <link   href=". asset('vendor/datatables/dataTables.bootstrap.css') ." rel='stylesheet' type='text/css'/>
                <link   href=". asset('vendor/datatables/responsive/dataTables.responsive.min.css') ." rel='stylesheet' type='text/css'/>
                <script src=". asset('vendor/datatables/dataTables.min.js') ."></script>
                <script src=". asset('vendor/datatables/dataTables.bootstrap.min.js') ."></script>
                <script src=". asset('vendor/datatables/responsive/dataTables.responsive.min.js') ."></script>
                <script src=". asset('vendor/datatables/DataTableLangBR.js') ."></script>
                <script src=". asset('vendor/datatables/DataTableProccessSS.js') ."></script>       
            ";
        });
        Blade::directive("DatatablesExtraCss", function(){
            return "
                <link href=". asset('vendor/datatables/extra/tablesorter.css') ." rel='stylesheet' type='text/css'/>
            ";
        });
        Blade::directive("FontAwesomeV4.7.0", function(){
            return "
                <link href=". asset('vendor/font-awesome-v4.7.0/css/font-awesome.min.css') ." rel='stylesheet' type='text/css'/>
            ";
        });
    }
}
