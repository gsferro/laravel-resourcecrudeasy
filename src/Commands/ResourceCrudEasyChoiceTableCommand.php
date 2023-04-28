<?php

namespace Gsferro\ResourceCrudEasy\Commands;

use Exception;
use Gsferro\DatabaseSchemaEasy\DatabaseSchemaEasy;
use Gsferro\ResourceCrudEasy\Traits\{UseDomains, UseReacts};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ResourceCrudEasyChoiceTableCommand extends ResourceCrudEasyGenerateCommand
{
    use UseDomains, UseReacts;

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

            /*
            |---------------------------------------------------
            | Proteção
            |---------------------------------------------------
            */
            if (!in_array($tables, $getTables)) {
                $this->info('');
                $this->error('The table choice not exists in connection');
                return;
            }

            // caso seja todos, pega as tabelas
            if (current($tables) == 'Todos') {
                $tables = $getTables;
            }

            // gerar progress bar
            $filesBar = $this->output->createProgressBar(count($tables));
            $filesBar->start();

            foreach ($tables as $table) {
                $this->info('');
                $this->info("Tabela: {$table}");
                $this->info('');
//                $this->info('');
//                $action = $this->choice('Qual ação deve executar', [
//                    'Gerar views react'
//                ]);

//                if ($action == 'Gerar views react') {
                    $this->setEntity($table);
                    $this->generateViewsReact($table);
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

    private function setEntity(string $table)
    {
        $schema        = dbSchemaEasy($table, $this->connection);
        $columnListing = $schema->getColumnListing();

        $this->entity = $table;
        $this->entitys[ $table ] = [
            'str'           => Str::of($table),
            'table'         => $table,
            'connection'    => $this->connection,
            'schema'        => $schema,
            'columnListing' => $columnListing,
        ];
    }

    // TODO ir para pacote DatabaseSchemaEasy
    private function getTables(): array
    {
        return DB::connection($this->connection)
            ->getDoctrineSchemaManager()
            ->listTableNames();
    }
}
