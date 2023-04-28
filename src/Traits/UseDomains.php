<?php

namespace Gsferro\ResourceCrudEasy\Traits;

use Illuminate\Support\Str;
use Illuminate\Support\Stringable;

trait UseDomains
{
    private function generateDomains(string $table)
    {
        $tableOf = Str::of($table);
        /*
        |---------------------------------------------------
        | Cria a pasta domains, caso não exista
        |---------------------------------------------------
        */
        $domain = $this->makeDirectory(app_path('Domains'));

        /*
        |---------------------------------------------------
        | Cria a pasta do modulo
        |---------------------------------------------------
        */
        $pathBase = $this->makeDirectory($domain."/". $this->modulo);

        /*
        |---------------------------------------------------
        | Cria a pasta com o nome da Table
        |---------------------------------------------------
        */
        $tableName = $tableOf->singular()->camel()->ucfirst();
        $pathTable = $this->makeDirectory($pathBase . "/" . $tableName);

        /*
        |---------------------------------------------------
        | Criar pastas
        |---------------------------------------------------
        */
        $this->generateDomainsActions($pathTable, $tableOf);
        $this->generateDomainsBags($pathTable, $tableOf);
        $this->generateDomainsCriteria($pathTable, $tableOf);
        $this->generateDomainsExport($pathTable, $tableOf);
        $this->generateDomainsHttp($pathTable, $tableOf);
    }

    private function generateDomainsActions(string $pathTable, Stringable $tableOf)
    {
        /*
        |---------------------------------------------------
        | Cria a pasta base
        |---------------------------------------------------
        */
        $pathBase = $this->makeDirectory($pathTable."/Actions");

        /*
        |---------------------------------------------------
        | reuso
        |---------------------------------------------------
        */
        $filesystem = $this->files;

        /*
        |---------------------------------------------------
        | Arquivos a serem criados
        |---------------------------------------------------
        */
        $arches = [
            'create',
            'destroy',
            'export',
            'get',
            'update',
        ];

        $this->info('');
        $this->info("Actions");
        // gerar progress bar
        $filesBarActions = $this->output->createProgressBar(count($arches));
        $filesBarActions->start();

        foreach ($arches as $arch) {
            $params = $this->getParams($tableOf);

            $tableNameUse = match ($arch) {
                'export', 'get' => $tableOf->camel()->ucfirst(),
                default => $tableOf->singular()->camel()->ucfirst()
            };
            $filename = Str::ucfirst($arch) . $tableNameUse . "Action.php";
            $path     = $this->makeDirectory($pathBase . "/" . $filename);

            // busca o stub
            $stub = $filesystem->get($this->getStubEntity('domains/actions/' . $arch));

            // aplica as alterações
            $contents = $this->replace($params, $stub);
            // cria o arquivo
            $filesystem->put("{$path}", "{$contents}");

            $filesBarActions->advance();
        }
        $filesBarActions->finish();

    }

    private function generateDomainsBags(string $pathTable, Stringable $tableOf)
    {
        /*
        |---------------------------------------------------
        | Cria a pasta base
        |---------------------------------------------------
        */
        $pathBase = $this->makeDirectory($pathTable."/Bags");

        /*
        |---------------------------------------------------
        | reuso
        |---------------------------------------------------
        */
        $filesystem = $this->files;

        /*
        |---------------------------------------------------
        | Arquivos a serem criados
        |---------------------------------------------------
        */
        $arches = [
            'bag',
        ];

        $this->info('');
        $this->info("Bags");
        // gerar progress bar
        $filesBarBags = $this->output->createProgressBar(count($arches));
        $filesBarBags->start();

        foreach ($arches as $arch) {
            $params   = $this->getParams($tableOf);
            $filename = $tableOf->singular()->camel()->ucfirst() . "Bag.php";
            $path     = $this->makeDirectory($pathBase . "/" . $filename);

            // busca o stub
            $stub = $filesystem->get($this->getStubEntity('domains/bags/' . $arch));

            // aplica as alterações
            $contents = $this->replace($params, $stub);
            // cria o arquivo
            $filesystem->put("{$path}", "{$contents}");

            $filesBarBags->advance();
        }
        $filesBarBags->finish();
    }

    private function generateDomainsCriteria(string $pathTable, Stringable $tableOf)
    {
        /*
        |---------------------------------------------------
        | Cria a pasta base
        |---------------------------------------------------
        */
        $pathBase = $this->makeDirectory($pathTable."/Criteria");

        /*
        |---------------------------------------------------
        | reuso
        |---------------------------------------------------
        */
        $filesystem = $this->files;

        /*
        |---------------------------------------------------
        | Arquivos a serem criados
        |---------------------------------------------------
        */
        $arches = [
            'criteria',
        ];

        $this->info('');
        $this->info("Criteria");
        // gerar progress bar
        $filesBarCriteria = $this->output->createProgressBar(count($arches));
        $filesBarCriteria->start();

        foreach ($arches as $arch) {
            $params   = $this->getParams($tableOf);
            $filename = $tableOf->camel()->ucfirst() . "ListCriteria.php";
            $path     = $this->makeDirectory($pathBase . "/" . $filename);

            // busca o stub
            $stub = $filesystem->get($this->getStubEntity('domains/criterias/' . $arch));

            // aplica as alterações
            $contents = $this->replace($params, $stub);
            // cria o arquivo
            $filesystem->put("{$path}", "{$contents}");

            $filesBarCriteria->advance();
        }
        $filesBarCriteria->finish();
    }

