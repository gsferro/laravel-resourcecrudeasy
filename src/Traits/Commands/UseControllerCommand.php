<?php

namespace Gsferro\ResourceCrudEasy\Traits\Commands;

use Illuminate\Support\Str;

trait UseControllerCommand
{
    /*
    |---------------------------------------------------
    | Controller
    |---------------------------------------------------
    */
    private function generateController(string $entite): void
    {
        if (!$this->entites[$entite]['useController']) {
            return;
        }

        $stub = $this->entites[ $entite ][ 'useControllerApi' ] ? 'controller_api' : 'controller';
        $path = 'app\Http\Controllers\\' . $entite . 'Controller.php';
        $this->generate($entite, $path, $stub, 'Controllers');
    }

    /*
    |---------------------------------------------------
    | Gerar Views
    |---------------------------------------------------
    */
    private function generateViews(string $entite)
    {
        $entites = $this->entites[ $entite ];
        if (!$entites['useView']) {
            return;
        }

        $useDatatable   = $entites[ 'useDatatable' ];
        $viewsDatatable = $useDatatable ? ['datatable_action', 'filter',] : [];
        $views = array_merge([
            'index',
            'form',
            'create',
            'edit',
            // 'table',
        ], $viewsDatatable);

        $usePermission = config('resource-crud-easy.use_permissions', true) ? 'permissions/' : '';
        $pathBase      = 'resources\views\\' . $entites[ 'str' ]->snake();
        foreach ($views as $view) {
            $pathView = $pathBase . "\\$view.blade.php";
            $stub     = "{$usePermission}views/{$view}";
            if ($useDatatable && $view == 'index') {
                $stub .= '_datatable';
            }

            $this->generate($entite, $pathView, $stub, 'View '. ucfirst($view));
        }

        /*
        |---------------------------------------------------
        | Escreve as views
        |---------------------------------------------------
        */
        $this->generateViewFormTable($entite, $pathBase);

        if ($useDatatable) {
            $this->generateViewFilterTable($entite);
        }
    }

    private function generateViewFormTable(string $entite, string $pathBase): void
    {
        $entites       = $this->entites[ $entite ];
        $columnListing = $entites[ 'columnListing' ] ?? null;
        if (empty($columnListing)) {
            return;
        }

        $schema = $entites[ 'schema' ];
        $fields = "";
        foreach ($columnListing as $column) {
            // não exibe
            if ($schema->isPrimaryKey($column) || $column == 'uuid') {
                continue;
            }

            // TODO aplicar input por cada type
            // $columnType = $schema->getColumnType($column);

            $this->interpolate($fields, $this->getStubFieldString($column, empty($fields)));
        }

        $this->files->put("$pathBase\\form.blade.php", $fields);
    }

    private function generateViewFilterTable(string $entite): void
    {
        $entites       = $this->entites[ $entite ];
        $columnListing = $entites[ 'columnListing' ] ?? null;
        if (empty($columnListing)) {
            return;
        }

        $schema = $entites[ 'schema' ];
        $fields = "";
        foreach ($columnListing as $column) {
            // não exibe
            if ($schema->isPrimaryKey($column) || $column == 'uuid') {
                continue;
            }

            $columnType = $schema->getColumnType($column);
            if (!in_array($columnType, ['string',])) {
                continue;
            }

            $this->interpolate($fields, $this->getStubFieldString($column, empty($fields), '$form'));
        }

        $path     = 'resources/views/' . $entites[ 'str' ]->snake() . '/filter.blade.php';
        $base     = base_path($path);
        $contents = file_get_contents($base);

        $contents = $this->replace([
            '/\{\{-- fields filter --\}\}/' => $fields,
        ], $contents);

        $this->files->put($path, $contents);
    }

    private function getStubFieldString(string $column, bool $first = false, string $var = '$model'): string
    {
        $str     = Str::of($column)->title()->replace('_', ' ');
        $params  = [
            '/\{{ column }}/'         => $column,
            '/\{{ column_title }}/'   => $str,
            '/\{{ mt-4 }}/'           => $first ? '' : 'mt-4',
            '/\{{ field_form_var }}/' => $var,
        ];

        $usePermission = config('resource-crud-easy.use_permissions', true) ? 'permissions/' : '';
        $stub = $this->files->get($this->getStubEntite("{$usePermission}views/field_form_string"));
        return $this->replace($params, $stub);
    }

    /*
    |---------------------------------------------------
    | Criar Service
    |---------------------------------------------------
    |
    | TODO
    |
    */

    /*
    |---------------------------------------------------
    | Criar Pest Test
    |---------------------------------------------------
    |
    | Controller:
    |   - Unit
    |   - Feature
    |
    */
    private function generatePestUnitController(string $entite): void
    {
        if (!$this->entites[$entite]['useController']) {
            return;
        }

        $path = 'tests\Unit\Controllers\\' . $entite . 'ControllerTest.php';
        $this->generate($entite, $path, 'tests/unit/controller', 'PestTest Unit Controllers');
    }

    private function generatePestFeatureController(string $entite): void
    {
        if (!$this->entites[$entite]['useController']) {
            return;
        }

        $path = 'tests\Feature\Controllers\\' . $entite . 'ControllerTest.php';
        $this->generate($entite, $path, 'tests/feature/controller', 'PestTest Feature Controllers');
    }

    /*
    |---------------------------------------------------
    | Publish Route
    |---------------------------------------------------
    */
    private function publishRoute(string $entite): void
    {
        $path         = 'routes/web.php';
        $base          = base_path($path);
        $routeContents = file_get_contents($base);

        if (str_contains($routeContents, $this->entites[$entite]['str']->snake()->slug()->plural())) {
            return ;
        }

        $stub = config('resource-crud-easy.use_permissions', true) ? 'permissions/' : '';
        $stub .= $this->entites[ $entite ][ 'useControllerApi' ] ? 'api' : 'web';
        $contents = $this->buildClassEntite($entite, $stub);

        // increment use controller
        // TODO ta quebrando uma linha a mais, descobrir o pq
        $this->replace([
            '/\<\?php/' => '<?php'. PHP_EOL.PHP_EOL .'use App\Http\Controllers\\'.$entite.'Controller;'
        ], $routeContents);

        // write group route
        $routeContents .= "\n\n".$contents;

        // re-write file route
        $this->put($path, $routeContents, 'Route Web Updated:');
    }

    private function generatePermissions(string $entite): void
    {
        if (!config('resource-crud-easy.use_permissions', true)) {
            return;
        }

        $pathStubBase = 'permissions/';
        // SEEDER
        $path         = 'database\seeders\\' . $entite . 'PermissionSeeder.php';
        $stubSeeder   = $pathStubBase . 'seeder';
        $this->generate($entite, $path, $stubSeeder, 'Permissions Seeder');

        // MIGRATE
        $arquivo     = 'seeder_' . $this->entites[ $entite ][ 'str' ]->snake() . '_permissions.php';
        $migrateName = now()->format('Y_m_d_his') . '_' . $arquivo;
        $path        = 'database\migrations\\' . $migrateName;
        $stubMigrate = $pathStubBase . 'migrate_seeder';
        $this->generate($entite, $path, $stubMigrate, 'Permissions Migration');
    }
}