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
    private string     $entite;
    private Stringable $str;
    private bool       $useFactory = true;
    private bool       $useSeeder  = false;
    private bool       $useMigrate = true;
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

    private function messageWellcome()
    {
        $this->br();
        $this->comment("  _____                                                         _____                      _
 |  __ \                                                       / ____|                    | |
 | |__) |   ___   ___    ___    _   _   _ __    ___    ___    | |       _ __   _   _    __| |
 |  _  /   / _ \ / __|  / _ \  | | | | | '__|  / __|  / _ \   | |      | '__| | | | |  / _` |
 | | \ \  |  __/ \__ \ | (_) | | |_| | | |    | (__  |  __/   | |____  | |    | |_| | | (_| |
 |_|  \_\  \___| |___/  \___/   \__,_| |_|     \___|  \___|    \_____| |_|     \__,_|  \__,_|

                                                                                             ");
        /*$this->comment(" _____                                                         _____                      _     ______
 |  __ \                                                       / ____|                    | |   |  ____|
 | |__) |   ___   ___    ___    _   _   _ __    ___    ___    | |       _ __   _   _    __| |   | |__      __ _   ___   _   _
 |  _  /   / _ \ / __|  / _ \  | | | | | '__|  / __|  / _ \   | |      | '__| | | | |  / _` |   |  __|    / _` | / __| | | | |
 | | \ \  |  __/ \__ \ | (_) | | |_| | | |    | (__  |  __/   | |____  | |    | |_| | | (_| |   | |____  | (_| | \__ \ | |_| |
 |_|  \_\  \___| |___/  \___/   \__,_| |_|     \___|  \___|    \_____| |_|     \__,_|  \__,_|   |______|  \__,_| |___/  \__, |
                                                                                                                         __/ |
                                                                                                                        |___/ ");*/
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

        /*
        |---------------------------------------------------
        | Wellcome package
        |---------------------------------------------------
        */
        $this->messageWellcome();

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
            $this->generateModel();
            $this->generateFactory();
            $this->generateSeeder();
            $this->generateMigrate();

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
            $this->generatePestUnitModel();
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

    private function verifyParams()
    {
        $this->useFactory = (bool)($this->option('factory') ? : $this->confirm('Create Factory?', true));
        $this->useSeeder  = (bool)($this->option('seeder')  ? : $this->confirm('Create Seeder?', !$this->useFactory));
        $this->useMigrate = (bool)($this->option('migrate') ? : $this->confirm('Create Migrate?', true));
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
            '/\{{ comment_seeder }}/' => $this->useSeeder ? '' : '// ',
            '/\{{ comment_factory }}/' => $this->useFactory ? '' : '// ',

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
    private function generateModel(): void
    {
        $path = 'app\Models\\' . $this->entite . '.php';
        $stub = $this->useFactory ? 'model_factory' : 'model';
        $this->generate($path, $stub, 'Model');
    }

    private function generateFactory(): void
    {
        if (!$this->useFactory ) {
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

        // TODO by datatable
        // $this->generate($path, 'factory', 'Factory');

        $this->message($path, 'Factory created:');
    }

    private function generateSeeder(): void
    {
        if (!$this->useSeeder ) {
            return;
        }

        $path = 'database\seeders\\' . $this->entite . 'Seeder.php';
        $this->generate($path, 'seeder', 'Seeder');
    }

    private function generateMigrate(): void
    {
        if (!$this->useMigrate) {
            return;
        }

        // nome da table
        $arquivo = $this->str->snake() . '_table.php';
        // sempre fazer override
        $override = true;
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
        $path        = 'database\migrations\\' . $migrateName;

        // caso tenha criado o seeder, coloca para executar ao rodar a migrate
        $stub = $this->useSeeder ? 'migrate_seeder' : 'migrate';
        $this->generate($path, $stub, 'Migration');
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
    private function generatePestUnitModel(): void
    {
        $path     = 'tests\Unit\Models\\' . $this->entite . 'Test.php';
        $this->generate($path, 'pest_unit_model', 'PestTest Unit Models');
    }

    private function generatePestUnitController(): void
    {
        $path = 'tests\Unit\Controllers\\' . $this->entite . 'ControllerTest.php';
        $this->generate($path, 'pest_unit_controller', 'PestTest Unit Controllers');
    }

    private function generatePestFeatureController(): void
    {
        $path = 'tests\Feature\Controllers\\' . $this->entite . 'ControllerTest.php';
        $this->generate($path, 'pest_feature_controller', 'PestTest Feature Controllers');
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
    private function generate(string $path, string $stub, string $message)
    {
        $contents = $this->buildClassEntite($this->entite, $stub);

        $this->makeDirectory($path);
        $this->put($path, $contents, $message);
    }

    protected function buildClassEntite($name, string $stubType)
    {
        $stub = $this->files->get($this->getStubEntite($stubType));

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
        $this->comment(">> $message created:");
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
