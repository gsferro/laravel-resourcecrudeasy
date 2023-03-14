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
    protected string     $entite;
    protected Stringable $str;

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['entite', InputArgument::REQUIRED, 'The name of the Entite'],
        ];
    }

    protected function messageWellcome(): void
    {
        $this->br();
        $this->comment("  _____                                                         _____                      _
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

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStubEntite(string $type)
    {
        $relativePath = "/../stubs/{$type}.stub";

        return file_exists($customPath = $this->laravel->basePath(trim($relativePath, '/')))
            ? $customPath
            : __DIR__.$relativePath;
    }

    protected function applyReplace($stub)
    {
        $localStub = $this->applyReplaceAfter($stub);
        
        $params = [
            '/\{{ class }}/'        => $this->str,
            '/\{{ class_camel }}/'  => $this->str->camel(),
            '/\{{ class_folder }}/' => $this->str->snake(),
            '/\{{ class_title }}/'  => $this->str->snake()->title()->replace('_', ' '),
            '/\{{ model }}/'        => $this->str,

            // Nome tabela
            '/\{{ class_table }}/' => $this->str->snake()->plural(),

            /*
            |---------------------------------------------------
            | Especifico Route
            |---------------------------------------------------
            */
            '/\{{ class_route_slug }}/'        => $this->str->snake()->slug(),
            '/\{{ class_route_slug_plural }}/' => $this->str->snake()->slug()->plural(),
        ];

        return preg_replace(
            array_keys($params),
            array_values($params),
            $localStub
        );
    }
    
    protected function applyReplaceAfter($stub)
    {
        $params = [
            /*
            |---------------------------------------------------
            | Use Exists
            |---------------------------------------------------
            */
            '/\{{ use_factory_exists }}/' => $this->useFactoryExists(),
            '/\{{ use_seeder_exists }}/'  => $this->useSeederExists(),
        ];

        return preg_replace(
            array_keys($params),
            array_values($params),
            $stub
        );
    }

    /*
    |---------------------------------------------------
    | Reuso
    |---------------------------------------------------
    */
    protected function generate(string $path, string $stub, string $message)
    {
        $contents = $this->buildClassEntite($this->entite, $stub);

        $this->makeDirectory($path);
        $this->put($path, $contents, $message);
    }

    protected function buildClassEntite($name, string $stubType)
    {
        $stub = $this->files->get($this->getStubEntite($stubType));

        return $this->replaceClass($stub, $name);
    }

    protected function replaceClass($stub, $name)
    {
        return $this->applyReplace($stub);
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

    // ---------------------------------------------------------------------------------------
    /*
    |---------------------------------------------------
    | GeneratorCommand
    |---------------------------------------------------
    */
    protected function getStub()
    {
        // TODO: Implement getStub() method.
    }

    private function useFactoryExists()
    {
//        return $this->classExists('\Database\Factories\\'. $this->entite .'Factory' )
        return $this->classExists(database_path('factories/' . $this->entite .'Factory.php') )
            ? $this->files->get($this->getStubEntite('ifs/use_factory_exists'))
            : '';
    }
    
    private function useSeederExists()
    {
//        return $this->classExists('\Database\Seeders\\'. $this->entite .'Seeder' )
        return $this->classExists(database_path('seeders/' . $this->entite .'Seeder.php') )
            ? $this->files->get($this->getStubEntite('ifs/use_seeder_exists'))
            : '';
    }
    
    private function classExists($class): bool
    {
        return file_exists($class);
//        return class_exists($class);
    }
}
