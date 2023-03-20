<?php

namespace Gsferro\ResourceCrudEasy\Traits;

use Gsferro\ResourceCrudEasy\Commands\ResourceCrudEasyModelRecursiveCommand;
use Illuminate\Support\Facades\Artisan;
use function Pest\Laravel\options;

trait WithExistsTableCommand
{
    private function modelTable(string $entite, array $params): array
    {
        // prepara variaveis
        $pkString   = "";
        $fillable   = "";
        $rulesStore = "";
        $casts      = "";
        $relations  = "";

        $entites = $this->entites[ $entite ];
        $schema  = $entites[ 'schema' ];
        // para colocar elegantemente no arquivo
        foreach ($entites['columnListing'] as $column) {
            $str        = "'{$column}'";
            $columnType = $schema->getColumnType($column);

//            dump($str, $columnType);

            // caso a pk seja string
            if ($schema->isPrimaryKey($column) && $columnType == 'string') {
                $pkString = $this->getStubModelPkString($column);
            }
            // não exibe
            if ($schema->isPrimaryKey($column)) {
                continue;
            }

            // fillable
            $this->interpolate($fillable, "{$str}, ");

            // regras para colocar no rules['store']
            $notNull = $schema->getDoctrineColumn($column)[ "notnull" ];
            $this->rulesStore($columnType, $rulesStore, $str, $notNull);

            // casts
            $this->interpolate($casts, "{$str} => '{$columnType}', ");

            // relations
            $foreinsKey = $schema->hasForeinsKey($column);

            if (!is_array($foreinsKey)) {
                continue;
            }

            // new entite
            if (!$schema->hasModelWithTableName($foreinsKey['table'])) {
                $this->newEntiteFromRelation($entites['table'], $foreinsKey);
            }

            // belongTo
            $relations = $this->setBelongsTo($entite, $column, $relations);
        }

        return [
                '/\{{ pk_string }}/'  => $pkString,
                '/\{{ timestamps }}/' => !$schema->hasColumnsTimestamps() ? 'public $timestamps = false;' : '',
                '/\{{ fillable }}/'   => $fillable,
                '/\{{ cast }}/'       => $casts,
                '/\{{ relations }}/'  => $relations,
                '/\{{ belongs_to_relation }}/'  => 'use Illuminate\Database\Eloquent\Relations\BelongsTo;',
                //            '/\{{ has_many_relation }}/'    => 'use Illuminate\Database\Eloquent\Relations\HasMany;',

                // Nome tabela
                '/\{{ class_table }}/'  => $entites['table'],
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
    private function newEntiteFromRelation(string $tableCurrent, array $foreinsKey)
    {
        $table = $foreinsKey[ 'table' ];
        $this->comment("Your new model, based on table [ {$tableCurrent} ], has a foreign key [ {$foreinsKey['foreignKey']} ] of table [ {$table} ] and this model has not yet been created.");
        $create = $this->confirm("Do you like to create now?", true);
        if (!$create) {
            return;
        }

        $entite  = $foreinsKey[ 'tableCamel' ]->ucfirst();
        $options = [
            'entite'     => $entite,
            'table'      => $table,
            'connection' => current($this->entites)[ 'connection' ], // pega a conexão da 1º chamada
        ];

        $entite = $this->choice("Create a Completed Crud or only Model?", [
//            '1' => 'Completed',
            '2' => 'Only Model',
        ], 2);

        match ($entite) {
            'Completed' => '', //$this->call('gsferro:resource-crud', $options),
            'Only Model' => $this->exec($options['entite'], $options['table'], $options['connection']),
        };
    }

    /**
     * @param mixed $column
     * @param string $relations
     * @return array
     */
    private function setBelongsTo(string $entite, string $column, string $relations): string
    {
        $foreinsKey = $this->entites[$entite]['schema']->hasForeinsKey($column, true);
        $belongto   = $this->getStubRelatios('belongto', $foreinsKey);
        $this->interpolate($relations, $belongto);

        // TODO Set hasone Or hasMany in Model
        $this->applyRelationHasInTableForeingKey($entite, $foreinsKey);
        return $relations;
    }
}
