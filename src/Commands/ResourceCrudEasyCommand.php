<?php

namespace Gsferro\ResourceCrudEasy\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Stringable;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Support\Str;

class ResourceCrudEasyCommand extends GeneratorCommand
{
    private string $entite;
    private Stringable $str;
    private bool   $createFactory  = true;
    private bool   $createSeeder   = false;
    private bool   $createMigrate  = true;
    //    private bool   $createService   = false;
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
    protected $signature = 'gsferro:resource-crud {entite : Entite name} {--factory} {--seeder} {--migrate}';

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
        $this->str    = Str::of($this->entite);

        $this->br();
        $this->comment("Preper create Entite [ {$this->entite} ]");
        $this->br();

        /*
        |---------------------------------------------------
        | Questions
        |---------------------------------------------------
        */
        // todo configuração
        $this->verifyParams();

        /*
        |---------------------------------------------------
        | v0.1
        |---------------------------------------------------
        |
        | CRUD:
        |
        | - model
        |    - factory
        |    - seeder
        |    - migrate
        | - controller
        | - Pest
        |    - Unit Model
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
            $this->createModel();
            $this->createFactory();
            $this->createSeeder();
            $this->createMigrate();

            /*
            |---------------------------------------------------
            | Criar controller
            |---------------------------------------------------
            */
            $this->createController();

            /*
            |---------------------------------------------------
            | Criar Tests
            |---------------------------------------------------
            */
            $this->createPestUnitModel();
            $this->createPestUnitController();
            $this->createPestFeatureController();

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

