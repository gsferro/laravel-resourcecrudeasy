<?php

namespace Gsferro\ResourceCrudEasy\Traits;

use App\Models\ResourceCrud;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use DatatablesEasy\Helpers\DatatablesEasy;
use Illuminate\Support\Facades\Validator;
use Exception;
use Gsferro\ResponseView\Traits\ResponseView;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Throwable;

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
    use ResouceCrudViews;

    public Model $model;

    /*
    |---------------------------------------------------
    | Use breadcrumb
    |---------------------------------------------------
    */
    protected bool $useBreadcrumb = true;

    /*
    |---------------------------------------------------
    | Set type arquiture
    |---------------------------------------------------
    */
    public bool $isSPA = false;

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

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $route      = $this->getRouteRedirectStore();
        $attributes = $request->all();
        try {
            $transaction = DB::transaction(function () use ($request, $attributes) {
                $request->validate($this->rules()[ 'store' ]);

                $model  = $this->model->fill($attributes);
                $update = $this->updateRelations($model, $attributes);
                if (!$update->exists) {
                    $update->save();
                }

                return $model;
            });

            return $this->isSPA
                ? $this->success($transaction)
                : redirect()->route($route); // , $this->redirectStore == 'edit' ? ['resource_crud' => $result->uuid] : []

        } catch (ValidationException $validator) {
            $exception = [
                "error"   => $validator->errors(),
                "code"    => 422,
                "type"    => "ValidationException",
                "message" => $validator->getMessage(),
            ];
        }
        /*catch (ServiceUnavailableHttpException $e) {
            $exception = [
                "code"    => 503,
                "message" => $e->getMessage(),
                "type"    => "ServiceUnavailableHttpException",
            ];
        }*/
        catch (Throwable $throwable) {
            $exception = [
                "error"   => $throwable->errors(),
                "code"    => 500,
                "type"    => "Throwable",
                "message" => config('app.debug', true)
                                ? $throwable->getMessage()
                                : __("Ops... Erro Inesperado!"),
            ];
        }

        /*
        |---------------------------------------------------
        | Look up Tables Principles
        |---------------------------------------------------
        |
        | 1- Caso seja view
        |   1.1 - verificar se tem que retornar para a mesma tela
        |
        */

        return $this->isSPA
            ? $this->error($exception, $attributes, $exception['code'])
            : redirect()->route($this->getRouteName('index'))->withInput()->withErrors($exception['error']);
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

    public function destroy($find)
    {
        // TODO implemented
    }

    /*
    |---------------------------------------------------
    | Metodos Reuso
    |---------------------------------------------------
    */

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

    /**
     * TODO package autovalidate
    */
    public function rules(): array
    {
        return $this->model::$rules;
    }

    private function getRouteName(string $method): string
    {
        return Str::of($this->getEntidade())->snake()->slug(). ".{$method}";
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
    /**
     * @return string
     */
    public function getRouteRedirectStore(): string
    {
        // TODO revisar redirect
        return route($this->getRouteName('index')); //  ?? $this->redirectStore
    }
    /**
     * @return string
     */
    public function getRouteRedirectUpdate(): string
    {
        // TODO revisar redirect
        return route($this->getRouteName('index')); //  ?? $this->redirectUpdate
    }


    /*
    |---------------------------------------------------
    |
    |---------------------------------------------------
    */
    private function updateRelations(Model $model, $attributes): Model
    {
        foreach ($attributes as $key => $val) {
            if (isset($model) &&
                method_exists($model, $key) &&
                is_a($model->$key(), 'Illuminate\Database\Eloquent\Relations\Relation')
            ) {
                $methodClass = get_class($model->$key($key));

                switch ($methodClass) {
                    case 'Illuminate\Database\Eloquent\Relations\BelongsToMany':
                        $new_values = Arr::get($attributes, $key, []);
                        if (array_search('', $new_values) !== false) {
                            unset($new_values[array_search('', $new_values)]);
                        }
                        $model->$key()->sync(array_values($new_values));
                    break;
                    case 'Illuminate\Database\Eloquent\Relations\BelongsTo':
                        $attributesRelation = Arr::get($attributes, $key, null);

                        // TODO Update
                        // create new register
                        $relation = $model->$key()->create($attributesRelation);

                        // necessário setar no relacionamento o campo fk
                        // $foreignKeyName = $model->$key()->getForeignKeyName(); // fk
                        // $ownerKeyName = $model->$key()->getOwnerKeyName(); // pk to relation
                        // $model->$foreignKeyName = $relation->$ownerKeyName;

                        // faz a associate ao registro principal
                        $model = $model->$key()->associate($relation);
                    break;
                    case 'Illuminate\Database\Eloquent\Relations\HasOne':
                        // precisa salvar para pegar o pk
                        $model->save();

                        $attributesRelation = Arr::get($attributes, $key, null);

                        // get name pk model
                        $keyName = $model->getKeyName();

                        // get name fk
                        $foreignKey     = $model->$key()->getOneOfManySubQuerySelectColumns();
                        $foreignKeyName = explode('.', $foreignKey)[ 1 ];

                        // without not set (update)
                        if (empty($attributesRelation[ $foreignKeyName ])) {
                            $attributesRelation[ $foreignKeyName ] = $model->$keyName;
                        }

                        // get name classe to hasOne
                        $related  = get_class($model->$key()->newRelatedInstanceFor($model));

                        // TODO Update
                        // create new register into relation
                        $related::create($attributesRelation);
                    break;
                    case 'Illuminate\Database\Eloquent\Relations\HasOneOrMany':
                    break;
                    case 'Illuminate\Database\Eloquent\Relations\HasMany':
                        $new_values = Arr::get($attributes, $key, []);
                        if (array_search('', $new_values) !== false) {
                            unset($new_values[array_search('', $new_values)]);
                        }

                        $foreignKeyName = $model->$key()->getForeignKeyName();
                        foreach ($model->$key as $rel) {
                            if (!in_array($rel->id, $new_values)) {
                                $rel->$foreignKeyName = null;
                                $rel->save();
                            }
                            unset($new_values[array_search($rel->id, $new_values)]);
                        }

                        if (count($new_values) > 0) {
                            // pega a class da model
                            $related = get_class($model->$key()->getRelated());
                            foreach ($new_values as $val) {
                                $rel = $related::find($val);
                                $rel->$foreignKeyName = $model->id;
                                $rel->save();
                            }
                        }
                    break;
                }
            }
        }

        return $model;
    }
}
