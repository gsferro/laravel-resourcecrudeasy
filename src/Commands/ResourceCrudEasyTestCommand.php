<?php

namespace Gsferro\ResourceCrudEasy\Commands;

use Gsferro\ResourceCrudEasy\Traits\Commands\{WithExistsTableCommand, UtilCommand, UseModelCommand, UseControllerCommand};
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;

class ResourceCrudEasyTestCommand extends ResourceCrudEasyGenerateCommand
{
    use WithExistsTableCommand, UtilCommand, UseModelCommand, UseControllerCommand;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'gsferro:resource-test';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gsferro:resource-test
    {--model= : Model class name to generate tests for}
    {--controller= : Controller class name to generate tests for}
    {--all : Generate tests for all models and controllers}
    {--force : Force overwrite existing tests}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate comprehensive tests for models and controllers';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->messageWelcome();

        if ($this->option('all')) {
            $this->generateTestsForAllModels();
            $this->generateTestsForAllControllers();
            return;
        }

        if ($model = $this->option('model')) {
            $this->generateTestsForModel($model);
        }

        if ($controller = $this->option('controller')) {
            $this->generateTestsForController($controller);
        }

        if (!$this->option('model') && !$this->option('controller') && !$this->option('all')) {
            $this->error('Please specify a model, controller, or use --all option.');
        }
    }

    /**
     * Generate tests for all models in the application.
     *
     * @return void
     */
    protected function generateTestsForAllModels()
    {
        $this->info('Generating tests for all models...');
        
        $modelFiles = File::glob(app_path('Models/*.php'));
        $bar = $this->output->createProgressBar(count($modelFiles));
        $bar->start();
        
        foreach ($modelFiles as $modelFile) {
            $modelName = pathinfo($modelFile, PATHINFO_FILENAME);
            $this->generateTestsForModel($modelName);
            $bar->advance();
        }
        
        $bar->finish();
        $this->info("\nAll model tests generated successfully!");
    }

    /**
     * Generate tests for all controllers in the application.
     *
     * @return void
     */
    protected function generateTestsForAllControllers()
    {
        $this->info('Generating tests for all controllers...');
        
        $controllerFiles = File::glob(app_path('Http/Controllers/*.php'));
        $bar = $this->output->createProgressBar(count($controllerFiles));
        $bar->start();
        
        foreach ($controllerFiles as $controllerFile) {
            $controllerName = pathinfo($controllerFile, PATHINFO_FILENAME);
            $this->generateTestsForController($controllerName);
            $bar->advance();
        }
        
        $bar->finish();
        $this->info("\nAll controller tests generated successfully!");
    }

    /**
     * Generate tests for a specific model.
     *
     * @param string $modelName
     * @return void
     */
    protected function generateTestsForModel($modelName)
    {
        $this->info("Generating tests for model: $modelName");
        
        // Set up entity data structure
        $entity = str_replace('App\\Models\\', '', $modelName);
        $entity = str_replace('\\', '', $entity);
        
        $this->entity = $entity;
        $this->entitys[$entity] = [
            'str' => Str::of($entity),
            'useFactory' => $this->hasFactory($entity),
            'useSeeder' => $this->hasSeeder($entity),
        ];
        
        // Analyze model structure
        $this->analyzeModelStructure($entity);
        
        // Generate tests
        $this->generateModelTests($entity);
        
        $this->info("Tests for model $modelName generated successfully!");
    }

    /**
     * Generate tests for a specific controller.
     *
     * @param string $controllerName
     * @return void
     */
    protected function generateTestsForController($controllerName)
    {
        $this->info("Generating tests for controller: $controllerName");
        
        // Extract entity name from controller name
        $entity = str_replace('Controller', '', $controllerName);
        $entity = str_replace('App\\Http\\Controllers\\', '', $entity);
        $entity = str_replace('\\', '', $entity);
        
        $this->entity = $entity;
        $this->entitys[$entity] = [
            'str' => Str::of($entity),
            'useController' => true,
            'useControllerApi' => $this->isApiController($controllerName),
        ];
        
        // Analyze controller structure
        $this->analyzeControllerStructure($entity, $controllerName);
        
        // Generate tests
        $this->generateControllerTests($entity);
        
        $this->info("Tests for controller $controllerName generated successfully!");
    }

    /**
     * Check if a model has a factory.
     *
     * @param string $entity
     * @return bool
     */
    protected function hasFactory($entity)
    {
        return file_exists(database_path("factories/{$entity}Factory.php"));
    }

    /**
     * Check if a model has a seeder.
     *
     * @param string $entity
     * @return bool
     */
    protected function hasSeeder($entity)
    {
        return file_exists(database_path("seeders/{$entity}Seeder.php"));
    }

    /**
     * Check if a controller is an API controller.
     *
     * @param string $controllerName
     * @return bool
     */
    protected function isApiController($controllerName)
    {
        $controllerClass = "App\\Http\\Controllers\\$controllerName";
        if (!class_exists($controllerClass)) {
            return false;
        }
        
        $controller = new $controllerClass();
        return property_exists($controller, 'isAPI') && $controller->isAPI;
    }

    /**
     * Analyze the structure of a model.
     *
     * @param string $entity
     * @return void
     */
    protected function analyzeModelStructure($entity)
    {
        $modelClass = "App\\Models\\$entity";
        if (!class_exists($modelClass)) {
            $this->error("Model $modelClass does not exist.");
            return;
        }
        
        try {
            $reflection = new ReflectionClass($modelClass);
            
            // Get relationships
            $relationships = [];
            $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
            
            foreach ($methods as $method) {
                if ($method->class === $modelClass) {
                    $methodName = $method->getName();
                    
                    // Skip non-relationship methods
                    if (in_array($methodName, ['__construct', 'boot', 'booted'])) {
                        continue;
                    }
                    
                    // Try to determine if this is a relationship method
                    try {
                        $instance = new $modelClass();
                        if (method_exists($instance, $methodName)) {
                            $returnType = $method->getReturnType();
                            if ($returnType && strpos($returnType->getName(), 'Illuminate\\Database\\Eloquent\\Relations') !== false) {
                                $relationships[] = $methodName;
                            } else {
                                // Check if the method returns a relation
                                $result = $instance->$methodName();
                                if (is_object($result) && strpos(get_class($result), 'Illuminate\\Database\\Eloquent\\Relations') !== false) {
                                    $relationships[] = $methodName;
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        // If we can't instantiate or call the method, skip it
                        continue;
                    }
                }
            }
            
            // Get scopes
            $scopes = [];
            foreach ($methods as $method) {
                if ($method->class === $modelClass && strpos($method->getName(), 'scope') === 0) {
                    $scopes[] = lcfirst(substr($method->getName(), 5));
                }
            }
            
            // Store the analysis results
            $this->entitys[$entity]['relationships'] = $relationships;
            $this->entitys[$entity]['scopes'] = $scopes;
            $this->entitys[$entity]['hasRules'] = property_exists($modelClass, 'rules');
            
            // Check if model uses BaseModel
            $this->entitys[$entity]['usesBaseModel'] = is_subclass_of($modelClass, 'Gsferro\\ResourceCrudEasy\\Models\\BaseModel');
            
            // Get table information if possible
            try {
                $instance = new $modelClass();
                $table = $instance->getTable();
                $this->entitys[$entity]['table'] = $table;
                
                // Try to get schema information
                if (function_exists('dbSchemaEasy')) {
                    $schema = dbSchemaEasy($table);
                    $this->entitys[$entity]['schema'] = $schema;
                    $this->entitys[$entity]['columnListing'] = $schema->getColumnListing();
                }
            } catch (\Exception $e) {
                // If we can't get table information, continue without it
            }
            
        } catch (\Exception $e) {
            $this->error("Error analyzing model structure: " . $e->getMessage());
        }
    }

    /**
     * Analyze the structure of a controller.
     *
     * @param string $entity
     * @param string $controllerName
     * @return void
     */
    protected function analyzeControllerStructure($entity, $controllerName)
    {
        $controllerClass = "App\\Http\\Controllers\\$controllerName";
        if (!class_exists($controllerClass)) {
            $this->error("Controller $controllerClass does not exist.");
            return;
        }
        
        try {
            $reflection = new ReflectionClass($controllerClass);
            
            // Get methods
            $methods = [];
            foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                if ($method->class === $controllerClass) {
                    $methods[] = $method->getName();
                }
            }
            
            // Check for ResourceCrudEasy traits
            $traits = class_uses_recursive($controllerClass);
            $usesCrudEasy = isset($traits['Gsferro\\ResourceCrudEasy\\Traits\\ResourceCrudEasy']);
            $usesCrudEasyApi = isset($traits['Gsferro\\ResourceCrudEasy\\Traits\\ResourceCrudEasyApi']);
            
            // Store the analysis results
            $this->entitys[$entity]['methods'] = $methods;
            $this->entitys[$entity]['usesCrudEasy'] = $usesCrudEasy;
            $this->entitys[$entity]['usesCrudEasyApi'] = $usesCrudEasyApi;
            
            // Try to get model information
            try {
                $controller = new $controllerClass();
                if (property_exists($controller, 'model')) {
                    $modelClass = get_class($controller->model);
                    $this->entitys[$entity]['modelClass'] = $modelClass;
                    
                    // If we haven't analyzed the model yet, do it now
                    $modelEntity = str_replace('App\\Models\\', '', $modelClass);
                    if (!isset($this->entitys[$modelEntity])) {
                        $this->analyzeModelStructure($modelEntity);
                    }
                }
            } catch (\Exception $e) {
                // If we can't get model information, continue without it
            }
            
        } catch (\Exception $e) {
            $this->error("Error analyzing controller structure: " . $e->getMessage());
        }
    }

    /**
     * Generate tests for a model.
     *
     * @param string $entity
     * @return void
     */
    protected function generateModelTests($entity)
    {
        // Generate basic model test
        $this->generatePestUnitModel($entity);
        
        // Generate factory test if applicable
        if ($this->entitys[$entity]['useFactory']) {
            $this->generatePestUnitFactory($entity);
        }
        
        // Generate seeder test if applicable
        if ($this->entitys[$entity]['useSeeder']) {
            $this->generatePestUnitSeeder($entity);
        }
        
        // Generate relationship tests if applicable
        if (!empty($this->entitys[$entity]['relationships'])) {
            $this->generatePestUnitModelRelationships($entity);
        }
        
        // Generate scope tests if applicable
        if (!empty($this->entitys[$entity]['scopes'])) {
            $this->generatePestUnitModelScopes($entity);
        }
        
        // Generate validation tests if applicable
        if ($this->entitys[$entity]['hasRules']) {
            $this->generatePestUnitModelValidation($entity);
        }
    }

    /**
     * Generate tests for a controller.
     *
     * @param string $entity
     * @return void
     */
    protected function generateControllerTests($entity)
    {
        // Generate unit controller test
        $this->generatePestUnitController($entity);
        
        // Generate feature controller test
        $this->generatePestFeatureController($entity);
        
        // Generate API tests if applicable
        if ($this->entitys[$entity]['useControllerApi']) {
            $this->generatePestFeatureApiController($entity);
        }
    }

    /**
     * Generate unit tests for model relationships.
     *
     * @param string $entity
     * @return void
     */
    protected function generatePestUnitModelRelationships($entity)
    {
        $path = 'tests\Unit\\' . $entity . '\Model\\' . $entity . 'RelationshipsTest.php';
        $this->generate($entity, $path, 'tests/unit/model_relationships', 'PestTest Unit Model Relationships');
    }

    /**
     * Generate unit tests for model scopes.
     *
     * @param string $entity
     * @return void
     */
    protected function generatePestUnitModelScopes($entity)
    {
        $path = 'tests\Unit\\' . $entity . '\Model\\' . $entity . 'ScopesTest.php';
        $this->generate($entity, $path, 'tests/unit/model_scopes', 'PestTest Unit Model Scopes');
    }

    /**
     * Generate unit tests for model validation.
     *
     * @param string $entity
     * @return void
     */
    protected function generatePestUnitModelValidation($entity)
    {
        $path = 'tests\Unit\\' . $entity . '\Model\\' . $entity . 'ValidationTest.php';
        $this->generate($entity, $path, 'tests/unit/model_validation', 'PestTest Unit Model Validation');
    }

    /**
     * Generate feature tests for API controller.
     *
     * @param string $entity
     * @return void
     */
    protected function generatePestFeatureApiController($entity)
    {
        $path = 'tests\Feature\Api\\' . $entity . 'ApiTest.php';
        $this->generate($entity, $path, 'tests/feature/api_controller', 'PestTest Feature API Controller');
    }

    /**
     * Apply replacements specific to this command.
     *
     * @param string $stub
     * @param string $entity
     * @param string $stubType
     * @return string
     */
    protected function applyReplace($stub, string $entity, string $stubType): string
    {
        $stub = parent::applyReplace($stub, $entity, $stubType);
        
        $params = [];
        
        // Add relationships to the stub if applicable
        if (isset($this->entitys[$entity]['relationships']) && !empty($this->entitys[$entity]['relationships'])) {
            $relationshipTests = '';
            foreach ($this->entitys[$entity]['relationships'] as $relationship) {
                $relationshipTests .= "test('{$relationship} relationship exists and works correctly', function() {\n";
                $relationshipTests .= "    expect(method_exists(\$this->model, '{$relationship}'))->toBeTrue();\n";
                $relationshipTests .= "    expect(\$this->model->{$relationship}())->toBeObject();\n";
                $relationshipTests .= "});\n\n";
            }
            $params['/\{{ relationship_tests }}/'] = $relationshipTests;
        } else {
            $params['/\{{ relationship_tests }}/'] = '';
        }
        
        // Add scopes to the stub if applicable
        if (isset($this->entitys[$entity]['scopes']) && !empty($this->entitys[$entity]['scopes'])) {
            $scopeTests = '';
            foreach ($this->entitys[$entity]['scopes'] as $scope) {
                $scopeTests .= "test('{$scope} scope exists and works correctly', function() {\n";
                $scopeTests .= "    expect(\$this->model->hasNamedScope('{$scope}'))->toBeTrue();\n";
                $scopeTests .= "});\n\n";
            }
            $params['/\{{ scope_tests }}/'] = $scopeTests;
        } else {
            $params['/\{{ scope_tests }}/'] = '';
        }
        
        // Add controller methods to the stub if applicable
        if (isset($this->entitys[$entity]['methods']) && !empty($this->entitys[$entity]['methods'])) {
            $methodTests = '';
            foreach ($this->entitys[$entity]['methods'] as $method) {
                // Skip common methods that are already tested
                if (in_array($method, ['__construct', 'index', 'create', 'store', 'show', 'edit', 'update', 'destroy'])) {
                    continue;
                }
                
                $methodTests .= "test('{$method} method exists', function() {\n";
                $methodTests .= "    expect(method_exists(\$this->controller, '{$method}'))->toBeTrue();\n";
                $methodTests .= "});\n\n";
            }
            $params['/\{{ method_tests }}/'] = $methodTests;
        } else {
            $params['/\{{ method_tests }}/'] = '';
        }
        
        return $this->replace($params, $stub);
    }
}