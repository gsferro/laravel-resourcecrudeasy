# Resource CRUD Easy

![Logo](logo.png)

Um pacote Laravel para geração de scaffolding CRUD completo baseado em estruturas de banco de dados, com suporte a SPA (Single Page Application).

## Descrição

Resource CRUD Easy é um pacote que facilita a criação de operações CRUD (Create, Read, Update, Delete) em aplicações Laravel. Ele gera automaticamente modelos, controladores, visualizações, testes e outros arquivos necessários com base na estrutura do banco de dados.

## Requisitos

Package | Versão
--------|------------
PHP | ^8.0
Laravel | ^8.0|^9.0|^10.0
owen-it/laravel-auditing | ^12.0|^13.5.1
spatie/laravel-permission | ^5.8
your-app-rocks/eloquent-uuid | ^2.5
genealabs/laravel-model-caching | 0.*|1.*
freshbitsweb/laratables | ^2.5|^3.0
gsferro/database-schema-easy | ^1
gsferro/filtereasy | ^1.1
gsferro/responseview | ^1.2
gsferro/powermodel | ^1.3
gsferro/select2easy | ^1.2.1

## Instalação

```bash
composer require gsferro/resource-crud-easy -W
```

## Configuração

### 1. Publicar arquivos

```bash
# Publicar arquivos do Resource CRUD Easy
php artisan vendor:publish --provider="Gsferro\ResourceCrudEasy\Providers\ResourceCrudEasyServiceProvider" --tag=config

# Publicar assets (opcional)
php artisan vendor:publish --provider="Gsferro\ResourceCrudEasy\Providers\ResourceCrudEasyServiceProvider" --tag=plugins
php artisan vendor:publish --provider="Gsferro\ResourceCrudEasy\Providers\ResourceCrudEasyServiceProvider" --tag=styles
php artisan vendor:publish --provider="Gsferro\ResourceCrudEasy\Providers\ResourceCrudEasyServiceProvider" --tag=views

# Publicar arquivos de pacotes dependentes
php artisan vendor:publish --provider="OwenIt\Auditing\AuditingServiceProvider"
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan vendor:publish --provider="Gsferro\Select2Easy\Providers\Select2EasyServiceProvider" --force
```

### 2. Configuração do arquivo config/resource-crud-easy.php

```php
return [
    // Quando true, o pacote implementará automaticamente permissões para todas as operações CRUD
    'use_permissions' => true,
];
```

### 3. Configuração do Frontend

Adicione os seguintes diretivas Blade no seu layout principal:

No cabeçalho (dentro da tag `<head>`):

```blade
{{-- jQuery v3.6.4 --}}
@ResourceCrudEasyJquery()

{{-- FontAwesome v4 (opcional) --}}
@FontAwesomeV4()

{{-- Estilos CSS para DataTables --}}
@ResourceCrudEasyDatatablesExtraCss() 

{{-- Estilos CSS gerais --}}
@ResourceCrudEasyStylesCss() 

{{-- Select2Easy CSS --}}
@select2easyCss()
```

No final do corpo (antes de fechar a tag `</body>`):

```blade
{{-- Plugin DataTables JS --}}
@ResourceCrudEasyDatatablesPlugin()

{{-- Plugins JS gerais --}}
@ResourceCrudEasyPlugins()

{{-- Select2Easy JS --}}
@select2easyJs()

{{-- Configuração para DataTables usar POST --}}
<script type="text/javascript">
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        async: true
    });
    $(function(){
        {{-- Inicialização do Select2Easy --}}
        $('.select2easy:not(".select2-hidden-accessible")').select2easy();
    });
</script>

{{-- Para scripts específicos de cada página --}}
@yield('js')
```

## Comandos Disponíveis

### Gerar CRUD Completo

```bash
php artisan gsferro:resource-crud <Nome-Entidade> [opções]
```

Opções disponíveis:
- `--table=`: Nome da tabela no banco de dados (opcional)
- `--connection=`: Nome da conexão do banco de dados (opcional)
- `--model-aux`: Indica se é um modelo auxiliar
- `--datatable`: Gera suporte para DataTables
- `--factory`: Gera factory para o modelo
- `--seeder`: Gera seeder para o modelo
- `--migrate`: Gera migração para o modelo
- `--controller`: Gera controller para o modelo

