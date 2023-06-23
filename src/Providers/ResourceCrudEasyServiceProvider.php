<?php

namespace Gsferro\ResourceCrudEasy\Providers;

use Gsferro\ResourceCrudEasy\Commands\{ResourceCrudEasyModelCommand, ResourceCrudEasyChoiceTableCommand};
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Filesystem\Filesystem;

class ResourceCrudEasyServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->command();
        $this->routes();
        $this->publisher();
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
        Blade::component('components.vendor.resource-crud-easy.datatables.datatables-process', 'datatables-process');
        Blade::component('components.vendor.resource-crud-easy.datatables.side-right-filters', 'side-right-filters');
        Blade::component('components.vendor.resource-crud-easy.filter', 'form-filter');

        # Directives
        Blade::directive("FontAwesomeV4", function () {
            return "
                <link href=" . asset('vendor/font-awesome-v4.7.0/css/font-awesome.min.css') . " rel='stylesheet' type='text/css'/>
            ";
        });

        /*
        |---------------------------------------------------
        | TODO virar package
        |---------------------------------------------------
        */
        Blade::directive("ResourceCrudEasyDatatablesPlugin", function () {
            return "
                <link   href=" . asset('vendor/resource-crud-easy/datatables/extra/pagination.css') . " rel='stylesheet' type='text/css'/>
                <link   href=" . asset('vendor/resource-crud-easy/datatables/extra/bootstrap-glyphicons.css') . " rel='stylesheet' type='text/css'/>
                <link   href=" . asset('vendor/resource-crud-easy/datatables/dataTables.bootstrap.css') . " rel='stylesheet' type='text/css'/>
                <link   href=" . asset('vendor/resource-crud-easy/datatables/responsive/dataTables.responsive.min.css') . " rel='stylesheet' type='text/css'/>
                <link   href=" . asset('vendor/resource-crud-easy/datatables/extra/datatables-processing.css') . " rel='stylesheet' type='text/css'/>
                <script src=" . asset('vendor/resource-crud-easy/datatables/dataTables.min.js') . " type=\"text/javascript\"></script>
                <script src=" . asset('vendor/resource-crud-easy/datatables/dataTables.bootstrap.min.js') . " type=\"text/javascript\"></script>
                <script src=" . asset('vendor/resource-crud-easy/datatables/responsive/dataTables.responsive.min.js') . " type=\"text/javascript\"></script>
                <script src=" . asset('vendor/resource-crud-easy/datatables/DataTableLangBR.js') . " type=\"text/javascript\"></script>
                <script src=" . asset('vendor/resource-crud-easy/datatables/DataTableProccessSS.js') . " type=\"text/javascript\"></script>       
            ";
        });

        Blade::directive("ResourceCrudEasyDatatablesExtraCss", function () {
            return "
                <link href=" . asset('vendor/resource-crud-easy/datatables/extra/tablesorter.css') . " rel='stylesheet' type='text/css'/>
            ";
        });

        Blade::directive("ResourceCrudEasyStylesCss", function () {
            return "
                <link href=" . asset('vendor/resource-crud-easy/styles/tag-count.css') . " rel='stylesheet' type='text/css'/>
            ";
        });

        Blade::directive("ResourceCrudEasyPlugins", function () {
            return "
                <script src=" . asset('vendor/resource-crud-easy/plugins/masks/jquery.mask.min.js') . " type=\"text/javascript\"></script>
                <script src=" . asset('vendor/resource-crud-easy/plugins/masks/masks.js') . " type=\"text/javascript\"></script>
            ";
        });

        Blade::directive("ResourceCrudEasyJquery", function () {
            return "
                <script src=" . asset('vendor/resource-crud-easy/plugins/jquery-3.6.4.min.js') . " type=\"text/javascript\"></script>
            ";
        });
    }

    private function publisher(): void
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
            __DIR__ . '/../public/font-awesome-v4.7.0' => public_path('vendor/font-awesome-v4.7.0'),
        ], 'plugins');

        $this->publishes([
            __DIR__ . '/../config/resource-crud-easy.php' => config_path('resource-crud-easy.php'),
        ], 'config');

        (new Filesystem)->copyDirectory(__DIR__ . '/../lang', resource_path('lang'));

        /*
        |---------------------------------------------------
        | vendor/resource-crud-easy
        |---------------------------------------------------
        */
        $this->publishes([
            __DIR__ . '/../public/styles' => public_path('vendor/resource-crud-easy/styles'),
        ], 'styles');

        $this->publishes([
            __DIR__ . '/../public/plugins' => public_path('vendor/resource-crud-easy/plugins'),
        ], 'plugins');

        $this->publishes([
            __DIR__ . '/../public/datatables' => public_path('vendor/resource-crud-easy/datatables'),
        ], 'plugins');

        /*
        |---------------------------------------------------
        | Views
        |---------------------------------------------------
        */
        $this->publishes([
            __DIR__ . '/../views/components' => resource_path('views/components/vendor/resource-crud-easy'),
            __DIR__ . '/../views/layouts'    => resource_path('views/layouts'),
        ], 'views');

        /*
        |---------------------------------------------------
        | Tests
        |---------------------------------------------------
        */
        $this->publishes([
            __DIR__ . '/../tests' => base_path('tests'),
        ], 'tests');

        /*
        |---------------------------------------------------
        | Seeders
        |---------------------------------------------------
        */
        $this->publishes([
            __DIR__ . '/../seeders' => database_path('seeders'),
        ], 'seeder');
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
//                ResourceCrudEasyCommand::class,
                ResourceCrudEasyModelCommand::class,
                ResourceCrudEasyChoiceTableCommand::class,
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
