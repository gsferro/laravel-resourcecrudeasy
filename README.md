![Logo](logo.png)

### Instalação:

```composer 
composer require gsferro/resource-crud-easy -W
```

### Dependências:

Package | Versão min
--------|-----------
PHP | 8

### Publish (TODO :install)
```composer 
php artisan vendor:publish --provider="Gsferro\ResourceCrudEasy\Providers\ResourceCrudEasyServiceProvider" --force
php artisan vendor:publish --provider "OwenIt\Auditing\AuditingServiceProvider"
genealabs/laravel-model-caching
```
### Config:

- No html principal, add plugin Datatables:
```text
    @DatatablesPlugin()
    @DatatablesExtraCss() {{-- style ui css datatables --}}
    @FontAwesomeV4()
    @StylesCss() {{-- style ui css --}}
    @Plugins() {{-- plugins js --}}
```

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

###