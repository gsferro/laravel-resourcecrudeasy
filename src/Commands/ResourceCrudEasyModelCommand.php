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
    {entite : Entite name}
    {--table=}
    {--connection=}
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
        $this->entites[ $entite ] = [
            'str' => Str::of($entite)
        ];
        // TODO bar progress
        $this->info("Preper to Create [ {$entite} ]:");
        try {
            /*
            |---------------------------------------------------
            | Questions
            |---------------------------------------------------
            |
            | TODO by config
            |
            */
            $this->verifyParams($entite, $table, $connection);
    
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
            $this->generateModel($entite);
            $this->generateDatatable($entite);
            $this->generateFactory($entite);
            $this->generateSeeder($entite);
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

            if ($this->entites[ $entite ][ 'useController' ]) {
                /*
                |---------------------------------------------------
                | Criar controller
                |---------------------------------------------------
                */
                $this->generateController($entite);

                /*
                |---------------------------------------------------
                | Gerar Views
                |---------------------------------------------------
                */
                $this->generateViews($entite);

                /*
                |---------------------------------------------------
                | Criar Tests
                |---------------------------------------------------
                */
                $this->generatePestUnitController($entite);
                $this->generatePestFeatureController($entite);

                /*
                |---------------------------------------------------
                | Publish Route
                |---------------------------------------------------
                */
                $this->publishRoute($entite);

                /*
                |---------------------------------------------------
                | Permissions if config is true
                |---------------------------------------------------
                */
                $this->generatePermissions($entite);
            }

        } catch (ModelNotFoundException $e) {
            $this->comment('Ops...');
            $this->error($e->getMessage());
        } catch (\Exception $e) {
            dump('Ops...', $e->getMessage(), $e->getCode(), $e->getLine() );
        }
    }

    private function verifyParams(string $entite, ?string $table = null, ?string $connection = null)
    {
        $this->verifyDatabase($entite, $table, $connection);

        /*
        |---------------------------------------------------
        | Model
        |---------------------------------------------------
        */
        if (is_null($table)) {
            $datatable  = (bool)($this->option('datatable') ? : $this->confirm('Use Datatable?', true));
            $factory    = (bool)($this->option('factory') ? : $this->confirm('Create Factory?', true));
            $seeder     = (bool)($this->option('seeder') ? : $this->confirm('Create Seeder?', !$factory));
            $migrate    = (bool)($this->option('migrate') ? : $this->confirm('Create Migrate?', true));
            $controller = (bool)($this->option('controller') ? : $this->confirm('Create Controller?', true));

        }

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

        $this->entites[ $entite ] += [
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
    protected function applyReplace($stub, string $entite, string $stubType)
    {
        $params = [
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
        $entitesTable = [];
        if (isset($this->entites[$entite]['table'])) {
            switch ($stubType) {
                case 'models/model':
                case 'models/model_factory':
                case 'models/model_datatable':
                case 'models/model_factory_datatable':
                    $entitesTable = $this->modelTable($entite);
                break;
                case 'datatables':
                    $entitesTable = $this->datatablesTable($entite);
                break;
                case 'factory':
                    $entitesTable = $this->factoryTable($entite);
                break;
                case 'seeder':
                    $entitesTable = $this->seederTable($entite);
                break;
                case 'migrate_seeder':
                case 'migrate':
                    $entitesTable = $this->migrateTable($entite);
                break;
            }
        }

        $replaceStub = $this->replace($entitesTable + $params, $stub);
        return parent::applyReplace($replaceStub, $entite, $stubType);
    }

}
