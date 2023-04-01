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
        if (!$this->entites[$entite]['useView']) {
            return;
        }

        // TODO by database
        $pathBase = 'resources\views\\' . $this->entites[$entite]['str']->snake();
        $views = [
            'index',
            'form',
            'create',
            'edit',
            'filter',
            'datatable_action',
            // 'table',
        ];

        foreach ($views as $view) {
            $pathView = $pathBase . "\\$view.blade.php";
            $this->generate($entite, $pathView, "views/{$view}", 'View '. ucfirst($view));
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

        $stub = $this->entites[$entite]['useControllerApi'] ? 'api' : 'web';
        $contents = $this->buildClassEntite($entite, $stub);

        $routeContents .= "\n\n".$contents;
        $this->put($path, $routeContents, 'Route Web Updated:');
    }

}