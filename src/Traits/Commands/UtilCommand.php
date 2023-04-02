<?php

namespace Gsferro\ResourceCrudEasy\Traits\Commands;

trait UtilCommand
{
    /*
    |---------------------------------------------------
    | Blocos
    |---------------------------------------------------
    */
    private function applyReplaceBlocoFactory(string $entite)
    {
        return $this->entites[$entite]['useFactory']
            ? $this->files->get($this->getStubEntite('ifs/pest_model_use_factory'))
            : '';
    }

    /**
     * @param string $type
     * @param array $params
     * @return array|string|string[]|null
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function getStubRelatios(string $type, array $params)
    {
        $stub = $this->files->get($this->getStubEntite('relations/' . $type));
        return $this->replace($params, $stub);
    }

    /**
     * @param string $type
     * @param array $params
     * @return array|string|string[]|null
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function getStubModelPkString(string $column)
    {
        $params = [
            '/\{{ primaryKey }}/' => $column
        ];
        $stub = $this->files->get($this->getStubEntite('ifs/model_pk_string'));
        return $this->replace($params, $stub);
    }

    /**
     * @param string $entite
     * @param string|null $table
     * @param string|null $connection
     * @throws \Throwable
     */
    private function verifyDatabase(string $entite, ?string $table = null, ?string $connection = null): void
    {
        /*
        |---------------------------------------------------
        | From Database
        |---------------------------------------------------
        */
        // se não tiver conexão, verifica se foi passado via option
        if (is_null($connection)) {
            $connection = (bool)$this->option('connection') ? $this->option('connection') : null;
        }

        throw_if(
            !is_null($connection) &&
            !in_array($connection, array_keys(config('database.connections'))),
            \Exception::class,
            "connection [ $connection ] not configured in your config database connections"
        );

        // se não tiver table, verifica se foi passado via option
        if (is_null($table)) {
            $table = (bool)$this->option('table') ? $this->option('table') : null;
        }

        // se for setado uma table
        if (!is_null($table)) {
            $schema        = dbSchemaEasy($table, $connection);
            $columnListing = $schema->getColumnListing();

            $this->entites[ $entite ] += [
                'table'         => $table,
                'connection'    => $connection,
                'schema'        => $schema,
                'columnListing' => $columnListing,
            ];
        }
    }

    /**
     * @param string $string
     * @param string $add
     * @param null $delimiter
     */
    private function interpolate(string &$string, string $add, $delimiter = null)
    {
        $string .= (strlen($string) == 0 ? $delimiter : '        ' ).$add. PHP_EOL;
    }

    /**
     * quando estiver em uma table que tiver um belongto, vai no relacionamento e aplica o hasMany
     *
     * @param array $foreinsKey
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function applyRelationHasInTableForeingKey(string $entite, array $foreinsKey, string $type = 'has_many')
    {
        // TODO criar qdo não houver?
        $entites = $this->entites[$entite];
        if (!$entites['schema']->hasModelWithTableName($foreinsKey['/\{{ table }}/'])) {
            return;
        }

        // busca o arquivo
        $path = 'app/Models/' . $foreinsKey[ '/\{{ related }}/' ] . '.php';
        $base = base_path($path);

        // pega todo o arquivo
        $fileContents = file_get_contents($base);

        // caso já tenha sido configurado
        $stringable = $entites['str'];
        if (str_contains($fileContents, $stringable->camel().'()')){
            return;
        }

        // prepara o stub
        $hasManyStub = $this->getStubRelatios($type, $foreinsKey + [
                // override
                '/\{{ class }}/'       => $stringable,
                '/\{{ class_camel }}/' => $stringable->camel(),
            ]);
        $params = [
            // relation
            '/# HasMany/' => $hasManyStub,
        ];

        // atualiza mesmo já tendo sido criado
        $this->files->put("{$path}", $this->replace($params, $fileContents));
    }

    /**
     * @param string $columnType
     * @param mixed $column
     * @param string $rules
     * @param string $str
     */
    private function replaceRules(string $columnType, string &$rules, string $str, bool $notNull): void
    {
        if ($str == 'uuid') {
            return;
        }
        
        // proteção contra type
        $rule = $this->replaceTypeColumnRules($columnType);

        if ($notNull) {
            $rule .= "|required";
        }
        $this->interpolate($rules, "{$str} => '{$rule}', ");
    }

    /**
     * @param string $columnType
     * @return string
     */
    private function replaceTypeColumnRules(string $columnType): string
    {
        switch ($columnType) {
            case 'guid':
                $columnType = "uuid";
            break;
            case 'decimal':
            case 'float':
                $columnType = "numeric";
            break;
            case 'datetime':
                $columnType = "date_format:Y-m-d H:i:s";
            break;
            case 'smallint':
            case 'bigint':
                $columnType = "integer";
            break;
        }
        return $columnType;
    }

    /**
     * @param string $columnType
     * @return string
     */
    private function replaceTypeColumnCast(string $columnType): string
    {
        switch ($columnType) {
            case 'guid':
            case 'uuid':
            case 'text':
                $columnType = "string";
            break;
            //            case 'decimal':
            //                $columnType = "numeric";
            //            break;
            case 'smallint':
            case 'bigint':
                $columnType = "integer";
            break;
        }
        return $columnType;
    }
}