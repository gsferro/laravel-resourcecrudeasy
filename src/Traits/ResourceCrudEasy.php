<?php

namespace Gsferro\ResourceCrudEasy\Traits;

use App\Models\ResourceCrud;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use DatatablesEasy\Helpers\DatatablesEasy;
use Gsferro\ResponseView\Traits\ResponseView;

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
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        if ($this->hasBreadcrumb()) {
            $this->addBreadcrumb(__('Listagem'));
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
        if ($this->model == "App\User")
            return "User";

        return str_replace("App\Models\\", "", get_class($this->model));
    }

    /*
    |---------------------------------------------------
    | Metodos Resource
    |---------------------------------------------------
    */

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        if ($this->hasBreadcrumb()) {
            $this->addBreadcrumb(__('Novo registro'));
        }
        return $this->view($this->getViewCreate());
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param $find
     * @param string $descricao
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($find)
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

    /*
    |---------------------------------------------------
    | Analisar
    |---------------------------------------------------
    */
    /*public function filtro($request)
    {
        $dados = $request->except('_token');
        // limpa os nulos
        foreach ($dados as $index => $dado) {
            if (is_null($dado)) {
                unset($dados[ $index ]);
            }
        }

        // processo de datatable
        session()->put($this->sessionName, $dados);

        $this->addData('form', $dados);
        return $this->view($this->getViewIndex());
    }

    public function grid()
    {
        // se tiver filtro repassa
        if (!empty(session($this->sessionName))) {
            return Laratables::recordsOf($this->model, function ($q) {
                return $q->where(session($this->sessionName));
            });
        }

        // se não, chama direto
        return Laratables::recordsOf($this->model);
    }*/
    
}
