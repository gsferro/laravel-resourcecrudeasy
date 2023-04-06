![Logo](logo.png)

### Instalação:

```composer 
composer require gsferro/resource-crud-easy -W
```

### Pacotes Dependências:

Package | Versão
--------|-----------
PHP | 8.*
Laravel | ^8.*
owen-it/laravel-auditing | ^12.0
spatie/laravel-permission | ^5.8
your-app-rocks/eloquent-uuid | ^2.5
genealabs/laravel-model-caching | ^0.11.7
freshbitsweb/laratables | ^2.5
gsferro/database-schema-easy | ^1
gsferro/filtereasy | ^1.1
gsferro/responseview" | ^1.2
gsferro/powermodel | ^1.3
gsferro/select2easy | ^1.2.1

### Publish (TODO :install)
```composer 
php artisan vendor:publish --provider="Gsferro\ResourceCrudEasy\Providers\ResourceCrudEasyServiceProvider" --force
php artisan vendor:publish --provider "OwenIt\Auditing\AuditingServiceProvider"
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan vendor:publish --provider="Gsferro\Select2Easy\Providers\Select2EasyServiceProvider" --force
```
### Config front-end:

- No Header html principal:
```text
    {{-- jquery v3.6.4 (2023-03-08) --}}
    @ResourceCrudEasyJquery()
    {{-- opcional fontawesome v4 --}}
    @FontAwesomeV4()
    {{-- style ui css datatables --}}
    @ResourceCrudEasyDatatablesExtraCss() 
    {{-- style ui css --}}
    @ResourceCrudEasyStylesCss() 
    
    {{-- select2easy --}}
    @select2easyCss()
```
- No Final Body html principal:
```text
    {{-- plugin datatables js --}}
    @ResourceCrudEasyDatatablesPlugin()
    {{-- plugins js --}}
    @ResourceCrudEasyPlugins()
    {{-- select2easy --}}
    @select2easyJs()
    {{-- para o datatables poder utilizar via post --}}
    <script type="text/javascript">
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            async: true
        });
        $(function(){
            {{-- select2easy run --}}
            $('.select2easy:not(".select2-hidden-accessible")').select2easy();
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