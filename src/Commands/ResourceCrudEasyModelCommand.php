<?php

namespace Gsferro\ResourceCrudEasy\Commands;

use Gsferro\DatabaseSchemaEasy\DatabaseSchemaEasy;
use Gsferro\ResourceCrudEasy\Traits\WithExistsTableCommand;
use Illuminate\Support\Str;

class ResourceCrudEasyModelCommand extends ResourceCrudEasyGenerateCommand
{
    use WithExistsTableCommand;

    // classes de apoio
    private bool $useFactory = true;
    private bool $useSeeder  = false;
    private bool $useMigrate = true;

    // exists table
    private ?string             $table         = null;
    private ?string             $connection    = null;
    private ?DatabaseSchemaEasy $schema        = null;
    private array               $columnListing = [];

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

        /*
        |---------------------------------------------------
        | Wellcome package
        |---------------------------------------------------
        */
        $this->messageWellcome();

        /*
        |---------------------------------------------------
        | Execute generate
        |---------------------------------------------------
        */
        $this->exec($this->entite);
    }

    private function exec(string $entite, ?string $table = null, ?string $connection = null)
    {
        // seta
        $this->entites[ $entite ] = [
            'str' => Str::of($entite)
        ];
        $this->info("Preper to Create [ {$entite} ]:");

        /*
        |---------------------------------------------------
        | Questions
        |---------------------------------------------------
        */
        // todo configuração
        $this->verifyParams($entite, $table, $connection);

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
            $this->generateModel($entite);
            $this->generateFactory($entite);
            $this->generateSeeder($entite);
            return ;
            $this->generateMigrate($entite);

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
            $this->generatePestUnitModel($entite);
            $this->generatePestUnitFactory($entite);
            $this->generatePestUnitSeeder($entite);

            /*
            |---------------------------------------------------
            | Write Files Creates
            |---------------------------------------------------
            */
            //            foreach ($this->messages as $message => $file) {
            //                                $this->message($message, )
            //                dump($message, $file);
            //            }

        } catch (\Exception $e) {
            dump('Ops...', $e->getLine());
        }
    }

    private function verifyParams(string $entite, ?string $table = null, ?string $connection = null)
    {
        $this->verifyDatabase($entite, $table, $connection);

        if (is_null($table)) {
            $factory = (bool)($this->option('factory') ?: $this->confirm('Create Factory?', true));
            $seeder  = (bool)($this->option('seeder')  ?: $this->confirm('Create Seeder?', !$factory));
            $migrate = (bool)($this->option('migrate') ?: $this->confirm('Create Migrate?', true));
        }

        $factory = $factory ?? $this->confirm('Create Factory?', true);
        $seeder  = $seeder  ?? $this->confirm('Create Seeder?', !$factory);
        $migrate = $migrate ?? $this->confirm('Create Migrate?', true);

        $this->entites[ $entite ] += [
            'useFactory' => $factory,
            'useSeeder'  => $seeder ,
            'useMigrate' => $migrate,
        ];
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
    private function generateModel(string $entite): void
    {

        $path = 'app\Models\\' . $entite . '.php';
        $stub = $this->entites[$entite]['useFactory'] ? 'model_factory' : 'model';
        $this->generate($entite, $path, $stub, 'Model');
    }

    private function generateFactory(string $entite): void
    {
        if (!$this->entites[$entite]['useFactory'] ) {
            return;
        }

        $path = 'database\factories\\' . $entite . 'Factory.php';
        $this->generate($entite, $path, 'factory', 'Factory');
    }

    private function generateSeeder(string $entite): void
    {
        if (!$this->entites[$entite]['useSeeder'] ) {
            return;
        }

        $path = 'database\seeders\\' . $entite . 'Seeder.php';
        $this->generate($entite, $path, 'seeder', 'Seeder');
    }

    private function generateMigrate(string $entite): void
    {
        if (!$this->entites[$entite]['useMigrate']) {
            return;
        }

        // nome da table
        $arquivo = $this->entites[$entite]['str']->snake() . '_table.php';
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
        $stub = $this->entites[$entite]['useSeeder'] ? 'migrate_seeder' : 'migrate';
        $this->generate($entite, $path, $stub, 'Migration');
    }

    /*
    |---------------------------------------------------
    | Criar Pest Test
    |---------------------------------------------------
    |
    | Unit from Models
    |
    */
    private function generatePestUnitModel(string $entite): void
    {
        $path = 'tests\Unit\\' . $entite . '\Model\\' . $entite . 'Test.php';
        $this->generate($entite, $path, 'tests/unit/model', 'PestTest Unit Models');
    }

    private function generatePestUnitFactory(string $entite): void
    {
        if (!$this->entites[$entite]['useFactory'] ) {
            return;
        }

        $path = 'tests\Unit\\' . $entite . '\Factory\\' . $entite . 'FactoryTest.php';
        $this->generate($entite, $path, 'tests/unit/factory', 'PestTest Unit Factory');
    }

    private function generatePestUnitSeeder(string $entite): void
    {
        if (!$this->entites[$entite]['useSeeder'] ) {
            return;
        }

        $path = 'tests\Unit\\' . $entite . '\Seeder\\' . $entite . 'SeederTest.php';
        $this->generate($entite, $path, 'tests/unit/seeder', 'PestTest Unit Seeder');
    }

    /*
    |---------------------------------------------------
    | Override
    |---------------------------------------------------
    |
    | Todo melhorar o replace de stub dentro de stub
    |
    */
    protected function applyReplace($stub, string $entite, string $stubType)
    {
        $params = [
            /*
            |---------------------------------------------------
            | aplica o relacionamento invertido
            |---------------------------------------------------
            */
            //            '/\{{ HasManys }}/' => '{{ HasManys }}',

            /*
            |---------------------------------------------------
            | Blocos
            |---------------------------------------------------
            */
            '/\{{ bloco_pest_model_use_factory }}/' => $this->applyReplaceBlocoFactory($entite),

            /*
            |---------------------------------------------------
            | Default not table
            |---------------------------------------------------
            */
            '/\{{ pk_string }}/'           => '',
            '/\{{ timestamps }}/'          => '',
            '/\{{ belongs_to_relation }}/' => '',
            '/\{{ fillable }}/'            => '',
            '/\{{ cast }}/'                => '',
            '/\{{ relations }}/'           => '',
            '/\{{ rules_store }}/'         => '',
            '/\{{ rules_update }}/'        => '',
        ];

        /*
        |---------------------------------------------------
        | Especifico Models
        |---------------------------------------------------
        */
        if (!is_null($this->entites[$entite]['table'])) {
            $params = $this->modelTable($entite, $params) + $params;
            //            $params = $this->factoryTable($params);
            //            $params = $this->seederWithExistsTable($params);
            //            $params = $this->migrateWithExistsTable($params);
        }

        $replaceStub = $this->replace($params, $stub);
        return parent::applyReplace($replaceStub, $entite, $stubType);
    }


    /*
    |---------------------------------------------------
    | Blocos
    |---------------------------------------------------
    */
    private function applyReplaceBlocoFactory(string $entite)
    {
        return $this->entites[$entite]['useFactory']
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
        return $this->replace($params, $stub);
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
        return $this->replace($params, $stub);
    }

    /**
     * @param string $entite
     * @param string|null $table
     * @param string|null $connection
     * @throws \Throwable
     */
    private function verifyDatabase(string $entite, ?string $table = null, ?string $connection = null): void
    {
        /*
        |---------------------------------------------------
        | From Database
        |---------------------------------------------------
        */
        // se não tiver conexão, verifica se foi passado via option
        if (is_null($connection)) {
            $connection = (bool)$this->option('connection') ? $this->option('connection') : null;
        }

        throw_if(
            !is_null($connection) &&
            !in_array($connection, array_keys(config('database.connections'))),
            \Exception::class,
            "connection [ $connection ] not configured in your config database connections"
        );

        // se não tiver table, verifica se foi passado via option
        if (is_null($table)) {
            $table = (bool)$this->option('table') ? $this->option('table') : null;
        }

        if (!is_null($table)) {
            $schema        = dbSchemaEasy($table, $connection);
            $columnListing = $schema->getColumnListing();
            
            $this->entites[ $entite ] += [
                'table'         => $table,
                'connection'    => $connection,
                'schema'        => $schema,
                'columnListing' => $columnListing,
            ];
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

    /**
     * quando estiver em uma table que tiver um belongto, vai no relacionamento e aplica o hasMany
     *
     * @param array $foreinsKey
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function applyRelationHasInTableForeingKey(string $entite, array $foreinsKey, string $type = 'has_many')
    {
        // TODO criar qdo não houver?
        $entites = $this->entites[$entite];
        if (!$entites['schema']->hasModelWithTableName($foreinsKey['/\{{ table }}/'])) {
            return;
        }

        // busca o arquivo
        $path = 'app/Models/' . $foreinsKey[ '/\{{ related }}/' ] . '.php';
        $base = base_path($path);

        // pega todo o arquivo
        $fileContents = file_get_contents($base);

        // caso já tenha sido configurado
        $stringable = $entites['str'];
        if (str_contains($fileContents, $stringable->camel().'()')){
            return;
        }

        // prepara o stub
        $hasManyStub = $this->getStubRelatios($type, $foreinsKey + [
                // override
                '/\{{ class }}/'       => $stringable,
                '/\{{ class_camel }}/' => $stringable->camel(),
            ]);
        $params = [
            // relation
            '/\/\/\ \{{ HasManys }}/'    => $hasManyStub,
        ];

        // atualiza mesmo já tendo sido criado
        $this->files->put("{$path}", $this->replace($params, $fileContents));
    }

    /**
     * @param string $columnType
     * @param mixed $column
     * @param string $rulesStore
     * @param string $str
     */
    private function rulesStore(string $columnType, string &$rulesStore, string $str, bool $notNull): void
    {
        // store
        $store = "{$columnType}";
        // proteção contra type
        if ($store == "guid") {
            $store = "uuid";
        }

        if ($notNull) {
            $store .= "|required";
        }
        $this->interpolate($rulesStore, "{$str} => '{$store}', ");
    }
}
