<?php

namespace Gsferro\ResourceCrudEasy\Traits\Commands;

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

        // TODO by database
        $pathBase = 'resources\views\\' . $entites['str']->snake();
        $views = [
            'index',
            'form',
            'create',
            'edit',
            'filter',
            'datatable_action',
            // 'table',
        ];

        $usePermission = config('resource-crud-easy.use_permissions', true) ? 'permissions/' : '';
        foreach ($views as $view) {
            if ($entites['useDatatable'] && $view == 'index') {
                $view .= '_datatable';
            }

            $pathView = $pathBase . "\\$view.blade.php";
            $this->generate($entite, $pathView, "{$usePermission}views/{$view}", 'View '. ucfirst($view));
        }

        /*
        |---------------------------------------------------
        | WithTable
        |---------------------------------------------------
        */
        $this->generateViewsTable($entite, $pathBase);
    }

    private function generateViewsTable(string $entite, string $pathBase): void
    {
        $entites       = $this->entites[ $entite ];
        $columnListing = $entites[ 'columnListing' ] ?? null;
        if (empty($columnListing)) {
            return;
        }

        $schema = $entites[ 'schema' ];
        $fields = "";
        foreach ($columnListing as $column) {
            // TODO aplicar por cada type
            // $columnType = $schema->getColumnType($column);

            // não exibe
            if ($schema->isPrimaryKey($column) || $column == 'uuid') {
                continue;
            }

            $this->interpolate($fields, $this->getStubField($column, empty($fields)));
        }

        $this->files->put("$pathBase\\form.blade.php", $fields);
    }

    private function getStubField(string $column, bool $first = false)
    {
        $params = [
            '/\{{ column }}/'         => $column,
            '/\{{ column_ucfirst }}/' => ucfirst($column),
            '/\{{ mt-4 }}/'           => $first ? '' : 'mt-4',
        ];
        $stub   = $this->files->get($this->getStubEntite('views/field_form'));
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
        $path = 'tests\Unit\Controllers\\' . $entite . 'ControllerTest.php';
        $this->generate($entite, $path, 'tests/unit/controller', 'PestTest Unit Controllers');
    }

    private function generatePestFeatureController(string $entite): void
    {
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

    private function generatePermissionsSeeder(string $entite): void
    {
//        $entites = $this->entites[ $entite ];
//        if (!$entites['useView']) {
//            return;
//        }

        if (!config('resource-crud-easy.use_permissions', true)) {
            return;
        }

        $path = 'database\seeders\\' . $entite . 'PermissionSeeder.php';
        $this->generate($entite, $path, 'seeder', 'Permissions Seeder');
    }
}