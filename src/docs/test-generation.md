# Test Generation in ResourceCrudEasy

This document explains the test generation functionality in the ResourceCrudEasy package, including the new features for generating comprehensive tests for models and controllers.

## Overview

ResourceCrudEasy now includes enhanced test generation capabilities that can:

1. Generate comprehensive tests for models and controllers
2. Analyze existing code structure to generate appropriate tests
3. Run tests independently on both new and existing projects

## Commands

### Basic Test Generation

When generating a new model or controller using the `gsferro:resource-crud` command, tests are automatically generated based on the options you select.

```bash
php artisan gsferro:resource-crud User
```

This will generate:
- Basic model tests
- Factory tests (if factory is enabled)
- Seeder tests (if seeder is enabled)
- Controller tests (if controller is enabled)

### Advanced Test Generation

The new `gsferro:resource-test` command provides more advanced test generation capabilities:

```bash
# Generate tests for a specific model
php artisan gsferro:resource-test --model=User

# Generate tests for a specific controller
php artisan gsferro:resource-test --controller=UserController

# Generate tests for all models and controllers
php artisan gsferro:resource-test --all

# Force overwrite existing tests
php artisan gsferro:resource-test --model=User --force
```

## Types of Tests Generated

### Model Tests

1. **Basic Model Tests** (`tests/Unit/{Model}/Model/{Model}Test.php`)
   - Tests that the model is a valid Model instance
   - Tests that the model has the expected attributes
   - Tests that the model is configured to use BaseModel and has the expected traits

2. **Model Relationship Tests** (`tests/Unit/{Model}/Model/{Model}RelationshipsTest.php`)
   - Tests for each relationship method in the model
   - Tests that relationships return the correct instance types

3. **Model Scope Tests** (`tests/Unit/{Model}/Model/{Model}ScopesTest.php`)
   - Tests for each scope method in the model
   - Tests that scopes return a Builder instance
   - Tests that scopes can be chained

4. **Model Validation Tests** (`tests/Unit/{Model}/Model/{Model}ValidationTest.php`)
   - Tests that validation rules are properly defined
   - Tests that validation fails with empty data
   - Tests that validation passes with valid data
   - Tests that required fields cannot be null

5. **Factory Tests** (`tests/Unit/{Model}/Factory/{Model}FactoryTest.php`)
   - Tests that the factory is a valid Factory instance
   - Tests that the factory is configured for the correct model
   - Tests that the factory has a definition method

6. **Seeder Tests** (`tests/Unit/{Model}/Seeder/{Model}SeederTest.php`)
   - Tests that the seeder is a valid Seeder instance
   - Tests that the seeder has a run method
   - Tests that the seeder has a rows method

### Controller Tests

1. **Unit Controller Tests** (`tests/Unit/Controllers/{Controller}Test.php`)
   - Tests that the controller is a valid Controller instance
   - Tests that the controller is configured to use the ResourceCrudEasy or ResourceCrudEasyApi trait
   - Tests that the routes for the controller exist and are properly configured
   - Tests that the controller has the expected methods
   - Tests that the controller has a model attribute that is an instance of the expected model
   - Tests that the controller's rules method returns the rules defined in the model

2. **Feature Controller Tests** (`tests/Feature/Controllers/{Controller}Test.php`)
   - Tests that the index, create, and edit routes return the expected views
   - Tests that the store method saves data to the database
   - Tests that the store method fails with validation errors when invalid data is provided
   - Tests that the update method updates data in the database
   - Tests that routes are protected by roles

3. **API Controller Tests** (`tests/Feature/Api/{Controller}ApiTest.php`)
   - Tests that the API index returns JSON with pagination
   - Tests that the API show returns the correct JSON structure
   - Tests that the API store creates a new record
   - Tests that the API store validates input data
   - Tests that the API update modifies an existing record
   - Tests that the API update validates input data
   - Tests that the API destroy removes a record
   - Tests that the API returns 404 for non-existent records
   - Tests that the API requires authentication

## How It Works

The test generation functionality works by:

1. Analyzing the structure of models and controllers
2. Identifying relationships, scopes, validation rules, and other features
3. Generating appropriate tests based on the analysis

For models, it analyzes:
- Relationships
- Scopes
- Validation rules
- Attributes
- Table structure

For controllers, it analyzes:
- Methods
- Traits
- Model associations
- Routes

## Best Practices

1. **Generate tests early in development**
   - Tests help catch issues early and ensure your code works as expected

2. **Run tests regularly**
   - Use `php artisan test` to run all tests
   - Use `php artisan test --filter={TestName}` to run specific tests

3. **Update tests when you change code**
   - If you change a model or controller, regenerate tests using `gsferro:resource-test`

4. **Use test groups for organization**
   - Tests are grouped by functionality (e.g., 'relationships', 'scopes', 'validation')
   - Run specific groups with `php artisan test --group={group}`

## Extending Test Generation

You can extend the test generation functionality by:

1. Creating new test stubs in `src/stubs/tests/`
2. Modifying existing test stubs
3. Extending the `ResourceCrudEasyTestCommand` class

## Troubleshooting

If you encounter issues with test generation:

1. Make sure your models and controllers follow Laravel conventions
2. Check that your database is properly configured
3. Ensure that your models have the necessary traits and methods
4. Run tests with the `--verbose` flag for more detailed output