    private function generateDomainsExport(string $pathTable, Stringable $tableOf)
    {
        /*
        |---------------------------------------------------
        | Cria a pasta base
        |---------------------------------------------------
        */
        $pathBase = $this->makeDirectory($pathTable."/Exports");

        /*
        |---------------------------------------------------
        | reuso
        |---------------------------------------------------
        */
        $filesystem = $this->files;

        /*
        |---------------------------------------------------
        | Arquivos a serem criados
        |---------------------------------------------------
        */
        $arches = [
            'export',
        ];

        $this->info('');
        $this->info("Export");
        // gerar progress bar
        $filesBar = $this->output->createProgressBar(count($arches));
        $filesBar->start();

        $columnData = $this->getExtraParamsDomains($tableOf)['columnData'];

        foreach ($arches as $arch) {
            $params   = $this->getParams($tableOf) + [
                '/\{{ column_data }}/' => trim($columnData),
            ];
            $filename = $tableOf->singular()->camel()->ucfirst() . "Export.php";
            $path     = $this->makeDirectory($pathBase . "/" . $filename);

            // busca o stub
            $stub = $filesystem->get($this->getStubEntity('domains/export/' . $arch));

            // aplica as alterações
            $contents = $this->replace($params, $stub);
            // cria o arquivo
            $filesystem->put("{$path}", "{$contents}");

            $filesBar->advance();
        }
        $filesBar->finish();
    }

    private function generateDomainsHttp(string $pathTable, Stringable $tableOf)
    {
        /*
        |---------------------------------------------------
        | Cria a pasta base
        |---------------------------------------------------
        */
        $pathBase = $this->makeDirectory($pathTable."/Http");

        /*
        |---------------------------------------------------
        | Arquivos a serem criados
        |---------------------------------------------------
        */
        $arches = [
            'http/controllers/controllers' => 'Controller',
        ];

        $this->info('');
        $this->info("Http");
        // gerar progress bar
        $filesBar = $this->output->createProgressBar(count($arches));
        $filesBar->start();

//        $columnData = $this->getExtraParamsDomains($tableOf)['columnData'];

        foreach ($arches as $arch => $fileExtensionName) {
            $params = $this->getParams($tableOf) + [
//                '/\{{ column_data }}/' => trim($columnData),
            ];

            $filename = $tableOf->camel()->ucfirst() . $fileExtensionName . ".php";
            $path     = $this->makeDirectory($pathBase . "/" . $filename);

            $this->writeFile("domains/$arch", $params, $path);

            $filesBar->advance();
        }
        $filesBar->finish();
    }

    /*
    |---------------------------------------------------
    | Reuso
    |---------------------------------------------------
    */
    private function getParams(Stringable $tableOf): array
    {
        return [
            // base
            '/\{{ modulo }}/'                            => $this->modulo,
            '/\{{ table_name }}/'                        => $tableOf,
            '/\{{ table_singular }}/'                    => $tableOf->singular(),
            '/\{{ table_title }}/'                       => $tableOf->title()->replace('_', ' '),
            '/\{{ table_name_camel }}/'                  => $tableOf->camel(),
            '/\{{ table_name_camel_ucfirst }}/'          => $tableOf->camel()->ucfirst(),
            '/\{{ table_name_singular_camel }}/'         => $tableOf->singular()->camel(),
            '/\{{ table_name_singular_camel_ucfirst }}/' => $tableOf->singular()->camel()->ucfirst(),
        ];
    }

    private function getExtraParamsDomains(Stringable $tableOf): array
    {
        $schema        = dbSchemaEasy($tableOf, $this->connection);
        $columnListing = $schema->getColumnListing();

        $getColumnType = [];
        foreach ($columnListing as $column) {
            $type = $schema->getColumnType($column) == 'integer' ? 'number' : 'string';
            $getColumnType[ $column ] = $type;
        }

        $filesystem = $this->files;

        $columnData = "";
        foreach ($getColumnType as $column => $type) {
            $columnOf = Str::of($column);

            if ($column == 'uuid') {
                continue;
            }

            $isRequired = $schema->getDoctrineColumn($column)[ 'notnull' ];

            /*
            |---------------------------------------------------
            | params reuso
            |---------------------------------------------------
            */
            $title = $columnOf->title()->replace('_', ' ');
            $paramsBase    = [
                '/\{{ column }}/'                    => $column,
                '/\{{ column_type }}/'               => $type,
                '/\{{ column_camel }}/'              => $columnOf->camel(),
                '/\{{ column_camel_ucfirst }}/'      => $columnOf->camel()->ucfirst(),
                '/\{{ column_title }}/'              => $title,
                '/\{{ column_is_required }}/'        => $isRequired ? 'rules={{ required: true }}' : '',
                '/\{{ table_name_singular_camel }}/' => $tableOf->singular()->camel(),
            ];

            // export
            $columnData .= $this->replace($paramsBase,
                $filesystem->get($this->getStubEntity('domains/export/column_data'))
            );
        }

        return [
            'columnData' => $columnData,
        ];
    }

    private function writeFile(string $stubPath, array $params, string $path): void
    {
        // encapsulamento
        $filesystem = $this->files;

        // busca o stub
        $stub = $filesystem->get($this->getStubEntity($stubPath));

        // aplica as alterações
        $contents = $this->replace($params, $stub);

        // cria o arquivo
        $filesystem->put("{$path}", "{$contents}");
    }
}