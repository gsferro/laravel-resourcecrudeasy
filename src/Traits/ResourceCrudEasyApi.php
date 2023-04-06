<?php

namespace Gsferro\ResourceCrudEasy\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

trait ResourceCrudEasyApi
{
    use ResponseJSON;

    public Model $model;
    public bool  $isSPA = false;
    public bool  $isAPI = false;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return $this->model->paginate();
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
            $request->validate($this->rules()[ 'store' ]);
            $transaction = DB::transaction(function () use ($attributes) {
                $model  = $this->model->fill($attributes);
                $update = $this->updateRelations($model, $attributes);
                if (!$update->exists) {
                    $update->save();
                }
                $model->save();

                return $model;
            });

            return $this->isSPA
                ? $this->success($transaction, null, 201)
                : redirect()->to($route, 201); // , $this->redirectStore == 'edit' ? ['resource_crud' => $result->uuid] : []

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
     * Update a newly update resource in storage.
     *
     * @param $find
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $find)
    {
        $route      = $this->getRouteRedirectUpdate();
        $attributes = $request->all();
        try {
            $request->validate($this->rules()[ 'update' ]);
            $transaction = DB::transaction(function () use ($find, $attributes) {
                $modelFind = $this->modelFind($find);
                $model     = $modelFind->fill($attributes);
                $update = $this->updateRelations($model, $attributes);
                if (!$update->exists) {
                    $update->save();
                }
                $model->save();

                return $model;
            });

            return $this->isSPA
                ? $this->success($transaction)
                : redirect()->to($route);

        } catch (ValidationException $validator) {
            $exception = [
                "error"   => $validator->errors(),
                "code"    => 422,
                "type"    => "ValidationException",
                "message" => $validator->getMessage(),
            ];
        }
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

    public function destroy($find)
    {
        // TODO implemented
    }

    /*
    |---------------------------------------------------
    | Utils
    |---------------------------------------------------
    */
    /**
     * TODO package autovalidate
     */
    public function rules(): array
    {
        return $this->model::$rules;
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

    private function getRouteName(string $method): string
    {
        return Str::of($this->getEntidade())->snake()->slug(). ".{$method}";
    }

    public function getRouteRedirectStore(): string
    {
        // TODO revisar redirect
        return route($this->getRouteName('index')); //  ?? $this->redirectStore
    }

    public function getRouteRedirectUpdate(): string
    {
        // TODO revisar redirect
        return route($this->getRouteName('index')); //  ?? $this->redirectUpdate
    }

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
                        $ids = array_map(function ($item) {
                            return current(array_values($item));
                        }, $new_values);

                        $model->$key()->sync($ids);
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
