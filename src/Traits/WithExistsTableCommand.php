<?php

namespace Gsferro\ResourceCrudEasy\Traits;

use Gsferro\ResourceCrudEasy\Commands\ResourceCrudEasyModelRecursiveCommand;
use Illuminate\Support\Facades\Artisan;
use function Pest\Laravel\options;

trait WithExistsTableCommand
{
    private function modelTable(string $entite): array
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
        ];
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
        $relations        = "";
        $thisFake         = '$this->faker->';
        $fake             = 'word';
        foreach ($entites['columnListing'] as $column) {
            if ($schema->isPrimaryKey($column)) {
                continue;
            }

            $str = "'{$column}'";
            // TODO get factory from relation, if exists
            if ($this->contains($str, ['_id'])) {
                // relations
                $foreinsKey = $schema->hasForeinsKey($column);
//                dump($foreinsKey);
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

    private function contains($str, array $arr)
    {
        foreach($arr as $a) {
            if (stripos($str,$a) !== false)
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

        $options = [
            'entite'     => $foreinsKey[ 'tableCamel' ]->ucfirst(),
            'table'      => $table,
            'connection' => current($this->entites)[ 'connection' ], // pega a conexão da 1º chamada
        ];

        $choice = $this->choice("Create a Completed Crud or only Model?", [
//            '1' => 'Completed',
            '2' => 'Only Model',
        ], 2);

        match ($choice) {
//            'Completed' => '', //$this->call('gsferro:resource-crud', $options),
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
