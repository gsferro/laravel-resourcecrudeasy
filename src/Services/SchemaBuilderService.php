<?php

namespace Gsferro\ResourceCrudEasy\Services;

use Illuminate\Database\Connection;
use \Illuminate\Database\Schema\Builder;
use \Illuminate\Support\Facades\DB;

class SchemaBuilderService
{
    private Connection $connection;
    private Builder $builder;

    public function __construct(private string $table, string $connection = null)
    {
        $this->connection = DB::connection($connection);
        $this->builder    = $this->connection->getSchemaBuilder();
    }

    /**
     * Retorna todas as colunas da table
     * 
     * @return array
     */
    public function getColumnListing(): array
    {
        return $this->builder->getColumnListing($this->table);
    }

    /**
     * Retorna o type da colunas
     *
     * @param string $column
     * @return string
     */
    public function getColumnType(string $column): string
    {
        return $this->builder->getColumnType($this->table, $column);
    }

    /**
     * Retorna todas as informações da colunas
     *
     * @param string $column
     * @return array
     */
    public function getDoctrineColumn(string $column): array
    {
        return $this->connection
                ->getDoctrineColumn($this->table, $column)
                ->toArray() + [
                    'primary_key' => $this->isPrimaryKey($column)
                ];
    }

    /**
     * verifica se a coluna é pk
     * 
     * @param string $column
     * @return bool
     */
    private function isPrimaryKey(string $column): bool
    {
        return in_array($column, $this->primaryKeys());
    }

    /**
     * Retorna as chaves pks
     * 
     * @return array
     */
    public function primaryKeys(): array
    {
        return $this->connection
            ->getDoctrineSchemaManager()
            ->listTableDetails($this->table)
            ->getPrimaryKey()
            ->getColumns();
    }

    /**
     * Verifica se a table já existe model eloquent criada
     * retorna a model ou false
     * 
     * @return bool|string
     */
    public function hasModelWithTableName(): bool|string
    {
        $path   = app_path('Models') . '/*.php';
        $models = collect(glob($path))->map(fn($file) => basename($file, '.php'))->toArray();

        foreach ($models as $model) {
            $instance = "App\Models\\$model";
            $table    = (new $instance)->getTable();
            if ($table == $this->table) {
                return $instance;
            }
        }

        return false;
    }
}
