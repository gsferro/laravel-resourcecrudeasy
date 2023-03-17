<?php

namespace Gsferro\ResourceCrudEasy\Commands;

use Illuminate\Console\Command;

class ResourceCrudEasyModelRecursiveCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'gsferro:resource-crud-model-recursive';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gsferro:resource-crud-model-recursive {entite : Entite name} {--table=} {--connection=} {--not-wellcome}';

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
        $entite     = $this->argument('entite');
        $table      = $this->option('table');
        $connection = $this->option('connection');

//        dump($entite, $table, $connection);

        return $this->call('gsferro:resource-crud-model', [
            'entite'         => $entite,
            '--table'        => $table,
            '--connection'   => $connection,
            '--not-wellcome' => true,
        ]);
    }
}
