<?php

namespace Gsferro\ResourceCrudEasy\Traits;

use Gsferro\ResourceCrudEasy\Commands\ResourceCrudEasyModelRecursiveCommand;
use Illuminate\Support\Facades\Artisan;

trait WithExistsTableCommand
{
    /**
     * @param array $params
     * @return array
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function modelTable(array $params): array
    {
        // prepara variaveis
        $pkString   = "";
        $fillable   = "";
        $rulesStore = "";
        $casts      = "";
        $relations  = "";

        // para colocar elegantemente no arquivo
        foreach ($this->columnListing as $column) {
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

            // regras para colocar no rules['store']
            $this->rulesStore($columnType, $column, $rulesStore, $str);

            // casts
            $this->interpolate($casts, "{$str} => '{$columnType}', ");

            // relations
            $foreinsKey = $this->schema->hasForeinsKey($column);

            if (!is_array($foreinsKey)) {
                continue;
            }

            dump($column, $this->entite);

            // temp
            $table = $this->entite;
            
            // new entite
            if (!$this->schema->hasModelWithTableName($foreinsKey['table'])) {
                $this->newEntiteFromRelation($foreinsKey);
                continue;
            }

            $this->entite = $table;
            dump($column);
            // belongTo
            $relations = $this->setBelongsTo($column, $relations);
        }

        return [
                '/\{{ pk_string }}/'  => $pkString,
                '/\{{ timestamps }}/' => !$this->schema->hasColumnsTimestamps() ? 'public $timestamps = false;' : '',
                '/\{{ fillable }}/'   => $fillable,
                '/\{{ cast }}/'       => $casts,
                '/\{{ relations }}/'  => $relations,
                '/\{{ belongs_to_relation }}/'  => 'use Illuminate\Database\Eloquent\Relations\BelongsTo;',
                //            '/\{{ has_many_relation }}/'    => 'use Illuminate\Database\Eloquent\Relations\HasMany;',

                // Nome tabela
                '/\{{ class_table }}/'  => $this->table,
                '/\{{ rules_store }}/'  => $rulesStore,
                '/\{{ rules_update }}/' => $casts, // default
            ] + $params;
    }

    /**
     * @param array $params
     * @return array
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function factoryTable(array $params): array
    {
        if (!$this->useFactory ) {
            return [];
        }

        // prepara variaveis
        $fillable   = "";
        $relations  = "";

    }

    /**
     * @param array $foreinsKey
     * @return int|void
     */
    private function newEntiteFromRelation(array $foreinsKey)
    {
        $this->comment("Your new model, based on table [ {$this->table} ], has a foreign key [ {$foreinsKey['foreignKey']} ] of table [ {$foreinsKey['table']} ] and this model has not yet been created.");
        $create = $this->confirm("Do you like to create now?", true);
        if (!$create) {
            return;
        }
        
        $options = [
            'entite'         => $foreinsKey[ 'tableCamel' ] ->ucfirst(),
            '--table'        => $foreinsKey[ 'table' ],
            '--connection'   => $this->connection,
            '--not-wellcome' => true,
        ];

        $entite = $this->choice("Create a Completed Crud or only Model?", [
            '1' => 'Completed',
            '2' => 'Only Model',
        ], 2);

//        $call = New Artisan();
        if ($entite == 'Only Model') {
//            $recurse = $this->call('gsferro:resource-crud-model', $options);
//            $recurse = app()->call('gsferro:resource-crud-model', $options);
//            return app()->call(function () use ($options) {
//                dump('--------------------');
//            });

            return $this->call('gsferro:resource-crud-model-recursive', $options);
            $recursive = new ResourceCrudEasyModelRecursiveCommand();
            return $recursive->call('gsferro:resource-crud-model-recursive', $options);

            //            return $call::clearResolvedInstance($recurse);
        }

        return $this->call('gsferro:resource-crud', $options);
        return \Illuminate\Support\Facades\Artisan::call('gsferro:resource-crud', $options);
        
       /* match ($entite) {
            'Completed' => $this->call('gsferro:resource-crud', $options),
            'Only Model' => $this->call('gsferro:resource-crud-model', $options),
        };*/
    }

    /**
     * @param mixed $column
     * @param string $relations
     * @return array
     */
    private function setBelongsTo(mixed $column, string $relations): string
    {
        $foreinsKey = $this->schema->hasForeinsKey($column, true);
        $belongto   = $this->getStubRelatios('belongto', $foreinsKey);
        $this->interpolate($relations, $belongto);

        // TODO Set hasone Or hasMany in Model
        $this->applyRelationHasInTableForeingKey($foreinsKey);
        return $relations;
    }
}
