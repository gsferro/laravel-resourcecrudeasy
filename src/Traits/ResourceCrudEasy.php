<?php

namespace Gsferro\ResourceCrudEasy\Traits;

use Illuminate\Contracts\View\Factory;
use Gsferro\ResponseView\Traits\ResponseView;
use Illuminate\View\View;

/**
 * Reuso generico as operações de crud e response ajax
 *
 * @author  Guilherme Ferro
 * @version 1.3
 *
 * @release 1.1 - possibilidade de usar find ou findByUuid
 * @release 1.2 - convenção dos nomes de viewIndex e Form conforme nome da Entidade
 * @release 1.3 - convenção de breadcumb caso não sete no controller, coloca o titulo
 *
 * @construct
 * @field   model Injeção dependencia
 * @field   viewIndex ResponseView->view
 * @field   viewForm ResponseView->view
 *
 * @depencencias
 * @trait   ResponseJson
 * @trait   GSFerro/ResponseView
 *
 * @package Gsferro\ResourceCrudEasy
 */
trait ResourceCrudEasy
{
    use ResponseView, ResouceCrudViews, ResourceCrudEasyApi;

    /*
    |---------------------------------------------------
    | Use breadcrumb
    |---------------------------------------------------
    */
    protected bool $useBreadcrumb = true;

    /*
    |---------------------------------------------------
    | redirect from actoin
    |---------------------------------------------------
    |
    | TODO revisar redirect
    |
    */
//    protected string $redirectStore  = 'index';
//    protected string $redirectUpdate = 'index';

    /*
    |---------------------------------------------------
    | Pegar as view pela convenção do nome da Entidade
    |---------------------------------------------------
    |
    | Ex:
    | Entidade (model) -> UsuariosAutorizados (camelCase)
    | Pasta (resources > views) -> usuarios_autorizados (snake_case)
    |
    */

    /**
     * Display a listing of the resource.
     *
     * @return Factory|View
     */
    public function index(): Factory|View
    {
        if ($this->hasBreadcrumb()) {
            $this->addBreadcrumb(__('Listagem'));
        }

        /*
        |---------------------------------------------------
        | Controla a filtragem da tela index
        |---------------------------------------------------
        */
        if (!empty($this->sessionName)) {
            $this->addData('form', session()->get($this->sessionName));
        }

        return $this->view($this->getViewIndex());
    }

    /**
     * Pega o nome da Entidade (model)
     *
     * @return string|string[]
     */
    private function getEntidade(): string
    {
        $class = get_class($this->model);
        if ($class == "App\Models\User") {
            return "Users";
        }

        return str_replace("App\Models\\", "", $class);
    }

    /*
    |---------------------------------------------------
    | Metodos Resource
    |---------------------------------------------------
    */

    /**
     * Show the form for creating a new resource.
     *
     * @return Factory|View
     */
    public function create(): Factory|View
    {
        if ($this->hasBreadcrumb()) {
            $this->addBreadcrumb(__('Novo registro'));
        }
        return $this->view($this->getViewCreate());
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param string|int $find
     * @return Factory|View
     */
    public function edit(string|int $find): Factory|View
    {
        $this->addData('model', $this->modelFind($find));

        if ($this->hasBreadcrumb()) {
            $this->addBreadcrumb(__('Editar'));
        }
        return $this->view($this->getViewEdit());
    }

    /*
    |---------------------------------------------------
    | Metodos Reuso
    |---------------------------------------------------
    */

    public function hasBreadcrumb(): bool
    {
        return $this->useBreadcrumb;
    }
}
