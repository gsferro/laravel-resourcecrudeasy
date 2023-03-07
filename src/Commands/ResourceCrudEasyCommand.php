<?php

namespace Gsferro\ResourceCrudEasy\Commands;

//use Illuminate\Console\Command;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Input\InputArgument;
use function Livewire\str;

class ResourceCrudEasyCommand extends GeneratorCommand
{
    private string $entite;
//    private string $pathStubs    = "/stubs/livewire";
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

        $this->line("");
        $this->comment("Preparando a criação da entidade [ {$this->entite} ]");

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
            | Criar controller
            |---------------------------------------------------
            */
            $controller = $this->buildClass($this->entite);


            /*
            |---------------------------------------------------
            | alterar os arquivos apos a criação colocando a Model
            |---------------------------------------------------
            */
            $this->applyModelInView();
            $this->applyModelInClass();

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
    protected function getStub()
    {
        $relativePath = '/stubs/controller.stub';

        return file_exists($customPath = $this->laravel->basePath(trim($relativePath, '/')))
            ? $customPath
            : __DIR__.$relativePath;
    }

    private function applyModelInView()
    {
        $pathViews = resource_path('views');

        $acoes = ["index", "create", "edit"];
        foreach ($acoes as $file) {
            $file = "{$pathViews}\livewire\\".str($this->entite)->kebab()."\\$file.blade.php";

            $this->applyInClass($file);
        }
    }

    private function applyModelInClass()
    {
        $pathLivewire = app_path("Http/Livewire/{$this->entite}");

        $acoes = ["Index", "Create", "Edit"];
        foreach ($acoes as $file) {
            $file = "{$pathLivewire}\\$file.php";

            $this->applyInClass($file);
        }
    }

    private function applyInClass($file)
    {
        return File::exists($file) ? File::put($file, $this->applyReplace($file)) : false;
    }

    private function applyReplace($file)
    {
        $params = [
            '/\[Model\]/'        => "{$this->entite}",
            '/\{Model\}/'        => "{$this->entite}",
            '/\[pathLivewire\]/' => str($this->entite)->kebab(),
            '/\{Import\}/'       => "{$this->entite}Import",
            '/\[Import\]/'       => "{$this->entite}Import",
        ];

        return preg_replace(
            array_keys($params),
            array_values($params),
            file_get_contents("{$file}")
        );
    }

    private function verifyParams()
    {
        $this->createDash   = (bool) ($this->option('dashboard') ?: $this->confirm('Create Dashboard?', true));
        $this->createImport = (bool) ($this->option('import')    ?: $this->confirm('Create Import Excel?', true));
        $this->createModal  = (bool) ($this->option('modal')     ?: $this->confirm('Create Modal?', false));
    }

    private function applyModelInImport()
    {
        $pathLivewire = app_path("Http/Livewire/{$this->entite}/Importacao/Modal.php");

        $this->applyInClass("{$pathLivewire}");
    }
}