Este comando irá gerar:
- Modelo
- Controlador (opcional)
- Visualizações (opcional)
- Factory (opcional)
- Seeder (opcional)
- Migração (opcional)
- Testes unitários e de feature
- Rotas

### Gerar Testes Automatizados

```bash
php artisan gsferro:resource-test [opções]
```

Opções disponíveis:
- `--model=`: Nome do modelo para gerar testes
- `--controller=`: Nome do controlador para gerar testes
- `--all`: Gera testes para todos os modelos e controladores
- `--force`: Força a sobrescrita de testes existentes

Este comando analisa a estrutura de código existente e gera testes abrangentes para modelos e controladores, incluindo:
- Testes de relacionamentos
- Testes de escopos
- Testes de validação
- Testes de API
- Testes de banco de dados

Para mais detalhes, consulte a [documentação de geração de testes](src/docs/test-generation.md).

### Gerar Componentes React para Tabelas Existentes

```bash
php artisan gsferro:resource-choice-table [opções]
```

Opções disponíveis:
- `--connection=`: Nome da conexão do banco de dados (opcional)
- `--table=`: Nome da tabela específica (opcional)
- `--modulo=`: Nome do módulo para agrupar os componentes

## Funcionalidades Principais

### Traits para Controladores

#### ResourceCrudEasy

Fornece métodos básicos para operações CRUD:
- `index()`: Exibe a listagem de registros
- `create()`: Exibe o formulário de criação
- `edit($id)`: Exibe o formulário de edição

#### ResourceCrudEasyApi

Fornece métodos para manipulação de dados:
- `store(Request $request)`: Cria um novo registro
- `update(Request $request, $id)`: Atualiza um registro existente
- `show($id)`: Exibe um registro específico
- `destroy($id)`: Remove um registro (não implementado)

### Modelos Base

#### BaseModel

Modelo base que estende o Eloquent Model e implementa:
- Auditoria (via owen-it/laravel-auditing)
- Filtragem (via gsferro/filtereasy)
- Funcionalidades avançadas (via gsferro/powermodel)

#### AuxModel

Modelo para tabelas auxiliares (lookup tables) que estende BaseModel e implementa:
- Cache (via genealabs/laravel-model-caching)
- Integração com Select2 (via gsferro/select2easy)
- Configurações padrão para tabelas simples (id, name)

### Componentes Blade

- `<x-datatables-process>`: Processamento de DataTables
- `<x-side-right-filters>`: Filtros laterais para DataTables
- `<x-form-filter>`: Formulário de filtro
- `<x-btn-edit>`: Botão de edição
- `<x-btn-register>`: Botão de registro
- `<x-link-cancel>`: Link de cancelamento

## Exemplo de Uso

### 1. Gerar um CRUD completo para uma entidade

```bash
php artisan gsferro:resource-crud Usuario --table=usuarios
```

### 2. Criar um controlador que utiliza as traits do pacote

```php
namespace App\Http\Controllers;

use App\Models\Usuario;
use Gsferro\ResourceCrudEasy\Traits\ResourceCrudEasy;
use Illuminate\Http\Request;

class UsuarioController extends Controller
{
    use ResourceCrudEasy;

    public function __construct(Usuario $model)
    {
        $this->model = $model;
    }

    // Os métodos index(), create(), edit(), store(), update() já estão implementados pela trait
}
```

### 3. Criar um modelo que estende o BaseModel

```php
namespace App\Models;

use Gsferro\ResourceCrudEasy\Models\BaseModel;

class Usuario extends BaseModel
{
    protected $fillable = ['nome', 'email', 'telefone'];

    // Regras de validação
    public static $rules = [
        'store' => [
            'nome' => 'required|string|max:255',
            'email' => 'required|email|unique:usuarios,email',
            'telefone' => 'nullable|string|max:20',
        ],
        'update' => [
            'nome' => 'required|string|max:255',
            'email' => 'required|email|unique:usuarios,email,{id}',
            'telefone' => 'nullable|string|max:20',
        ],
    ];
}
```

## Notas

- O pacote utiliza convenções para nomes de views baseados no nome da entidade (snake_case)
- As permissões são geradas automaticamente se a configuração `use_permissions` estiver ativada
- O pacote suporta tanto aplicações tradicionais quanto SPAs
