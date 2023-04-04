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
        $this->command();
        $this->routes();
        $this->publishs();
        $this->blades();
    }

    private function blades(): void
    {
        /*
        |---------------------------------------------------
        | Alias blade
        |---------------------------------------------------
        |
        | Components
        | Directives
        |
        */
        # Components
        Blade::component('components.datatables.datatable-process', 'datatable-process');
        Blade::component('components.datatables.side-right-filters', 'side-right-filters');

        # Directives
        Blade::directive("DatatablesPlugin", function () {
            return "
                <link   href=" . asset('vendor/datatables/extra/pagination.css') . " rel='stylesheet' type='text/css'/>
                <link   href=" . asset('vendor/datatables/dataTables.bootstrap.css') . " rel='stylesheet' type='text/css'/>
                <link   href=" . asset('vendor/datatables/responsive/dataTables.responsive.min.css') . " rel='stylesheet' type='text/css'/>
                <script src=" . asset('vendor/datatables/dataTables.min.js') . " type=\"text/javascript\"></script>
                <script src=" . asset('vendor/datatables/dataTables.bootstrap.min.js') . " type=\"text/javascript\"></script>
                <script src=" . asset('vendor/datatables/responsive/dataTables.responsive.min.js') . " type=\"text/javascript\"></script>
                <script src=" . asset('vendor/datatables/DataTableLangBR.js') . " type=\"text/javascript\"></script>
                <script src=" . asset('vendor/datatables/DataTableProccessSS.js') . " type=\"text/javascript\"></script>       
            ";
        });

        Blade::directive("DatatablesExtraCss", function () {
            return "
                <link href=" . asset('vendor/datatables/extra/tablesorter.css') . " rel='stylesheet' type='text/css'/>
            ";
        });

        Blade::directive("FontAwesomeV4.7.0", function () {
            return "
                <link href=" . asset('vendor/font-awesome-v4.7.0/css/font-awesome.min.css') . " rel='stylesheet' type='text/css'/>
            ";
        });

        Blade::directive("StylesCss", function () {
            return "
                <link href=" . asset('vendor/font-awesome-v4.7.0/css/font-awesome.min.css') . " rel='stylesheet' type='text/css'/>
            ";
        });

        Blade::directive("Plugins", function () {
            return "
                <script src=" . asset('vendor/masks/jquery.mask.min.js') . " type=\"text/javascript\"></script>
                <script src=" . asset('vendor/masks/masks.js') . " type=\"text/javascript\"></script>
            ";
        });
    }

    private function publishs(): void
    {
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
            __DIR__ . '/../public/styles' => public_path('vendor/styles'),
        ], 'styles');

        $this->publishes([
            __DIR__ . '/../public/plugins' => public_path('vendor/plugins'),
        ], 'plugins');

        $this->publishes([
            __DIR__ . '/../public/datatables' => public_path('vendor/datatables'),
        ], 'plugins');

        $this->publishes([
            __DIR__ . '/../public/font-awesome-v4.7.0' => public_path('vendor/font-awesome-v4.7.0'),
        ], 'plugins');

        $this->publishes([
            __DIR__ . '/../views/components' => resource_path('views/components/datatables'),
        ], 'views');

        $this->publishes([
            __DIR__ . '/../tests' => base_path('tests'),
        ], 'tests');
    }

    private function command(): void
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
    }

    private function routes(): void
    {
        /*
        |---------------------------------------------------
        | Route Datatable
        |---------------------------------------------------
        */
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
    }
}
