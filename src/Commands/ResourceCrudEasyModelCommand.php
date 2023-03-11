<?php

namespace Gsferro\ResourceCrudEasy\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Stringable;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Support\Str;

class ResourceCrudEasyModelCommand extends ResourceCrudEasyGenerateCommand
{
    private bool       $useFactory = true;
    private bool       $useSeeder  = false;
    private bool       $useMigrate = true;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'gsferro:resource-crud-model';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gsferro:resource-crud-model {entite : Entite name} {--factory} {--seeder} {--migrate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate all files for new Model!';

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
        | - Pest
        |    - Unit Model
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
            | Criar Tests
            |---------------------------------------------------
            */
            $this->generatePestUnitModel();

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
    | Criar Pest Test
    |---------------------------------------------------
    |
    | Unit from Models
    |
    */
    private function generatePestUnitModel(): void
    {
        $path     = 'tests\Unit\Models\\' . $this->entite . 'Test.php';
        $this->generate($path, 'pest_unit_model', 'PestTest Unit Models');
    }
    
    /*
    |---------------------------------------------------
    | Override
    |---------------------------------------------------
    */
    protected function applyReplace($stub)
    {
        $parent = parent::applyReplace($stub);
        $params = [
            /*
            |---------------------------------------------------
            | colocar ou não para cada pergunta um comment
            |---------------------------------------------------
            */
            '/\{{ comment_seeder }}/'  => $this->useSeeder ? '' : '// ',
            '/\{{ comment_factory }}/' => $this->useFactory ? '' : '// ',
        ];

        return preg_replace(
            array_keys($params),
            array_values($params),
            $parent
        );
    }
}