    private function verifyParams()
    {
        $this->createFactory = (bool)($this->option('factory') ? : $this->confirm('Create Factory?', true));
        $this->createSeeder  = (bool)($this->option('seeder')  ? : $this->confirm('Create Seeder?', !$this->createFactory));
        $this->createMigrate = (bool)($this->option('migrate') ? : $this->confirm('Create Migrate?', true));
//        $this->createDash   = (bool) ($this->option('dashboard') ?: $this->confirm('Create Dashboard?', true));
//        $this->createImport = (bool) ($this->option('import')    ?: $this->confirm('Create Import Excel?', true));
//        $this->createModal  = (bool) ($this->option('modal')     ?: $this->confirm('Create Modal?', false));
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
        $params     = [
            '/\{{ class }}/'        => $this->str,
            '/\{{ class_folder }}/' => $this->str->snake(),
            '/\{{ class_title }}/'  => $this->str->snake()->title()->replace('_', ' '),
            '/\{{ model }}/'        => $this->str,

            /*
            |---------------------------------------------------
            | Especifico Models
            |---------------------------------------------------
            */
            '/\{{ class_table }}/' => $this->str->snake()->plural(),

            /*
            |---------------------------------------------------
            | Especifico Route
            |---------------------------------------------------
            */
            '/\{{ class_route_slug }}/' => $this->str->snake()->slug()->plural(),
            
            /*
            |---------------------------------------------------
            | colocar ou não para cada pergunta um comment
            |---------------------------------------------------
            */
            '/\{{ comment_seeder }}/' => $this->createSeeder ? '' : '// ',
            '/\{{ comment_factory }}/' => $this->createFactory ? '' : '// ',
            
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
    |
    | Factory
    | Seeder
    | Migration
    | Police (?)
    |
    */
    private function createModel(): void
    {
        $path     = 'app\Models\\' . $this->entite . '.php';
        $stub     = $this->createFactory ? 'model_factory' : 'model';
        $contents = $this->buildClassEntite($this->entite, $stub);

        $this->put($path, $contents, 'Model created:');
    }

    private function createFactory(): void
    {
        if (!$this->createFactory ) {
            return;
        }

        $name = "{$this->entite}Factory";
        $path = "database/factories/{$name}.php";

        if (! file_exists($path)){
            $this->callSilent("make:factory", [
                "name" => $name,
                "-m"   => $this->entite,
            ]);
        }

        $this->message($path, 'Factory created:');
    }

    private function createSeeder(): void
    {
        if (!$this->createSeeder ) {
            return;
        }

        $path     = 'database\seeders\\' . $this->entite . 'Seeder.php';
        $stub     = 'seeder';
        $contents = $this->buildClassEntite($this->entite, $stub);

        $this->put($path, $contents, 'Seeder created:');
    }

    private function createMigrate(): void
    {
        if (!$this->createMigrate ) {
            return;
        }

        // nome da table
        $arquivo = $this->str->snake().'_table.php';
        // sempre fazer override
        $override      = true;
        // caso exista, pega o nome
        $existsMigrate = null;
        // lista todos as migrates
        $migrations = dir(database_path('migrations'));
        // le toda a pastas
        while ($migration = $migrations->read()) {
            // verifica se a migrate é o arquivo que sera criado
            if (str_contains($migration, $arquivo)) {
                // salva o nome para replace, em caso de override
                $existsMigrate = $migration;
                // pergunta ao usuário se deseja fazer override
                $override = $this->confirm('Already Exists Migrate. Want to replace?', false);
            }

            // caso marque como false, return
            if (!$override) {
                return;
            }
        }
        $migrations->close();
        // o nome sera ou o atual ou novo
        $migrateName = $existsMigrate ?? now()->format('Y_m_d_his') . '_' . $arquivo;
        $path        = 'database\migrations\\'.$migrateName;

        // caso tenha criado o seeder, coloca para executar ao rodar a migrate
        $stub        = $this->createSeeder ? 'migrate_seeder' : 'migrate';
        $contents    = $this->buildClassEntite($this->entite, $stub);

        $this->put($path, $contents, 'Migration created:');
    }

    /*
    |---------------------------------------------------
    | Criar Controller
    |---------------------------------------------------
    */
    private function createController(): void
    {
        $path     = 'app\Http\Controllers\\' . $this->entite . 'Controller.php';
        $contents = $this->buildClassEntite($this->entite, 'controller');

        $this->put($path, $contents, 'Controller created:');
    }

    /*
    |---------------------------------------------------
    | Criar Service
    |---------------------------------------------------
    */

    /*
    |---------------------------------------------------
    | Criar Pest Test
    |---------------------------------------------------
    |
    | Unit from Models
    | Feature from Controller and Services
    |
    */
    private function createPestUnitModel(): void
    {
        $path     = 'tests\Unit\Models\\' . $this->entite . 'Test.php';
        $contents = $this->buildClassEntite($this->entite, 'pest_unit_model');

        $this->makeDirectory($path);
        $this->put($path, $contents, 'PestTest Unit Models created:');
    }

    private function createPestUnitController(): void
    {
        $path     = 'tests\Unit\Controllers\\' . $this->entite . 'ControllerTest.php';
        $contents = $this->buildClassEntite($this->entite, 'pest_unit_controller');

        $this->makeDirectory($path);
        $this->put($path, $contents, 'PestTest Unit Controllers created:');
    }

    private function createPestFeatureController(): void
    {
        $path     = 'tests\Feature\Controllers\\' . $this->entite . 'ControllerTest.php';
        $contents = $this->buildClassEntite($this->entite, 'pest_feature_controller');

        $this->makeDirectory($path);
        $this->put($path, $contents, 'PestTest Feature Controllers created:');
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

    private function put($path, $contents, string $message)
    {
        $this->files->put("{$path}", "{$contents}");

        $this->message($path, $message);
    }

    private function message($path, string $message)
    {
        $this->comment($message);
        $this->comment("$path");
        $this->br();
    }

    private function br()
    {
        $this->line('');
    }

    // ---------------------------------------------------------------------------------------
    /*
    |---------------------------------------------------
    | GeneratorCommand
    |---------------------------------------------------
    */
    protected function getStub()
    {
        // TODO: Implement getStub() method.
    }
}
