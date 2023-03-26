<?php

namespace Gsferro\ResourceCrudEasy\Traits\Commands;

use Gsferro\DatabaseSchemaEasy\DatabaseSchemaEasy;
use Gsferro\ResourceCrudEasy\Commands\ResourceCrudEasyModelRecursiveCommand;
use Illuminate\Support\Facades\Artisan;

trait WithExistsTableCommand
{
    private function modelTable(string $entite): array
    {
        // prepara variaveis
        $pkString    = "";
        $fillable    = "";
        $rulesStore  = "";
        $rulesUpdate = "";
        $casts       = "";
        $relations   = "";

        $entites = $this->entites[ $entite ];
        $schema  = $entites[ 'schema' ];
        // para colocar elegantemente no arquivo
        foreach ($entites['columnListing'] as $column) {
            $str        = "'{$column}'";
            $columnType = $schema->getColumnType($column);

            // caso a pk seja string
            if ($schema->isPrimaryKey($column) && $columnType == 'string') {
                $pkString = $this->getStubModelPkString($column);
            }
            // não exibe
            if ($schema->isPrimaryKey($column) || $column == 'uuid') {
                continue;
            }

            // fillable
            $this->interpolate($fillable, "{$str}, ");

            // regras para colocar no rules['store']
            $notNull = $schema->getDoctrineColumn($column)[ "notnull" ];
            $this->replaceRules($columnType, $rulesStore, $str, $notNull);

            // regras para colocar no rules['update']
            $this->replaceRules($columnType, $rulesUpdate, $str, false);

            // casts
            $this->setCasts($columnType, $casts, $str);

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

        // use HasUuid
        $useHasUuid = [];
        if (!$schema->hasColumn('uuid')){
            $useHasUuid = [
                '/\{{ has_uuid }}/' => ''
            ];
        }

        return [
            '/\{{ pk_string }}/'  => $pkString,
            '/\{{ timestamps }}/' => !$schema->hasColumnsTimestamps() ? 'public $timestamps = false;' : '',
            '/\{{ connection }}/' => !is_null($entites[ 'connection' ]) ? 'protected $connection = \'' . $entites[ 'connection' ] . '\';' : '',
            '/\{{ fillable }}/'   => $fillable,
            '/\{{ cast }}/'       => $casts,
            '/\{{ relations }}/'  => $relations,
            '/\{{ belongs_to_relation }}/'  => 'use Illuminate\Database\Eloquent\Relations\BelongsTo;',
            //            '/\{{ has_many_relation }}/'    => 'use Illuminate\Database\Eloquent\Relations\HasMany;',

            // Nome tabela
            '/\{{ class_table }}/'  => $entites['table'],
            '/\{{ rules_store }}/'  => $rulesStore,
            '/\{{ rules_update }}/' => $rulesUpdate,
        ] + $useHasUuid;
    }

    private function factoryTable(string $entite): array
    {
        $entites = $this->entites[ $entite ];
        if (!$entites['useFactory'] ) {
            return [];
        }

        // encapsulando
        $schema = $entites[ 'schema' ];

        // prepara variaveis
        $factoryFillables = "";
        $thisFake         = '$this->faker->';
        $fake             = 'word';
        foreach ($entites['columnListing'] as $column) {
            if ($schema->isPrimaryKey($column) || $column == 'uuid') {
                continue;
            }

            $str = "'{$column}'";
            // get factory from relation, if exists
            if ($this->contains($str, ['_id'])) {
                // relations
                $foreinsKey = $schema->hasForeinsKey($column);
                // pega o nome da model
                $modelName = $foreinsKey[ 'related' ];
                // pega o caminho do factory para ver se existe
                $class = database_path('factories/' . $modelName . 'Factory.php');
                // retorno default como id 1 ou factory
                $add = !$this->classExists($class)
                    ? "{$str} => 1, // TODO Factory Relation"
                    : "{$str} => \App\Models\\$modelName::factory()->create()->id,";

                $this->interpolate($factoryFillables, $add);
                continue;
            }

            if ($this->contains($str, ['uuid'])) {
                $this->interpolate($factoryFillables, "{$str} => {$thisFake}uuid,");
                continue;
            }

            // get type column
            $columnType = $schema->getColumnType($column);
             switch ($columnType){
                case 'string':
                    if ($this->contains($str, ['nome', 'name'])) {
                        $fake = "name";
                    }
                    if ($this->contains($str, ['titulo', 'title'])) {
                        $fake = "title";
                    }
                    if ($this->contains($str, ['email'])) {
                        $fake = "unique()->safeEmail";
                    }
                break;
                case 'integer':
                    $fake = "numerify";
                break;
            };

            $faker = "{$thisFake}{$fake}";
            $this->interpolate($factoryFillables, "{$str} => {$faker}, ");
        }

        return [
            '/\{{ factory_fillables }}/' => $factoryFillables,
        ];
    }

    private function seederTable(string $entite): array
    {
        $entites = $this->entites[ $entite ];
        if (!$entites['useSeeder'] ) {
            return array();
        }

        // prepara variaveis
        $seederFillables = "";
        foreach ($entites['columnListing'] as $column) {
            $this->interpolate($seederFillables, "'{$column}' => '', ");
        }

        return [
            '/\{{ seeder_fillables }}/' => $seederFillables,
        ];
    }

    private function migrateTable(string $entite): array
    {
        $entites = $this->entites[ $entite ];
        if (!$entites['useMigrate'] ) {
            return [];
        }

        // encapsulando
        /**
         * @var DatabaseSchemaEasy
        */
        $schema = $entites[ 'schema' ];

        // prepara variaveis
        $migrateFillables = "";
        $migrateRelation  = "";
        $base             = '$table->';
        foreach ($entites['columnListing'] as $column) {
            if ($schema->isPrimaryKey($column)) {
                continue;
            }

            // pega os dados da coluna
            $infosColumn = $schema->getDoctrineColumn($column);

            // is null
            $nullable = !$infosColumn[ 'notnull' ] ? '->nullable()' : '';

            // verifica se é do tipo uuid
            if ($this->contains($column, ['uuid'])) {
                $this->interpolate($migrateFillables, "{$base}uuid('{$column}'){$nullable};");
                continue;
            }

            // type column
            $columnType = $schema->getColumnType($column);
            switch ($columnType){
                case 'datetime':
                    $columnType = "timestamp";
                break;
                case 'smallint':
                    $columnType = "smallInteger";
                break;
                case 'integer':
                    // cria o int com unsigned
                    if (($infosColumn[ 'unsigned' ])) {
                        $columnType = 'unsignedInteger';
                    }
                    // integerIncrements chama o unsignedInteger passando true para autoincrement
                    if (($infosColumn[ 'autoincrement' ])) {
                        $columnType = 'integerIncrements';
                    }
                break;
            };

            /*
            |---------------------------------------------------
            | Infos of column
            |---------------------------------------------------
            |
            | lenght
            | default
            | comment
            |
            */
            $length  = !is_null($infosColumn[ 'length' ])  ? ", '{$infosColumn['length']}'"           : '';
            $default = !is_null($infosColumn[ 'default' ]) ? "->default('{$infosColumn['default']}')" : '';
            $comment = !is_null($infosColumn[ 'comment' ]) ? "->comment('{$infosColumn['comment']}')" : '';

            // montagem da linha
            $add = "{$base}{$columnType}('{$column}'{$length}){$nullable}{$default}{$comment};";

            // escreve na variavel
            $this->interpolate($migrateFillables, $add);

            // has relations
            $foreinsKey = $schema->hasForeinsKey($column);
            // verifica se eh uma fk para criar o relacionamento
            if ($this->contains($column, ['_id']) && is_array($foreinsKey)) {
                $addRelation = "{$base}foreign('{$column}')->references('{$foreinsKey['ownerKey']}')->on('{$foreinsKey['table']}');";
                $this->interpolate($migrateRelation, $addRelation);
            }
        }

        // padrão laravel de timestamp
        if ($schema->hasColumnsTimestamps()) {
            $this->interpolate($migrateFillables, "{$base}timestamps();");
        }

        if (!empty($migrateRelation)) {
            $migrateRelation = "// Relations".PHP_EOL.$migrateRelation;
        }

        return [
            '/\{{ migrate_fillables }}/' => $migrateFillables,
            '/\{{ migrate_relation }}/'  => $migrateRelation,
            // Nome tabela
            '/\{{ class_table }}/'  => $entites['table'],
        ];
    }

    private function contains($str, array $array): bool
    {
        foreach($array as $arr) {
            if (stripos($str,$arr) !== false)
                return true;
        }
        return false;
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

        $this->exec($foreinsKey[ 'tableCamel' ]->ucfirst(), $table, current($this->entites)[ 'connection' ]);
    }

    private function setBelongsTo(string $entite, string $column, string $relations): string
    {
        $foreinsKey = $this->entites[$entite]['schema']->hasForeinsKey($column, true);
        $belongto   = $this->getStubRelatios('belongto', $foreinsKey);
        $this->interpolate($relations, $belongto);

        // TODO Set hasone Or hasMany in Model
        $this->applyRelationHasInTableForeingKey($entite, $foreinsKey);
        return $relations;
    }

    private function setCasts(string $columnType, string &$casts, string $str): void
    {
        // proteção contra type
        $columnType = $this->replaceTypeColumnCast($columnType);
        // casts
        $this->interpolate($casts, "{$str} => '{$columnType}', ");
    }
}
