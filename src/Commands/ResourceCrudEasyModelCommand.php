<?php

namespace Gsferro\ResourceCrudEasy\Commands;

use Gsferro\DatabaseSchemaEasy\DatabaseSchemaEasy;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Gsferro\ResourceCrudEasy\Traits\Commands\{WithExistsTableCommand, UseModelCommand, UseControllerCommand, UtilCommand};
use Illuminate\Support\Str;

class ResourceCrudEasyModelCommand extends ResourceCrudEasyGenerateCommand
{
    use WithExistsTableCommand, UseControllerCommand, UseModelCommand, UtilCommand;

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
    protected $name = 'gsferro:resource-crud';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gsferro:resource-crud
    {entity : Entity name}
    {--table=}
    {--connection=}
    {--model-aux}
    {--datatable}
    {--factory}
    {--seeder}
    {--migrate}
    {--controller}
    ';
    // {--view}

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate all files for new Model!';

    public function handle()
    {
        $this->entity = ucfirst($this->argument('entity'));

        /*
        |---------------------------------------------------
        | Wellcome package
        |---------------------------------------------------
        */
        $this->messageWelcome();

        /*
        |---------------------------------------------------
        | Execute generate
        |---------------------------------------------------
        */
        $this->exec($this->entity);
    }

    private function exec(string $entity, ?string $table = null, ?string $connection = null)
    {
        $this->entitys[ $entity ] = [
            'str' => Str::of($entity)
        ];
        // TODO bar progress
        $this->info("Preper to Create [ {$entity} ]:");
        try {
            /*
            |---------------------------------------------------
            | Questions
            |---------------------------------------------------
            |
            | TODO by config
            |
            */
            $this->verifyParams($entity, $table, $connection);
    
            /*
            |---------------------------------------------------
            | v0.1
            |---------------------------------------------------
            |
            | CRUD:
            |
            | - model
            |    - datatable
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

            /*
            |---------------------------------------------------
            | Criar Models
            |---------------------------------------------------
            */
            $this->generateModel($entity);
            $this->generateDatatable($entity);
            $this->generateFactory($entity);
            $this->generateSeeder($entity);
            $this->generateMigrate($entity);

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
            $this->generatePestUnitModel($entity);
            $this->generatePestUnitFactory($entity);
            $this->generatePestUnitSeeder($entity);

            if ($this->entitys[ $entity ][ 'useController' ]) {
                /*
                |---------------------------------------------------
                | Criar controller
                |---------------------------------------------------
                */
                $this->generateController($entity);

                /*
                |---------------------------------------------------
                | Gerar Views
                |---------------------------------------------------
                */
                $this->generateViews($entity);

                /*
                |---------------------------------------------------
                | Criar Tests
                |---------------------------------------------------
                */
                $this->generatePestUnitController($entity);
                $this->generatePestFeatureController($entity);

                /*
                |---------------------------------------------------
                | Publish Route
                |---------------------------------------------------
                */
                $this->publishRoute($entity);

                /*
                |---------------------------------------------------
                | Permissions if config is true
                |---------------------------------------------------
                */
                $this->generatePermissions($entity);
            }

        } catch (ModelNotFoundException $e) {
            $this->comment('Ops...');
            $this->error($e->getMessage());
        } catch (\Exception $e) {
            dump('Ops...', $e->getMessage(), $e->getCode(), $e->getLine() );
        }
    }

    private function verifyParams(string $entity, ?string $table = null, ?string $connection = null)
    {
        $this->verifyDatabase($entity, $table, $connection);

        /*
        |---------------------------------------------------
        | Model
        |---------------------------------------------------
        */
        if (is_null($table)) {
            $modelAux   = (bool)($this->option('model-aux') ? : $this->confirm('Is Auxiliary Model?', false));
            $datatable  = (bool)($this->option('datatable') ? : $this->confirm('Use Datatable?', !$modelAux));
            $factory    = (bool)($this->option('factory') ? : $this->confirm('Create Factory?', !$modelAux));
            $seeder     = (bool)($this->option('seeder') ? : $this->confirm('Create Seeder?', !$factory));
            $migrate    = (bool)($this->option('migrate') ? : $this->confirm('Create Migrate?', true));
            $controller = (bool)($this->option('controller') ? : $this->confirm('Create Controller?', !$modelAux));
        }

        $modelAux   = $modelAux   ?? $this->confirm('Is Auxiliary Model?', false);
        $datatable  = $datatable  ?? $this->confirm('Use Datatable?', true);
        $factory    = $factory    ?? $this->confirm('Create Factory?', true);
        $seeder     = $seeder     ?? $this->confirm('Create Seeder?', !$factory);
        $migrate    = $migrate    ?? $this->confirm('Create Migrate?', true);
        $controller = $controller ?? $this->confirm('Create Controller?', true);

        /*
        |---------------------------------------------------
        | Controller
        |---------------------------------------------------
        */
        $api  = $controller && $this->confirm('The Controller is API?', false);
        $view = $controller && $this->confirm('Create Views?', !$api);
//        $view = $controller ?? ((bool)($this->option('view') ? : $this->confirm('Create Views?', !$api)));

        $this->entitys[ $entity ] += [
            'isAuxModel'       => $modelAux,
            'useDatatable'     => $datatable,
            'useFactory'       => $factory,
            'useSeeder'        => $seeder,
            'useMigrate'       => $migrate,
            'useController'    => $controller,
            'useControllerApi' => $api,
            'useView'          => $view,
        ];
    }

    /*
    |---------------------------------------------------
    | Override
    |---------------------------------------------------
    |
    | Todo melhorar o replace de stub dentro de stub
    |
    */
    protected function applyReplace($stub, string $entity, string $stubType): string
    {
        $params = [
            /*
            |---------------------------------------------------
            | Blocos
            |---------------------------------------------------
            */
            '/\{{ bloco_pest_model_use_factory }}/' => $this->applyReplaceBlocoFactory($entity),

            /*
            |---------------------------------------------------
            | Default not table
            |---------------------------------------------------
            */
            '/\{{ pk_string }}/'           => '',
            '/\{{ timestamps }}/'          => '',
            '/\{{ connection }}/'          => '',
            '/\{{ belongs_to_relation }}/' => '',
            '/\{{ fillable }}/'            => '',
            '/\{{ cast }}/'                => '',
            '/\{{ relations }}/'           => '',
            '/\{{ rules_store }}/'         => '',
            '/\{{ rules_update }}/'        => '',
        ];

        /*
        |---------------------------------------------------
        | if exists table
        |---------------------------------------------------
        */
        $entitysTable = [];
        if (isset($this->entitys[$entity]['table'])) {
            $entitysTable = match ($stubType) {
                'models/model', 'models/model_factory', 'models/model_datatable', 'models/model_factory_datatable' => $this->modelTable($entity),
                'datatables' => $this->datatablesTable($entity),
                'factory' => $this->factoryTable($entity),
                'seeder' => $this->seederTable($entity),
                'migrate_seeder', 'migrate' => $this->migrateTable($entity),
                default => []
            };
        }

        $replaceStub = $this->replace($entitysTable + $params, $stub);
        return parent::applyReplace($replaceStub, $entity, $stubType);
    }

}
