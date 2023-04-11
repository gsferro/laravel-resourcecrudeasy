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
    private function generateController(string $entity): void
    {
        if (!$this->entitys[$entity]['useController']) {
            return;
        }

        $stub = $this->entitys[ $entity ][ 'useControllerApi' ] ? 'controller_api' : 'controller';
        $path = 'app\Http\Controllers\\' . $entity . 'Controller.php';
        $this->generate($entity, $path, $stub, 'Controllers');
    }

    /*
    |---------------------------------------------------
    | Gerar Views
    |---------------------------------------------------
    */
    private function generateViews(string $entity)
    {
        $entitys = $this->entitys[ $entity ];
        if (!$entitys['useView']) {
            return;
        }

        $useDatatable   = $entitys[ 'useDatatable' ];
        $viewsDatatable = $useDatatable ? ['datatable_action', 'filter',] : [];
        $views = array_merge([
            'index',
            'form',
            'create',
            'edit',
            // 'table',
        ], $viewsDatatable);

        $usePermission = config('resource-crud-easy.use_permissions', true) ? 'permissions/' : '';
        $pathBase      = 'resources\views\\' . $entitys[ 'str' ]->snake();
        foreach ($views as $view) {
            $pathView = $pathBase . "\\$view.blade.php";
            $stub     = "{$usePermission}views/{$view}";
            if ($useDatatable && $view == 'index') {
                $stub .= '_datatable';
            }

            $this->generate($entity, $pathView, $stub, 'View '. ucfirst($view));
        }

        /*
        |---------------------------------------------------
        | Escreve as views
        |---------------------------------------------------
        */
        $this->generateViewFormTable($entity, $pathBase);

        if ($useDatatable) {
            $this->generateViewFilterTable($entity);
        }
    }

    private function generateViewFormTable(string $entity, string $pathBase): void
    {
        $entitys       = $this->entitys[ $entity ];
        $columnListing = $entitys[ 'columnListing' ] ?? null;
        if (empty($columnListing)) {
            return;
        }

        $schema = $entitys[ 'schema' ];
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

    private function generateViewFilterTable(string $entity): void
    {
        $entitys       = $this->entitys[ $entity ];
        $columnListing = $entitys[ 'columnListing' ] ?? null;
        if (empty($columnListing)) {
            return;
        }

        $schema = $entitys[ 'schema' ];
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

        $path     = 'resources/views/' . $entitys[ 'str' ]->snake() . '/filter.blade.php';
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
        $stub = $this->files->get($this->getStubEntity("{$usePermission}views/field_form_string"));
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
    private function generatePestUnitController(string $entity): void
    {
        if (!$this->entitys[$entity]['useController']) {
            return;
        }

        $path = 'tests\Unit\Controllers\\' . $entity . 'ControllerTest.php';
        $this->generate($entity, $path, 'tests/unit/controller', 'PestTest Unit Controllers');
    }

    private function generatePestFeatureController(string $entity): void
    {
        if (!$this->entitys[$entity]['useController']) {
            return;
        }

        $path = 'tests\Feature\Controllers\\' . $entity . 'ControllerTest.php';
        $this->generate($entity, $path, 'tests/feature/controller', 'PestTest Feature Controllers');
    }

    /*
    |---------------------------------------------------
    | Publish Route
    |---------------------------------------------------
    */
    private function publishRoute(string $entity): void
    {
        $path         = 'routes/web.php';
        $base          = base_path($path);
        $routeContents = file_get_contents($base);

        if (str_contains($routeContents, $this->entitys[$entity]['str']->snake()->slug()->plural())) {
            return ;
        }

        $stub = config('resource-crud-easy.use_permissions', true) ? 'permissions/' : '';
        $stub .= $this->entitys[ $entity ][ 'useControllerApi' ] ? 'api' : 'web';
        $contents = $this->buildClassEntity($entity, $stub);

        // increment use controller
        // TODO ta quebrando uma linha a mais, descobrir o pq
        $routeContents = $this->replace([
            '/^<\?php\n/' => '<?php'. PHP_EOL.PHP_EOL.'use App\Http\Controllers\\'.$entity.'Controller;'
        ], $routeContents);

        // write group route
        $routeContents .= "\n".$contents."\n";

        // re-write file route
        $this->put($path, $routeContents, 'Route Web Updated:');
    }

    private function generatePermissions(string $entity): void
    {
        if (!config('resource-crud-easy.use_permissions', true)) {
            return;
        }

        $pathStubBase = 'permissions/';
        // SEEDER
        $path         = 'database\seeders\\' . $entity . 'PermissionSeeder.php';
        $stubSeeder   = $pathStubBase . 'seeder';
        $this->generate($entity, $path, $stubSeeder, 'Permissions Seeder');

        // MIGRATE
        $arquivo     = 'seeder_' . $this->entitys[ $entity ][ 'str' ]->snake() . '_permissions.php';
        $migrateName = now()->format('Y_m_d_his') . '_' . $arquivo;
        $path        = 'database\migrations\\' . $migrateName;
        $stubMigrate = $pathStubBase . 'migrate_seeder';
        $this->generate($entity, $path, $stubMigrate, 'Permissions Migration');
    }
}