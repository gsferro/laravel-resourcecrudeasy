<?php

namespace Gsferro\ResourceCrudEasy\Commands;

use Gsferro\ResourceCrudEasy\Services\SchemaBuilderService;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Stringable;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Support\Str;
use function PHPUnit\Framework\isNan;
use function PHPUnit\Framework\isNull;

class ResourceCrudEasyModelCommand extends ResourceCrudEasyGenerateCommand
{
    private bool $useFactory = true;
    private bool $useSeeder  = false;
    private bool $useMigrate = true;
    private ?string $table = null;
    private ?string $connection = null;
    private ?SchemaBuilderService $schema = null;

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
    protected $signature = 'gsferro:resource-crud-model {entite : Entite name} {--table=} {--connection=} {--factory} {--seeder} {--migrate}';

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
            dd(1);
            $this->generateFactory();
            $this->generateSeeder();
            $this->generateMigrate();

            /*
            |---------------------------------------------------
            | Criar Tests
            |---------------------------------------------------
            |
            | - Unit
            |   - Model
            |   - Factory
            |   - Seeder
            |
            */
            $this->generatePestUnitModel();
            $this->generatePestUnitFactory();
            $this->generatePestUnitSeeder();

        } catch (\Exception $e) {
            dump('Ops...', $e->getMessage());
        }
    }

    private function verifyParams()
    {
        $this->verifyDatabase();

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

        $path = 'database\factories\\' . $this->entite . 'Factory.php';
        $this->generate($path, 'factory', 'Factory');
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
        $path     = 'tests\Unit\\' . $this->entite . '\Model\\' . $this->entite . 'Test.php';
        $this->generate($path, 'tests/unit/model', 'PestTest Unit Models');
    }

    private function generatePestUnitFactory(): void
    {
        if (!$this->useFactory) {
            return;
        }
        $path     = 'tests\Unit\\' . $this->entite . '\Factory\\' . $this->entite . 'FactoryTest.php';
        $this->generate($path, 'tests/unit/factory', 'PestTest Unit Factory');
    }

    private function generatePestUnitSeeder(): void
    {
        if (!$this->useSeeder) {
            return;
        }
        $path     = 'tests\Unit\\' . $this->entite . '\Seeder\\' . $this->entite . 'SeederTest.php';
        $this->generate($path, 'tests/unit/seeder', 'PestTest Unit Seeder');
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
        $params = [
            /*
            |---------------------------------------------------
            | Blocos
            |---------------------------------------------------
            */
            '/\{{ bloco_pest_model_use_factory }}/' => $this->applyReplaceBlocoFactory(),

            /*
            |---------------------------------------------------
            | Default not table
            |---------------------------------------------------
            */
            '/\{{ pk_string }}/'   => '',
            '/\{{ BelongsTo }}/'   => '',
            '/\{{ fillable }}/'    => '',
            '/\{{ cast }}/'        => '',
            '/\{{ relations }}/'   => '',
            '/\{{ rules_store }}/'  => '',
            '/\{{ rules_update }}/' => '',
        ];

        /*
        |---------------------------------------------------
        | Especifico Models
        |---------------------------------------------------
        */
        if (!is_null($this->table)) {
            // prepara variaveis
            $pkString  = "";
            $fillable   = "";
            $rulesStore = "";
            $casts      = "";
            $relations  = "";
            
            // buscar colunas
            $columnListings = $this->schema->getColumnListing();

            // para colocar elegantemente no arquivo
            foreach ($columnListings as $column) {
                $str        = "'{$column}'";
                $columnType = $this->schema->getColumnType($column);
                
                // caso a pk seja string
                if ($this->schema->isPrimaryKey($column) && $columnType == 'string') {
                    $pkString = $this->getStubModelPkString($column);
                }
                // não exibe 
                if ($this->schema->isPrimaryKey($column)) {
                    continue;
                }

                // fillable
                $this->interpolate($fillable, "{$str}, ");

                // store
                $store = "{$columnType}";
                if ($this->schema->getDoctrineColumn($column)[ "notnull" ]) {
                    $store .= "|required";
                }
                $this->interpolate($rulesStore, "{$str} => '{$store}', ");

                // casts
                $this->interpolate($casts, "{$str} => '{$columnType}', ");

                // relations
                $foreinsKey = $this->schema->hasForeinsKey($column, true);
                if ($foreinsKey !== false) {
                    $belongto = $this->getStubRelatios('belongto', $foreinsKey);
                    $this->interpolate($relations, $belongto);
                }
            }

            $params = [
                '/\{{ pk_string }}/' => $pkString,
                '/\{{ fillable }}/'  => $fillable,
                '/\{{ cast }}/'      => $casts,
                '/\{{ relations }}/' => $relations,
                '/\{{ BelongsTo }}/' => 'use Illuminate\Database\Eloquent\Relations\BelongsTo;',

                // Nome tabela
                '/\{{ class_table }}/' => $this->table,
                '/\{{ rules_store }}/'  => $rulesStore,
                '/\{{ rules_update }}/' => $casts, // default
            ] + $params;
        }

        return parent::applyReplace(preg_replace(
            array_keys($params),
            array_values($params),
            $stub
        ));
    }

    /*
    |---------------------------------------------------
    | Blocos
    |---------------------------------------------------
    */
    private function applyReplaceBlocoFactory()
    {
        return $this->useFactory
            ? $this->files->get($this->getStubEntite('ifs/pest_model_use_factory'))
            : '';
    }

    /**
     * @param string $type
     * @param array $params
     * @return array|string|string[]|null
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function getStubRelatios(string $type, array $params)
    {
        $stub = $this->files->get($this->getStubEntite('relations/' . $type));
        return preg_replace(
            array_keys($params),
            array_values($params),
            $stub
        );
    }
    
    /**
     * @param string $type
     * @param array $params
     * @return array|string|string[]|null
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function getStubModelPkString(string $column)
    {
        $params = [
            '/\{{ primaryKey }}/' => $column
        ];
        $stub = $this->files->get($this->getStubEntite('ifs/model_pk_string'));
        return preg_replace(
            array_keys($params),
            array_values($params),
            $stub
        );
    }

    /**
     * @param $connection
     * @throws \Throwable
     */
    private function verifyDatabase(): void
    {
        /*
        |---------------------------------------------------
        | From Database
        |---------------------------------------------------
        */
        $this->table      = (bool)$this->option('table') ? $this->option('table') : null;
        $this->connection = (bool)$this->option('connection') ? $this->option('connection') : null;

        throw_if(
            !is_null($this->connection) &&
            !in_array($this->connection, array_keys(config('database.connections'))),
            \Exception::class,
            "connection [ {$this->connection} ] not configured in your config database connections"
        );

        if (!is_null($this->table)) {
            $this->schema = new SchemaBuilderService($this->table, $this->connection);
        }
    }

    /**
     * @param string $string
     * @param string $add
     * @param null $delimiter
     */
    private function interpolate(string &$string, string $add, $delimiter = null)
    {
        $string .= (strlen($string) == 0 ? $delimiter : '        ' ).$add. PHP_EOL;
    }
}
