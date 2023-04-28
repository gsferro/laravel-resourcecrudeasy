<?php

namespace Gsferro\ResourceCrudEasy\Commands;

use Exception;
use Gsferro\DatabaseSchemaEasy\DatabaseSchemaEasy;
use Gsferro\ResourceCrudEasy\Traits\UseDomains;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ResourceCrudEasyChoiceTableCommand extends ResourceCrudEasyGenerateCommand
{
    use UseDomains;

    private string              $pathBase;
    private string              $modulo;
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
    protected $signature = 'gsferro:resource-choice-table 
    {--connection=} 
    {--table=}
    {--modulo=}
    ';

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
        $this->messageWelcome();

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
            $this->modulo    = $this->option('modulo') ?: $this->ask('Qual o nome do Modulo?');
            $this->info('');
            $this->info("Modulo: {$this->modulo}");
            $this->info('');

            $getTables = $this->getTables();
            $choices   = array_merge(['Todos'], $getTables);
            $tables    = (bool)$this->option('table') ? [$this->option('table')] : $this->choice('Qual tabela voce quer executar?', $choices, null, true, true);

            // caso seja todos, pega as tabelas
            if (current($tables) == 'Todos') {
                $tables = $getTables;
            }

            // gerar progress bar
            $filesBar = $this->output->createProgressBar(count($tables));
            $filesBar->start();

            foreach ($tables as $table) {
//                $this->info('');
//                $action = $this->choice('Qual ação deve executar', [
//                    'Gerar views react'
//                ]);

//                if ($action == 'Gerar views react') {
//                    $this->generateViewsReact($table);
                    $this->generateDomains($table);
//                }

                $filesBar->advance();
            }
            $filesBar->finish();
            $this->info('');
        } catch (Exception $e) {
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

    private function generateViewsReact(string $table)
    {
        $this->info('');
        $this->info("Tabela: {$table}");
        $this->info('');

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

        $this->generateConfig($table);
        $this->generatePages($table, $getColumnType);
    }

    private function generateConfig(string $table): void
    {
        $this->info('');
        $this->info("Configs");

        // gerar progress bar
        $filesBarConfig = $this->output->createProgressBar(1);
        $filesBarConfig->start();

        // criando pasta
        $path = $this->makeDirectory($this->pathBase."/configs/".$this->modulo."/". $table .".ts");
        // change values
        $params = [
            '/\{{ table_name }}/' => $table
        ];
        // busca o stub
        $stub = $this->files->get($this->getStubEntity('views/react/configs/config'));
        // aplica as alterações
        $contents = $this->replace($params, $stub);
        // cria o arquivo
        $this->files->put("{$path}", "{$contents}");

        $filesBarConfig->advance();
        $filesBarConfig->finish();
    }

    private function generatePages(string $table, array $getColumnType)
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
        $setValue               = '';

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
            $paramsBase    = [
                '/\{{ column }}/'               => $column,
                '/\{{ column_type }}/'          => $type,
                '/\{{ column_camel }}/'         => $columnOf->camel(),
                '/\{{ column_camel_ucfirst }}/' => $columnOf->camel()->ucfirst(),
                '/\{{ column_title }}/'         => $title,
                '/\{{ column_is_required }}/'   => $isRequired ? 'rules={{ required: true }}' : '',
            ];

            // index
            $columns        .= $this->replace($paramsBase, $filesystem->get($this->getStubEntity('views/react/pages/column')));
            $indexGrid      .= $this->replace($paramsBase, $filesystem->get($this->getStubEntity('views/react/pages/grid')));
            $indexConst     .= $this->replace($paramsBase, $filesystem->get($this->getStubEntity('views/react/pages/const')));
            $indexFetchData .= $this->replace($paramsBase, $filesystem->get($this->getStubEntity('views/react/pages/fetch_data')));
            $indexClearFilter .= $this->replace($paramsBase, $filesystem->get($this->getStubEntity('views/react/pages/clear_filter')));

            /*
            |---------------------------------------------------
            | create/index
            |---------------------------------------------------
            */

            if ($schema->isPrimaryKey($column) || $column == 'uuid') {
                continue;
            }

            $createIndexFormControl .= $this->replace($paramsBase, $filesystem->get($this->getStubEntity('views/react/pages/create/form_control')));

            // create/index
            $columnsDefaultValues[$column] = '';

            // is required
            if ($isRequired) {
                $columnsRequired .= "'$column': yup.string().required('{$title} é um campo obrigatório'),".PHP_EOL;
            }

            /*
            |---------------------------------------------------
            | edit/[uuid]
            |---------------------------------------------------
            */
            $setValue .= $this->replace($paramsBase, $filesystem->get($this->getStubEntity('views/react/pages/edit/set_value')));
        }
        ///////////////////////////////////////////////////////////////// exec
        $arches = [
            //pages
            'index.tsx'        => 'index',
            'create/index.tsx' => 'create/index',
            'edit/[uuid].tsx'  => 'edit/[uuid]',

            // store
            'store' => 'store/index',

            // types
            'types' => 'types/index',
        ];

        $this->info('');
        $this->info("Pages");
        // gerar progress bar
        $filesBarPages = $this->output->createProgressBar(count($arches));
        $filesBarPages->start();

        foreach ($arches as $arch => $stubPage) {

            // change values
            $tableOf = Str::of($table);

            // criando pasta
            $path = match($arch) {
                'index.tsx',
                'create/index.tsx',
                'edit/[uuid].tsx'
                => $this->makeDirectory($this->getpathModulo('pages', $table) . "/" . $arch),
                'store'
                => $this->makeDirectory($this->getpathModulo('store', $table) . "/index.tsx"),
                'types'
                => $this->makeDirectory($this->getpathModulo('types', $table) . "/". $tableOf->camel()->ucfirst() ."Types.ts"),
            };
            //            $path = $this->makeDirectory($this->pathBase . "/pages/" . $this->modulo . "/" . $table . "/" . $arch);

            $params = [
                // base
                '/\{{ modulo }}/'                   => $this->modulo,
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
                '/\{{ columns_json }}/'                => Str::of(json_encode($getColumnType))->replace('"', '')->replace(',', '
    ')->replace('{', '')->replace('}', ''),
                '/\{{ columns_default_values_json }}/' => json_encode($columnsDefaultValues),
                '/\{{ columns_required }}/'            => trim($columnsRequired),
                '/\{{ columns_form_control }}/'        => trim($createIndexFormControl),

                // edit/[uuid]
                '/\{{ edit_set_value }}/' => trim($setValue),
            ];

            // busca o stub
            //            $stub = $filesystem->get($this->getStubEntity('views/react/pages/' . $stubPage));
            $stub = match($stubPage) {
                'index',
                'create/index',
                'edit/[uuid]',
                => $filesystem->get($this->getStubEntity('views/react/pages/' . $stubPage)),
                'store/index',
                'types/index',
                => $filesystem->get($this->getStubEntity('views/react/' . $stubPage)),
            };

            // aplica as alterações
            $contents = $this->replace($params, $stub);
            // cria o arquivo
            $filesystem->put("{$path}", "{$contents}");

            $filesBarPages->advance();
        }
        $filesBarPages->finish();
    }

    private function getpathModulo(string $page, string $table): string
    {
        return "{$this->pathBase}/{$page}/{$this->modulo}/{$table}";
    }
}
