<?php

namespace Gsferro\ResourceCrudEasy\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Stringable;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Support\Str;

class ResourceCrudEasyCommand extends ResourceCrudEasyGenerateCommand
{
//    private bool   $useService   = false;
//    private bool   $createDash   = false;
//    private bool   $createModal  = false;
//    private bool   $createImport = false;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'gsferro:resource-crud';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gsferro:resource-crud {entite : Entite name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate all files for new Entite!';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->entite = ucfirst($this->argument('entite'));
        $this->str    = Str::of($this->entite);

        /*
        |---------------------------------------------------
        | Generate Principle
        |---------------------------------------------------
        |
        | CRUD:
        |
        | - model: gsferro:resource-crud-model
        | - controller
        | - Pest
        |    - Unit Model: gsferro:resource-crud-model
        |    - Feature Controller
        | - todo:
        |       - preenchimento model e migration with fields
        |       - criar a pasta da entidade com o form pegando os fields
        | -
        |
        */

        try {
            /*
            |---------------------------------------------------
            | Criar Models
            |---------------------------------------------------
            */
            $this->call('gsferro:resource-crud-model', [
                "entite" => "{$this->entite}",
            ]);

            /*
            |---------------------------------------------------
            | Criar controller
            |---------------------------------------------------
            */
            $this->generateController();

            /*
            |---------------------------------------------------
            | Gerar Views
            |---------------------------------------------------
            */
            $this->generateViews();

            /*
            |---------------------------------------------------
            | Criar Tests
            |---------------------------------------------------
            */
            $this->generatePestUnitController();
            $this->generatePestFeatureController();

            /*
            |---------------------------------------------------
            | Publish Route
            |---------------------------------------------------
            */
            $this->publishRoute();

        } catch (\Exception $e) {
            dump('Ops...', $e->getMessage());
        }
    }

    /*
    |---------------------------------------------------
    | Criar Controller
    |---------------------------------------------------
    */
    private function generateController(): void
    {
        $path = 'app\Http\Controllers\\' . $this->entite . 'Controller.php';
        $this->generate($path, 'controller', 'Controllers');
    }

    /*
    |---------------------------------------------------
    | Gerar Views
    |---------------------------------------------------
    */
    private function generateViews()
    {
        // TODO by database
        $pathBase = 'resources\views\\';

        $views = [
            'index',
            'form',
            'create',
            'edit',
            // 'filter',
            // 'table',
        ];

        foreach ($views as $view) {
            $index = $pathBase.$this->str->snake() . "\\$view.blade.php";
            $this->generate($index, "view_{$view}", 'View '. ucfirst($view));
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
    private function generatePestUnitController(): void
    {
        $path = 'tests\Unit\Controllers\\' . $this->entite . 'ControllerTest.php';
        $this->generate($path, 'tests/unit/controller', 'PestTest Unit Controllers');
    }

    private function generatePestFeatureController(): void
    {
        $path = 'tests\Feature\Controllers\\' . $this->entite . 'ControllerTest.php';
        $this->generate($path, 'tests/feature/controller', 'PestTest Feature Controllers');
    }

    /*
    |---------------------------------------------------
    | Publish Route
    |---------------------------------------------------
    */
    private function publishRoute(): void
    {
        $path         = 'routes/web.php';
        $base          = base_path($path);
        $routeContents = file_get_contents($base);

        if (str_contains($routeContents, $this->str->snake()->slug()->plural())) {
            return ;
        }

        $contents = $this->buildClassEntite($this->entite, 'web');

        $routeContents .= "\n\n".$contents;
        $this->put($path, $routeContents, 'Route Web Updated:');
    }
    
    /*
    |---------------------------------------------------
    | Override
    |---------------------------------------------------
    |
    | Todo melhorar o replace de stub dentro de stub
    |
    */
    protected function applyReplace($stub)
    {
        return parent::applyReplace($stub);
    }
}
