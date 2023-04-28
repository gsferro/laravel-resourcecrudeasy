<?php

namespace Gsferro\ResourceCrudEasy\Traits;

use Gsferro\ResourceCrudEasy\Traits\Commands\{WithExistsTableCommand, UtilCommand};
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;

trait UseDomains
{
    use WithExistsTableCommand, UtilCommand;

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
        $this->generateDomainsHttps($pathTable, $tableOf);
        $this->generateDomainsRespositories($pathTable, $tableOf);
        $this->generateDomainsRoutes($pathTable, $tableOf);
        $this->generateDomainsModels($pathTable, $tableOf);

        $this->info('');
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
        $this->comment("> Actions");
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
        $this->comment("> Bags");
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
        $this->comment("> Criteria");
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
        $this->comment("> Export");
        // gerar progress bar
        $filesBarExport = $this->output->createProgressBar(count($arches));
        $filesBarExport->start();

        $columnData = $this->getExtraParamsDomains($tableOf)['columnData'];

        foreach ($arches as $arch) {
            $params   = $this->getParams($tableOf) + [
                '/\{{ column_data }}/' => trim($columnData),
            ];
            $filename = $tableOf->camel()->ucfirst() . "Export.php";
            $path     = $this->makeDirectory($pathBase . "/" . $filename);

            // busca o stub
            $stub = $filesystem->get($this->getStubEntity('domains/export/' . $arch));

            // aplica as alterações
            $contents = $this->replace($params, $stub);
            // cria o arquivo
            $filesystem->put("{$path}", "{$contents}");

            $filesBarExport->advance();
        }
        $filesBarExport->finish();
    }

    private function generateDomainsHttps(string $pathTable, Stringable $tableOf)
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
            'http/requests/create'         => 'Request',
            'http/requests/update'         => 'Request',
            'http/resources/resources'     => 'Resource',
        ];

        $this->info('');
        $this->comment("> Http");
        // gerar progress bar
        $filesBarHttp = $this->output->createProgressBar(count($arches));
        $filesBarHttp->start();

        $extraParamsDomains = $this->getExtraParamsDomains($tableOf);
        $attributesRequest  = $extraParamsDomains['attributesRequest'];
        $attributesResource = $extraParamsDomains['attributesResource'];

        foreach ($arches as $arch => $fileExtensionName) {
            $params = $this->getParams($tableOf) + [
                '/\{{ attributes_request }}/' => trim($attributesRequest),
                '/\{{ attributes_resource }}/' => trim($attributesResource),
            ];

            $folder = match ($fileExtensionName) {
                'Controller' => 'Controllers',
                'Request'    => 'Requests',
                'Resource'   => 'Resources',
            };

            $name = $tableOf->singular()->camel()->ucfirst();

            if ($fileExtensionName == 'Request') {
                $prefix = match ($arch) {
                    'http/requests/create' => 'Create',
                    'http/requests/update' => 'Update',
                };
                $name = $prefix.$name;
            }

            $filename = $folder . "/" .$name . $fileExtensionName . ".php";
            $path     = $this->makeDirectory($pathBase . "/" . $filename);

            $this->writeFile("domains/$arch", $params, $path);

            $filesBarHttp->advance();
        }
        $filesBarHttp->finish();
    }

    private function generateDomainsRespositories(string $pathTable, Stringable $tableOf)
    {
        /*
        |---------------------------------------------------
        | Cria a pasta base
        |---------------------------------------------------
        */
        $pathBase = $this->makeDirectory($pathTable."/Repositories");

        /*
        |---------------------------------------------------
        | Arquivos a serem criados
        |---------------------------------------------------
        */
        $arches = [
            'repositories/repository' => 'Repository',
        ];

        $this->info('');
        $this->comment("> Repositories");
        // gerar progress bar
        $filesBarRepository = $this->output->createProgressBar(count($arches));
        $filesBarRepository->start();

        foreach ($arches as $arch => $fileExtensionName) {
            $params = $this->getParams($tableOf);

            $name     = $tableOf->singular()->camel()->ucfirst();
            $filename = $name . $fileExtensionName . ".php";
            $path     = $this->makeDirectory($pathBase . "/" . $filename);

            $this->writeFile("domains/$arch", $params, $path);

            $filesBarRepository->advance();
        }
        $filesBarRepository->finish();
    }

    private function generateDomainsRoutes(string $pathTable, Stringable $tableOf)
    {
        /*
        |---------------------------------------------------
        | Cria a pasta base
        |---------------------------------------------------
        */
        $pathBase = $this->makeDirectory($pathTable."/routes");

        /*
        |---------------------------------------------------
        | Arquivos a serem criados
        |---------------------------------------------------
        */
        $arches = [
            'routes/api',
        ];

        $this->info('');
        $this->comment("> Routes");
        // gerar progress bar
        $filesBarRoute = $this->output->createProgressBar(count($arches));
        $filesBarRoute->start();

        foreach ($arches as $arch) {
            $params = $this->getParams($tableOf);

            $filename = $tableOf->camel() . ".php";
            $path     = $this->makeDirectory($pathBase . "/" . $filename);

            $this->writeFile("domains/$arch", $params, $path);

            $filesBarRoute->advance();
        }
        $filesBarRoute->finish();
    }

    private function generateDomainsModels(string $pathTable, Stringable $tableOf)
    {
        /*
        |---------------------------------------------------
        | Cria a pasta base
        |---------------------------------------------------
        */
        $pathBase = $this->makeDirectory($pathTable."/Models");

        /*
        |---------------------------------------------------
        | Arquivos a serem criados
        |---------------------------------------------------
        */
        $arches = [
            'models/model',
        ];

        $this->info('');
        $this->comment("> Models");
        // gerar progress bar
        $barModel = $this->output->createProgressBar(count($arches));
        $barModel->start();

        // encapsulamento
        $filesystem = $this->files;

        foreach ($arches as $arch) {
            $params = $this->getParams($tableOf) + $this->modelTable($tableOf, false);

            $filename = $tableOf->singular()->camel()->ucfirst() . ".php";
            $path     = $this->makeDirectory($pathBase . "/" . $filename);

            // busca o stub
            $stub = $filesystem->get($this->getStubEntity("domains/$arch"));
            // aplica as alterações
            $contents = $this->replace($params, $this->applyReplace($stub, $tableOf, $stub));
            // cria o arquivo
            $filesystem->put("{$path}", "{$contents}");

            //            $this->writeFile("models/$arch", $params, $path);

            $barModel->advance();
        }
        $barModel->finish();
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
        $attributesRequest = "";
        $attributesResource = "";
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
            $attributesRequest .= $this->replace($paramsBase,
                $filesystem->get($this->getStubEntity('domains/http/requests/attributes'))
            );
            $attributesResource .= $this->replace($paramsBase,
                $filesystem->get($this->getStubEntity('domains/http/resources/attributes'))
            );
        }

        return [
            'columnData'         => $columnData,
            'attributesRequest'  => $attributesRequest,
            'attributesResource' => $attributesResource,
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