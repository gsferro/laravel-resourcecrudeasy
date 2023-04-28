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

        $this->info('');
        $this->info("Domains: {$this->modulo}");
        $this->info('');
    }

    private function generateDomainsActions(string $pathTable, Stringable $tableOf)
    {
        //        $schema = dbSchemaEasy($tableOf, $this->connection);
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
            //            'export',
            //            'get',
            //            'update',
        ];

        $this->info('');
        $this->info("Actions");
        // gerar progress bar
        $filesBarPages = $this->output->createProgressBar(count($arches));
        $filesBarPages->start();

        foreach ($arches as $arch) {
            $tableName = $tableOf->singular()->camel()->ucfirst();
            $params     = [
                // base
                '/\{{ modulo }}/'                   => $this->modulo,
                '/\{{ table_name }}/'               => $tableOf,
                '/\{{ table_singular }}/'           => $tableOf->singular(),
                '/\{{ table_title }}/'              => $tableOf->title()->replace('_', ' '),
                '/\{{ table_name_camel }}/'         => $tableOf->camel(),
                '/\{{ table_name_camel_ucfirst }}/' => $tableOf->camel()->ucfirst(),
                '/\{{ table_name_singular_camel }}/' => $tableOf->singular()->camel(),
                '/\{{ table_name_singular_camel_ucfirst }}/' => $tableName,
            ];

            // CreateProviderAction.php
            $filename = Str::ucfirst($arch) . $tableName . "Action.php";
            $path     = $this->makeDirectory($pathBase . "/" . $filename);

            // busca o stub
            $stub = $filesystem->get($this->getStubEntity('domains/actions/' . $arch));

            // aplica as alterações
            $contents = $this->replace($params, $stub);
            // cria o arquivo
            $filesystem->put("{$path}", "{$contents}");

            $filesBarPages->advance();
        }
        $filesBarPages->finish();

    }
}