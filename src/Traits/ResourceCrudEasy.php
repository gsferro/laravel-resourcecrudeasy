<?php

namespace Gsferro\ResourceCrudEasy\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use DatatablesEasy\Helpers\DatatablesEasy;
use Illuminate\Support\Facades\Validator;
use Exception;
use Gsferro\ResponseView\Traits\ResponseView;
use Illuminate\Support\Str;

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
    use ResponseJSON, ResponseView;

    public Model $model;
    protected string $viewIndex;
    protected string $viewCreate;
    protected string $viewEdit;
    protected string $viewForm;
    protected bool   $useBreadcrumb          = true;
    protected bool   $redirectStoreYourserf  = false;
    protected bool   $redirectUpdateYourserf = false;
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
     * Verifica se foi setado um caminho para a view index ou retorna via conveção
     *
     * @return string
     */
    public function getViewIndex()
    {
        if (!empty($this->viewIndex)) {
            return $this->viewIndex;
        }

        return $this->getPathView('index');
    }
    
    public function getViewCreate()
    {
        if (!empty($this->viewCreate)) {
            return $this->viewCreate;
        }

        return $this->getPathView('create');
    }
    public function getViewEdit()
    {
        if (!empty($this->viewEdit)) {
            return $this->viewEdit;
        }

        return $this->getPathView('edit');
    }

    /**
     * Verifica se foi setado um caminho para a view form ou retorna via conveção
     *
     * @return string
     */
    public function getViewForm()
    {
        if (!empty($this->viewForm)) {
            return $this->viewForm;
        }

        return $this->getPathView('form');
    }

    /**
     * retorna a caminho da view de acordo com a Entidade (model)
     *
     * @param $view
     * @return string
     */
    private function getPathView($view)
    {
        return Str::of($this->getEntidade())->snake() . ".{$view}";
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
    | Return views
    |---------------------------------------------------
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

    public function filtro($request)
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
    }

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

    /**
     * Display the specified resource.
     *
     * @param $uuid
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show($uuid)
    {
        /*
         * model->viewShow() : array
         *
         * Para exibir na view generica modal.show
         *
         * (key => (string) value )
         * field => label
         *
         * (key => (array) value ) - relacionamento
         * relacionamento => [
         *   field_relacionamento => label
         * ]
         *
         * (key => (array)) - modificação do campo
         * _callback => [
         *      "label"=> "Label",
         *      "method" => "showNomeDaCallback", // declarar metodo
         * ]
         * */

        $this->addData('fields', $this->model->viewShow());
        $this->addData('model', $this->modelFind($uuid));
        $this->addData("relations", $this->model->viewShowRelations($this->modelFind($uuid)));
        return $this->view('modais.show');
    }
    /////////////////////////////////////////////////////////////////////////////////////////////////
    /*
    |---------------------------------------------------
    | Operações
    |---------------------------------------------------
    */
    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // encapsulamento do request com sanitize
        $dados = sanitize($request->all());

        // validação backend se for necessário
        if (isset($this->model->rules[ "store" ])) {
            $validator = Validator::make($dados, $this->model->rules[ "store" ] ?? []);
            if ($validator->fails()) {
                return $this->validateFails(__('messages.56'), $validator->messages()->toArray());
            }
        }

        //////////////////////////////////////////////////////////////////////////////////
        if (!empty($request->file('foto'))) {
            try {
                $dados[ "foto" ] = uploadFoto($request);
            } catch (Exception $e) {
                return $this->error($e->getMessage());
            }
        } else {
            unset($dados[ "foto" ]);
        }
        //////////////////////////////////////////////////////////////////////////////////

        //Inicia o Database Transaction
        DB::beginTransaction();
        $result = $this->model->create($dados);
        //dd($dados, $result);
        if (!$result) {
            DB::rollBack();
            logCreate("Cadastrar dados na model " . get_class($this->model), "F");
            $this->codeError(400);
            return $this->error(__('messages.45'));
        }

        DB::commit();
        logCreate("Cadastrar dados na model " . get_class($this->model));
        return $this->success($result->uuid, __('messages.41'));
    }

    /**
     * Update a newly update resource in storage.
     *
     * @param $find
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function update($find, Request $request)
    {
        // pega somente os dados q foram alterados
        $dados = $this->realUpdate($find, $request);

        // se n tiver nenhuma alteração
        if (empty($dados)) {
            return $this->error(__('messages.60'));
        }

        // validação backend se for necessário
        if (isset($this->model->rules[ "update" ])) {
            $validator = Validator::make($dados, $this->model->rules[ "update" ] ?? []);
            if ($validator->fails()) {
                return $this->validateFails(__('messages.58'), $validator->messages()->toArray());
            }
        }

        //////////////////////////////////////////////////////////////////////////////////
        //        dd( $request->file('foto') );
        if (!is_null($request->file('foto'))) {
            try {
                $dados[ "foto" ] = uploadFoto($request);
            } catch (Exception $e) {
                return $this->error($e->getMessage());
            }
        } else {
            unset($dados[ "foto" ]);
        }
        //////////////////////////////////////////////////////////////////////////////////

        //Inicia o Database Transaction
        DB::beginTransaction();
        $find   = $this->modelFind($find);
        $result = $find->update($dados);
        if (!$result) {
            DB::rollBack();
            logUpdate("Atualizar dados na model " . get_class($this->model), "F");
            $this->codeError(400);
            return $this->error(__('messages.46'));
        }

        DB::commit();
        logUpdate("Atualizar dados na model " . get_class($this->model));
        return $this->success($find, __('messages.42'));
    }

    /**
     * Verifica os dados que vieram do form com os dados do registro
     * retorna os valores que foram alterados
     *
     * @param $find
     * @param $request
     * @return array
     */
    private function realUpdate($find, $request)
    {
        // encapsulamento do request com sanitize
        $dados = $request->all();
        $atual = $this->modelFind($find)->toArray();

        $alterados = [];
        // verifica qis foram os reais campos alterados
        foreach ($dados as $campo => $dado) {
            if (array_key_exists($campo, $atual) && $dado != $atual[ $campo ]) {
                // caso o valor tenha sido apagado coloca null
                $alterados[ $campo ] = (empty($dado) ? null : $dado);
            }
        }

        return sanitize($alterados);
    }

    /**
     * Metodo para usar genericamente com uuid ou primary key da model
     *
     * @param $find
     * @return colection
     */
    private function modelFind($find)
    {
        return (method_exists($this->model, 'getUuidColumnName') != null)
            ? $this->model->findByUuid($find)
            : $this->model->findOrFail($find);
    }

    public function hasBreadcrumb(): bool
    {
        return $this->useBreadcrumb;
    }
}
