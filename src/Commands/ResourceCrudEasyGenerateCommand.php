<?php

namespace Gsferro\ResourceCrudEasy\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Stringable;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Support\Str;

abstract class ResourceCrudEasyGenerateCommand extends GeneratorCommand
{
    protected string     $entity;
    protected Stringable $str;
    protected array      $entitys = [];

    protected function getArguments(): array
    {
        return [
            ['entity', InputArgument::REQUIRED, 'The name of the Entity'],
        ];
    }

    protected function messageWelcome(): void
    {
        $this->br();
        $this->info("  _____                                                         _____                      _
 |  __ \                                                       / ____|                    | |
 | |__) |   ___   ___    ___    _   _   _ __    ___    ___    | |       _ __   _   _    __| |
 |  _  /   / _ \ / __|  / _ \  | | | | | '__|  / __|  / _ \   | |      | '__| | | | |  / _` |
 | | \ \  |  __/ \__ \ | (_) | | |_| | | |    | (__  |  __/   | |____  | |    | |_| | | (_| |
 |_|  \_\  \___| |___/  \___/   \__,_| |_|     \___|  \___|    \_____| |_|     \__,_|  \__,_|

                                                                                             ");
        /*$this->comment(" _____                                                         _____                      _     ______
 |  __ \                                                       / ____|                    | |   |  ____|
 | |__) |   ___   ___    ___    _   _   _ __    ___    ___    | |       _ __   _   _    __| |   | |__      __ _   ___   _   _
 |  _  /   / _ \ / __|  / _ \  | | | | | '__|  / __|  / _ \   | |      | '__| | | | |  / _` |   |  __|    / _` | / __| | | | |
 | | \ \  |  __/ \__ \ | (_) | | |_| | | |    | (__  |  __/   | |____  | |    | |_| | | (_| |   | |____  | (_| | \__ \ | |_| |
 |_|  \_\  \___| |___/  \___/   \__,_| |_|     \___|  \___|    \_____| |_|     \__,_|  \__,_|   |______|  \__,_| |___/  \__, |
                                                                                                                         __/ |
                                                                                                                        |___/ ");*/
    }

    protected function getStubEntity(string $type): string
    {
        $relativePath = "/../stubs/{$type}.stub";

        return file_exists($customPath = $this->laravel->basePath(trim($relativePath, '/')))
            ? $customPath
            : __DIR__.$relativePath;
    }

    protected function applyReplace($stub, string $entity, string $stubType): string
    {
        $localStub = $this->applyReplaceAfter($stub, $entity);
        $str       = $this->entitys[ $entity ][ 'str' ];
        $params    = [
            '/\{{ class }}/'        => $str,
            '/\{{ class_camel }}/'  => $str->camel(),
            '/\{{ class_folder }}/' => $str->snake(),
            '/\{{ class_title }}/'  => $str->headline(),
            '/\{{ model }}/'        => $str,

            // use uuid
            '/\{{ has_uuid }}/' => 'use HasUuid;',

            // Nome tabela
            '/\{{ class_table }}/' => $str->snake()->plural(),

            /*
            |---------------------------------------------------
            | Especifico Route
            |---------------------------------------------------
            */
            '/\{{ class_route_slug }}/'        => $str->snake()->slug(),
            '/\{{ class_route_slug_plural }}/' => $str->snake()->slug()->plural(),

            /*
            |---------------------------------------------------
            | Using Table
            |---------------------------------------------------
            */
            '/\{{ factory_fillables }}/' => '// Configure column´s of Model',
            '/\{{ seeder_fillables }}/'  => '//',
            '/\{{ migrate_fillables }}/' => '$table->uuid(\'uuid\');'.PHP_EOL.PHP_EOL.'$table->timestamps();',
            '/\{{ migrate_relation }}/'  => '',

            /*
            |---------------------------------------------------
            | Datatables
            |---------------------------------------------------
            */
            '/\{{ datatable_grid }}/'    => '',
            '/\{{ datatable_columns }}/' => '',
        ];

        return $this->replace($params, $localStub);
    }

    protected function applyReplaceAfter($stub, ?string $entity = null): string
    {
        if (is_null($entity)) {
            return $stub;
        }

        $params = [
            /*
            |---------------------------------------------------
            | Use Exists
            |---------------------------------------------------
            */
            '/\{{ use_factory_exists }}/' => $this->useFactoryExists($entity),
            '/\{{ use_seeder_exists }}/'  => $this->useSeederExists($entity),
        ];

        return $this->replace($params, $stub);
    }

    /*
    |---------------------------------------------------
    | Reuso
    |---------------------------------------------------
    */
    protected function generate(string $entity, string $path, string $stub, string $message)
    {
        $contents = $this->buildClassEntity($entity, $stub);

        $this->makeDirectory($path);
        $this->put($path, $contents, $message);
    }

    protected function buildClassEntity($name, string $stubType): string
    {
        $stub = $this->files->get($this->getStubEntity($stubType));
        return $this->applyReplace($stub, $name, $stubType);
    }

    protected function put($path, $contents, string $message)
    {
        $this->files->put("{$path}", "{$contents}");

        $this->message($path, $message);
    }

    protected function message($path, string $message)
    {
        $this->comment(">> $message created:");
        $this->comment("$path");
        $this->br();
    }

    private function br()
    {
        $this->line('');
    }

    private function useFactoryExists(string $entity): string
    {
        $class = database_path('factories/' . $entity . 'Factory.php');
        return $this->useExists($class, 'ifs/use_factory_exists');
    }

    private function useSeederExists(string $entity): string
    {
        $class = database_path('seeders/' . $entity . 'Seeder.php');
        return $this->useExists($class, 'ifs/use_seeder_exists');
    }

    private function useExists(string $class, string $stubType): string
    {
        return $this->classExists($class)
            ? $this->files->get($this->getStubEntity($stubType))
            : '';
    }

    protected function classExists(string $class): bool
    {
        return file_exists($class);
    }

    protected function replace(array $params, string $stub): string
    {
        return preg_replace(
            array_keys($params),
            array_values($params),
            $stub
        );
    }

    /*
    |---------------------------------------------------
    | GeneratorCommand
    |---------------------------------------------------
    */
    protected function getStub()
    {
        // TODO: Implement getStub() method.
    }
}
