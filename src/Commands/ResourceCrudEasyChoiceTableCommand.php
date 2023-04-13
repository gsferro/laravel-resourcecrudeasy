<?php

namespace Gsferro\ResourceCrudEasy\Commands;

use Gsferro\DatabaseSchemaEasy\DatabaseSchemaEasy;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Gsferro\ResourceCrudEasy\Traits\Commands\{WithExistsTableCommand, UseModelCommand, UseControllerCommand, UtilCommand};
use Illuminate\Support\Str;

class ResourceCrudEasyChoiceTableCommand extends ResourceCrudEasyGenerateCommand
{
//    use WithExistsTableCommand, UseControllerCommand, UseModelCommand, UtilCommand;

    private string              $pathBase;
    private ?string             $connection = null;
    private ?DatabaseSchemaEasy $schema     = null;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'gsferro:resource-choice-table';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gsferro:resource-choice-table {--connection=} ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate all files for new Model!';

    public function handle()
    {
        // cria a pasta se não existir
        $this->pathBase = $this->makeDirectory(resource_path('react'));

        // verifica se passou uma conexão diferente
        $this->connection = (bool)$this->option('connection') ? $this->option('connection') : null;

        /*
        |---------------------------------------------------
        | Wellcome package
        |---------------------------------------------------
        */
//        $this->messageWelcome();

        /*
        |---------------------------------------------------
        | Execute generate
        |---------------------------------------------------
        */
        $this->exec();
    }

    private function exec()
    {
        try {
            $getTables = $this->getTables();
            $choices   = array_merge(['Todos'], $getTables);
            $modulo    = $this->ask('Qual o nome do Modulo?');
            $tables    = $this->choice('Qual tabela voce quer executar?', $choices, null, true, true);

            // caso seja todos, pega as tabelas
            if (current($tables) == 'Todos') {
                $tables = $getTables;
            }

            // gerar progress bar
            $filesBar = $this->output->createProgressBar(count($tables));
            $filesBar->start();

            foreach ($tables as $table) {
                $this->info('');
//                $action = $this->choice('Qual ação deve executar', [
//                    'Gerar views react'
//                ]);

//                if ($action == 'Gerar views react') {
                    $this->generateViewsReact($modulo, $table);
//                }

                $filesBar->advance();
            }
            $filesBar->finish();

            dd($tables);

        } catch (\Exception $e) {
            dump('Ops...', $e->getMessage(), $e->getCode(), $e->getLine() );
        }
    }

    // TODO ir para pacote DatabaseSchemaEasy
    private function getTables(): array
    {
        return DB::connection($this->connection)
            ->getDoctrineSchemaManager()
            ->listTableNames();
    }

    private function generateViewsReact(string $modulo, string $table)
    {
        $schema        = dbSchemaEasy($table, $this->connection);
        $columnListing = $schema->getColumnListing();

        $getColumnType = [];
        foreach ($columnListing as $column) {
            $type = $schema->getColumnType($column) == 'integer' ? 'number' : 'string';
            $getColumnType[ $column ] = $type;
        }

        /*
        |---------------------------------------------------
        | Gerar os modulos
        |---------------------------------------------------
        |
        | config
        | pages
        | store
        | types
        |
        */

        $this->generateConfig($modulo, $table, $getColumnType);
        $this->generatePages($modulo, $table, $getColumnType);
    }

    private function generateConfig(string $modulo, string $table, array $getColumnType): void
    {
        // criando pasta
        $path = $this->makeDirectory($this->pathBase."/configs/".$modulo."/". $table .".ts");
        // change values
        $params = [
          '/\{{ table_name }}/' => $table
        ];
        // busca o stub
        $stub = $this->files->get($this->getStubEntite('views/react/configs/config'));
        // aplica as alterações
        $contents = $this->replace($params, $stub);
        // cria o arquivo
        $this->files->put("{$path}", "{$contents}");
    }

