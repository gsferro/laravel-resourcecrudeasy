<?php

namespace Gsferro\ResourceCrudEasy\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Support\Str;

class ResourceCrudEasyCommand extends GeneratorCommand
{
    private string $entite;
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
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['entite', InputArgument::REQUIRED, 'The name of the Entite'],
        ];
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->entite = ucfirst($this->argument('entite'));

        $this->br();
        $this->comment("Preper create Entite [ {$this->entite} ]");
        $this->br();

        /*
        |---------------------------------------------------
        | Questions
        |---------------------------------------------------
        */
        // todo configuração
//        $this->verifyParams();

        /*dd( $this->createImport,
            $this->createDash,
            $this->createModal);*/

        /*
        |---------------------------------------------------
        | v0.1
        |---------------------------------------------------
        |
        | CRUD:
        |   - controller
        |   - model
        |       - todo: criar tmb os relacionamentos e models filho
        |   - todo:
        |       - preenchimento model e migration with fields
        |       - criar a pasta da entidade com o form pegando os fields
        |
        */

        try {
            /*$this->call("make:model", [
                "name" => "{$this->entite}",
                "-m"   => "-m"
            ]); # ser executada via blueprint

            $this->call("make:datatable", [
                "name"  => "{$this->entite}/Table",
                "model" => "{$this->entite}"
            ]);

            $this->call("livewire:make", [
                "name"   => "{$this->entite}/Index",
                "--test" => "--test",
                "--stub" => "{$this->pathStubs}/index"
            ]); # com o stub modificado

            $this->call("livewire:make", [
                "name"   => "{$this->entite}/Create",
                "--test" => "--test",
                "--stub" => "{$this->pathStubs}/create"
            ]); # com o stub modificado # todo unificar

            $this->call("livewire:make", [
                "name"   => "{$this->entite}/Edit",
                "--test" => "--test",
                "--stub" => "{$this->pathStubs}/edit"
            ]); # todo unificar

            $this->call("pest:test", [
                "name"   => "{$this->entite}ModelTest",
                "--unit" => "--unit"
            ]); # test model # Todo criar stub

            $this->call("dusk:make", [
                "name" => "{$this->entite}DuskTest"
            ]); # Todo criar stub*/

            /*
            |---------------------------------------------------
            | Criar Models
            |---------------------------------------------------
            */
            $this->createModel();
            
            /*
            |---------------------------------------------------
            | Criar controller
            |---------------------------------------------------
            */
            $this->createController();
            
            /*
            |---------------------------------------------------
            | alterar os arquivos apos a criação colocando a Model
            |---------------------------------------------------
            */
//            $this->applyModelInClass();
//            $this->applyModelInView();

            /*
            |---------------------------------------------------
            | criar dashboard
            |---------------------------------------------------
            */
            /*if ($this->createDash == true) {
                $this->line("");
                $this->comment("Preparando a criação do Dashboard");

                $this->call("livewire:make", [
                    "name"   => "Dashboard/{$this->entite}",
                    "--test" => "--test",
                    "--stub" => "{$this->pathStubs}/dashboard"
                ]);
            }*/

            /*
            |---------------------------------------------------
            | criar modal
            |---------------------------------------------------
            */
            /*if ($this->createModal == true) {
                $this->line("");
                $this->comment("Preparando a criação da Modal");

                $this->call("livewire:make", [
                    "name"   => "{$this->entite}/Modal",
                    "--test" => "--test",
                    "--stub" => "{$this->pathStubs}/modal"
                ]);
            }*/

            /*
            |---------------------------------------------------
            | criar import
            |---------------------------------------------------
            */
            /*if ($this->createImport == true) {
                $this->line("");
                $this->comment("Preparando a criação do Import");

                $this->call("make:import", [
                    "name"    => "{$this->entite}/{$this->entite}Import",
                    "--model" => "{$this->entite}",
                ]);

                $this->call("livewire:make", [
                    "name"   => "{$this->entite}/Importacao/Modal",
                    "--test" => "--test",
                    "--stub" => "{$this->pathStubs}/import"
                ]);

                $this->applyModelInImport();
            }*/

        } catch (\Exception $e) {
            dump('Ops...', $e->getMessage());
        }
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStubEntite(string $type)
    {
        $relativePath = "/../stubs/{$type}.stub";

        return file_exists($customPath = $this->laravel->basePath(trim($relativePath, '/')))
            ? $customPath
            : __DIR__.$relativePath;
    }

    private function applyReplace($stub)
    {
        $str = Str::of($this->entite);
        
        $params     = [
            '/\{{ class }}/'        => $str,
            '/\{{ class_folder }}/' => $str->snake(),
            '/\{{ class_title }}/'  => $str->snake()->title()->replace('_', ' '),
            '/\{{ model }}/'        => $str,
            
            /*
            |---------------------------------------------------
            | Especifico Models
            |---------------------------------------------------
            */
            '/\{{ class_table }}/' => $str->snake()->plural(),
        ];

        return preg_replace(
            array_keys($params),
            array_values($params),
            $stub
        );
    }
    /*
    |---------------------------------------------------
    | Criar Models
    |---------------------------------------------------
    */
    private function createModel()
    {
        $contents = $this->buildClassEntite($this->entite, 'model');
        $path     = 'app\Models\\' . $this->entite . '.php';
        
        $this->put($path, $contents);

        $this->comment('Model created:');
        $this->comment("$path");
        $this->br();
    }
    
    /*
    |---------------------------------------------------
    | Criar Controller
    |---------------------------------------------------
    */
    private function createController()
    {
        $contents = $this->buildClassEntite($this->entite, 'controller');
        $path     = 'app\Http\Controllers\\' . $this->entite . 'Controller.php';
        
        $this->put($path, $contents);
        
        $this->comment('Controller created:');
        $this->comment("$path");
        $this->br();
    }
    
    /*
    |---------------------------------------------------
    | Criar Service
    |---------------------------------------------------
    */

    /*
    |---------------------------------------------------
    | Reuso
    |---------------------------------------------------
    */
    protected function buildClassEntite($name, string $type)
    {
        $stub = $this->files->get($this->getStubEntite($type));

        return $this->replaceClass($stub, $name);
    }

    protected function replaceClass($stub, $name)
    {
        return $this->applyReplace($stub);
    }

    private function put($path, $contents)
    {
        $this->files->put("{$path}", "{$contents}");
    }
    
    protected function getStub()
    {
        // TODO: Implement getStub() method.
    }

    private function br()
    {
        $this->line('');
    }
}
