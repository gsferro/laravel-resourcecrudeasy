![Logo](logo.png)

### Instalação:

```composer 
composer require gsferro/resource-crud-easy && php artisan vendor:publish --provider="Gsferro\ResourceCrudEasy\Providers\ResourceCrudEasyServiceProvider" --force
```

### Dependências:

Package | Versão min
--------|-----------
PHP | 8

### Uso:

- Criar Crud Completo
```text 
php artisan gsferro:resource-crud <Nome-Entidade> {--table=} {--connection=} {--factory} {--seeder} {--migrate}
```

- Criar Model
```text 
php artisan gsferro:resource-crud-model <Nome-Entidade> {--table=} {--connection=} {--factory} {--seeder} {--migrate}
```

### Obs:
