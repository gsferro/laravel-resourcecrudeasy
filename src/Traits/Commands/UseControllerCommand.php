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
        $pathBase = 'resources\views\\';

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
            $index = $pathBase.$this->entites[$entite]['str']->snake() . "\\$view.blade.php";
            $this->generate($entite, $index, "views/{$view}", 'View '. ucfirst($view));
        }
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