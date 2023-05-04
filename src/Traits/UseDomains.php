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
        $this->info('');
        $this->comment("► ► ► Generate Domains Files");
        $this->info('');

        $this->tableOf = Str::of($table);
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
        $tableName = $this->tableOf->singular()->camel()->ucfirst();
        $pathTable = $this->makeDirectory($pathBase . "/" . $tableName);

        /*
        |---------------------------------------------------
        | Criar pastas
        |---------------------------------------------------
        */
        $this->generateDomainsActions($pathTable);
        $this->generateDomainsBags($pathTable);
        $this->generateDomainsCriteria($pathTable);
        $this->generateDomainsExport($pathTable);
        $this->generateDomainsHttps($pathTable);
        $this->generateDomainsRespositories($pathTable);
        $this->generateDomainsRoutes($pathTable);
        $this->generateDomainsModels($pathTable);
        $this->generateDefenderPermission();

        $this->info('');
    }

    private function generateDomainsActions(string $pathTable)
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
        $this->info("► Actions");
        // gerar progress bar
        $filesBarActions = $this->output->createProgressBar(count($arches));
        $filesBarActions->start();

        foreach ($arches as $arch) {
            $params = $this->getParams();

            $tableNameUse = match ($arch) {
                'export', 'get' => $this->tableOf->camel()->ucfirst(),
                default => $this->tableOf->singular()->camel()->ucfirst()
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

    private function generateDomainsBags(string $pathTable)
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
        $this->info("► Bags");
        // gerar progress bar
        $filesBarBags = $this->output->createProgressBar(count($arches));
        $filesBarBags->start();

        foreach ($arches as $arch) {
            $params   = $this->getParams();
            $filename = $this->tableOf->singular()->camel()->ucfirst() . "Bag.php";
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

    private function generateDomainsCriteria(string $pathTable)
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
        $this->info("► Criteria");
        // gerar progress bar
        $filesBarCriteria = $this->output->createProgressBar(count($arches));
        $filesBarCriteria->start();

        foreach ($arches as $arch) {
            $params   = $this->getParams();
            $filename = $this->tableOf->camel()->ucfirst() . "ListCriteria.php";
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

    private function generateDomainsExport(string $pathTable)
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
        $this->info("► Export");
        // gerar progress bar
        $filesBarExport = $this->output->createProgressBar(count($arches));
        $filesBarExport->start();

        $columnData = $this->getExtraParamsDomains()['columnData'];

        foreach ($arches as $arch) {
            $params   = $this->getParams() + [
                '/\{{ column_data }}/' => trim($columnData),
            ];
            $filename = $this->tableOf->camel()->ucfirst() . "Export.php";
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

    private function generateDomainsHttps(string $pathTable)
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
        $this->info("► Http");
        // gerar progress bar
        $filesBarHttp = $this->output->createProgressBar(count($arches));
        $filesBarHttp->start();

        $extraParamsDomains = $this->getExtraParamsDomains();
        $attributesRequest  = $extraParamsDomains['attributesRequest'];
        $attributesResource = $extraParamsDomains['attributesResource'];

        foreach ($arches as $arch => $fileExtensionName) {
            $params = $this->getParams() + [
                '/\{{ attributes_request }}/' => trim($attributesRequest),
                '/\{{ attributes_resource }}/' => trim($attributesResource),
            ];

            $folder = match ($fileExtensionName) {
                'Controller' => 'Controllers',
                'Request'    => 'Requests',
                'Resource'   => 'Resources',
            };

            $name = $this->tableOf->singular()->camel()->ucfirst();

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

    private function generateDomainsRespositories(string $pathTable)
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
        $this->info("► Repositories");
        // gerar progress bar
        $filesBarRepository = $this->output->createProgressBar(count($arches));
        $filesBarRepository->start();

        foreach ($arches as $arch => $fileExtensionName) {
            $params = $this->getParams();

            $name     = $this->tableOf->singular()->camel()->ucfirst();
            $filename = $name . $fileExtensionName . ".php";
            $path     = $this->makeDirectory($pathBase . "/" . $filename);

            $this->writeFile("domains/$arch", $params, $path);

            $filesBarRepository->advance();
        }
        $filesBarRepository->finish();
    }

    private function generateDomainsRoutes(string $pathTable)
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
        $this->info("► Routes");
        // gerar progress bar
        $filesBarRoute = $this->output->createProgressBar(count($arches));
        $filesBarRoute->start();

        foreach ($arches as $arch) {
            $params = $this->getParams();

            $filename = $this->tableOf->camel() . ".php";
            $path     = $this->makeDirectory($pathBase . "/" . $filename);

            $this->writeFile("domains/$arch", $params, $path);

            $filesBarRoute->advance();
        }
        $filesBarRoute->finish();
    }

    private function generateDomainsModels(string $pathTable)
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
        $this->info("► Models");
        // gerar progress bar
        $barModel = $this->output->createProgressBar(count($arches));
        $barModel->start();

        // encapsulamento
        $filesystem = $this->files;

        foreach ($arches as $arch) {
            $params = $this->getParams() + $this->modelTable($this->tableOf, false);

            $filename = $this->tableOf->singular()->camel()->ucfirst() . ".php";
            $path     = $this->makeDirectory($pathBase . "/" . $filename);

            // busca o stub
            $stub = $filesystem->get($this->getStubEntity("domains/$arch"));
            // aplica as alterações
            $contents = $this->replace($params, $this->applyReplace($stub, $this->tableOf, $stub));
            // cria o arquivo
            $filesystem->put("{$path}", "{$contents}");

            //            $this->writeFile("models/$arch", $params, $path);

            $barModel->advance();
        }
        $barModel->finish();
    }

    private function generateDefenderPermission()
    {
        /*
        |---------------------------------------------------
        | Cria a pasta base
        |---------------------------------------------------
        */
        $pathBase = database_path('seeders/' . $this->tableOf . 'PermissionSeeder.php');;

        /*
        |---------------------------------------------------
        | Arquivos a serem criados
        |---------------------------------------------------
        */
        $arches = [
            'permissions/seeder_defender',
        ];

        $this->info('');
        $this->info("► Permission Seeder");
        // gerar progress bar
        $barSeeder = $this->output->createProgressBar(count($arches));
        $barSeeder->start();

        foreach ($arches as $arch) {
            $params = $this->getParams();

            $filename = $this->tableOf->singular()->camel()->ucfirst() . ".php";
            $path     = $this->makeDirectory($pathBase . "/" . $filename);

            $this->writeFile("$arch", $params, $path);

            $barSeeder->advance();
        }
        $barSeeder->finish();
    }

    /*
    |---------------------------------------------------
    | Reuso
    |---------------------------------------------------
    */
    private function getParams(): array
    {
        return [
            // base
            '/\{{ modulo }}/'                            => $this->modulo,
            '/\{{ table_name }}/'                        => $this->tableOf,
            '/\{{ table_singular }}/'                    => $this->tableOf->singular(),
            '/\{{ table_title }}/'                       => $this->tableOf->title()->replace('_', ' '),
            '/\{{ table_name_camel }}/'                  => $this->tableOf->camel(),
            '/\{{ table_name_camel_ucfirst }}/'          => $this->tableOf->camel()->ucfirst(),
            '/\{{ table_name_singular_camel }}/'         => $this->tableOf->singular()->camel(),
            '/\{{ table_name_singular_camel_ucfirst }}/' => $this->tableOf->singular()->camel()->ucfirst(),
        ];
    }

    private function getExtraParamsDomains(): array
    {
        $schema        = dbSchemaEasy($this->tableOf, $this->connection);
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
                '/\{{ table_name_singular_camel }}/' => $this->tableOf->singular()->camel(),
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