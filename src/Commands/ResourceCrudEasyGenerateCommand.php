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
    protected array      $entites = [];

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

    protected function applyReplace($stub, string $entite, string $stubType)
    {
        $localStub = $this->applyReplaceAfter($stub, $entite);
        $str       = $this->entites[ $entite ][ 'str' ];
        $params    = [
            '/\{{ class }}/'        => $str,
            '/\{{ class_camel }}/'  => $str->camel(),
            '/\{{ class_folder }}/' => $str->snake(),
            '/\{{ class_title }}/'  => $str->snake()->title()->replace('_', ' '),
            '/\{{ model }}/'        => $str,

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
        ];

        return $this->replace($params, $localStub);
    }

    protected function applyReplaceAfter($stub, ?string $entite = null)
    {
        if (is_null($entite)) {
            return $stub;
        }

        $params = [
            /*
            |---------------------------------------------------
            | Use Exists
            |---------------------------------------------------
            */
            '/\{{ use_factory_exists }}/' => $this->useFactoryExists($entite),
            '/\{{ use_seeder_exists }}/'  => $this->useSeederExists($entite),
        ];

        return $this->replace($params, $stub);
    }

    /*
    |---------------------------------------------------
    | Reuso
    |---------------------------------------------------
    */
    // TODO passar o type para o generate > buildClassEntite >
    protected function generate(string $entite, string $path, string $stub, string $message)
    {
        $contents = $this->buildClassEntite($entite, $stub);

        $this->makeDirectory($path);
        $this->put($path, $contents, $message);
    }

    protected function buildClassEntite($name, string $stubType)
    {
        $stub = $this->files->get($this->getStubEntite($stubType));

        return $this->applyReplace($stub, $name, $stubType);
//        return $this->replaceClass($stub, $name);
    }

    // OLD talvez não seja necessário fazer overread
//    protected function replaceClass($stub, $name)
//    {
//        return $this->applyReplace($stub, $name);
//    }

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

    private function useFactoryExists(string $entite): string
    {
        $class = database_path('factories/' . $entite . 'Factory.php');
        return $this->useExists($class, 'ifs/use_factory_exists');
    }

    private function useSeederExists(string $entite): string
    {
        $class = database_path('seeders/' . $entite . 'Seeder.php');
        return $this->useExists($class, 'ifs/use_seeder_exists');
    }

    private function useExists(string $class, string $stubType): string
    {
        return $this->classExists($class)
            ? $this->files->get($this->getStubEntite($stubType))
            : '';
    }

    protected function classExists(string $class): bool
    {
        return file_exists($class);
    }

    protected function replace(array $params, string $stub)
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