    private function generatePages(string $modulo, string $table, array $getColumnType)
    {
        $schema = dbSchemaEasy($table, $this->connection);
        /*
        |---------------------------------------------------
        | Sub pastas
        |---------------------------------------------------
        |
        | create/index.tsx
        | edit/[uuid].tsx
        | index.tsx
        |
        */

//        $this->generatePageIndex();

        /////////////////////////////////////// Before
        // gerar columas
        $columns                = '';
        $createIndexFormControl = '';
        $indexGrid              = '';
        $indexConst             = '';
        $indexFetchData         = '';
        $indexClearFilter       = '';
        $columnsRequired        = '';

        $columnsDefaultValues = [];
        $filesystem           = $this->files;
        foreach ($getColumnType as $column => $type) {
            $columnOf = Str::of($column);
            
            if ($column == 'uuid') {
                continue;
            }
            
            $isRequired = $schema->getDoctrineColumn($column)[ 'notnull' ];

            /*
            |---------------------------------------------------
            | index.tsx
            |---------------------------------------------------
            */
            $title = $columnOf->title()->replace('_', ' ');
            $paramss    = [
                '/\{{ column }}/'               => $column,
                '/\{{ column_type }}/'          => $type,
                '/\{{ column_camel }}/'         => $columnOf->camel(),
                '/\{{ column_camel_ucfirst }}/' => $columnOf->camel()->ucfirst(),
                '/\{{ column_title }}/'         => $title,
                '/\{{ column_is_required }}/'   => $isRequired ? 'rules={{ required: true }}' : '',
            ];
            
            // index
            $columns        .= $this->replace($paramss, $filesystem->get($this->getStubEntite('views/react/pages/column')));
            $indexGrid      .= $this->replace($paramss, $filesystem->get($this->getStubEntite('views/react/pages/grid')));
            $indexConst     .= $this->replace($paramss, $filesystem->get($this->getStubEntite('views/react/pages/const')));
            $indexFetchData .= $this->replace($paramss, $filesystem->get($this->getStubEntite('views/react/pages/fetch_data')));
            $indexClearFilter .= $this->replace($paramss, $filesystem->get($this->getStubEntite('views/react/pages/clear_filter')));
            
            /*
            |---------------------------------------------------
            | create/index
            |---------------------------------------------------
            */

            if ($schema->isPrimaryKey($column) || $column == 'uuid') {
                continue;
            }
            
            $createIndexFormControl .= $this->replace($paramss, $filesystem->get($this->getStubEntite('views/react/pages/create/form_control')));

            // create/index
            $columnsDefaultValues[$column] = '';

            // is required
            if ($isRequired) {
                $columnsRequired .= "'$column': yup.string().required('{$title} é um campo obrigatório'),".PHP_EOL;
            }
        }
        ///////////////////////////////////////////////////////////////// exec
        $arches = [
            'index.tsx'        => 'index',
            'create/index.tsx' => 'create/index',
//            'edit/index.tsx'   => 'edit/index',
        ];

        foreach ($arches as $arch => $stubPage) {
            // criando pasta
            $path = $this->makeDirectory($this->pathBase . "/pages/" . $modulo . "/" . $table . "/" . $arch);
            // change values
            $tableOf = Str::of($table);

            $params = [
                // base
                '/\{{ modulo }}/'                   => $modulo,
                '/\{{ table_name }}/'               => $table,
                '/\{{ table_singular }}/'           => $tableOf->singular(),
                '/\{{ table_title }}/'              => $tableOf->title()->replace('_', ' '),
                '/\{{ table_name_camel }}/'         => $tableOf->camel(),
                '/\{{ table_name_camel_ucfirst }}/' => $tableOf->camel()->ucfirst(),
                '/\{{ block_column }}/'             => $columns,
                
                // index
                '/\{{ columns_grid }}/'         => trim($indexGrid),
                '/\{{ columns_const }}/'        => trim($indexConst),
                '/\{{ columns_fetch_data }}/'   => trim($indexFetchData),
                '/\{{ columns_clear_filter }}/' => trim($indexClearFilter),

                // create/index
                '/\{{ columns_json }}/'                => json_encode($getColumnType),
                '/\{{ columns_default_values_json }}/' => json_encode($columnsDefaultValues),
                '/\{{ columns_required }}/'            => trim($columnsRequired),
                '/\{{ columns_form_control }}/'        => trim($createIndexFormControl),
            ];

            // busca o stub
            $stub = $filesystem->get($this->getStubEntite('views/react/pages/' . $stubPage));
            // aplica as alterações
            $contents = $this->replace($params, $stub);
            // cria o arquivo
            $filesystem->put("{$path}", "{$contents}");
        }
    }
}
