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

//        $this->info('');
//        $this->info("Domains: {$this->modulo}");
//        $this->info('');
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
            $filename = $tableOf->camel()->ucfirst() . "Bag.php";
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
}