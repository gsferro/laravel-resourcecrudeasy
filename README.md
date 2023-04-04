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
### Config front-end:

- No Header html principal:
```text
    {{-- jquery v3.6.4 (2023-03-08) --}}
    @ResourceCrudEasyJquery()
    {{-- style ui css datatables --}}
    @ResourceCrudEasyDatatablesExtraCss() 
    {{-- style ui css --}}
    @ResourceCrudEasyStylesCss() 
```
- No Final Body html principal:
```text
    {{-- plugin datatables js --}}
    @ResourceCrudEasyDatatablesPlugin()
    {{-- plugins js --}}
    @ResourceCrudEasyPlugins()
    {{-- para o datatables poder utilizar via post --}}
    <script type="text/javascript">
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            async: true
        });
    </script>
    {{-- index utiliza  --}}
    @yield('js')
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