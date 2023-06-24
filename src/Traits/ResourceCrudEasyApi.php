<?php

namespace Gsferro\ResourceCrudEasy\Traits;

use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

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
     * @param Request $request
     * @return array|JsonResponse|RedirectResponse
     */
    public function store(Request $request): JsonResponse|array|RedirectResponse
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

            // TODO melhorar feedback
            session()->flash('success');

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
                "error"   => $throwable->getMessage(),
                "code"    => 500,
                "type"    => "Throwable",
                "message" => config('app.debug', true)
                    ? $throwable->getMessage()
                    : __('resource-crud.throwable'),
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
            : redirect()->back()->withInput()->withErrors($exception['error']);
    }

    /**
     * Display the specified resource.
     *
     * @param $uuid
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show(string|int $uuid): Factory|View
    {
        if ($this->hasBreadcrumb()) {
            $this->addBreadcrumb(__('resource-crud.see'));
        }

        $this->addData('model', $this->modelFind($uuid));
        $this->addData('form', $this->getViewForm());
        return $this->view($this->viewShow);
    }

    /**
     * Update a newly update resource in storage.
     *
     * @param $find
     * @param Request $request
     * @return array|JsonResponse|RedirectResponse
     */
    public function update(Request $request, $find): JsonResponse|array|RedirectResponse
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

            // TODO melhorar feedback
            session()->flash('success');

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
                "error"   => $throwable->getMessage(),
                "code"    => 500,
                "type"    => "Throwable",
                "message" => config('app.debug', true)
                    ? $throwable->getMessage()
                    : __('resource-crud.throwable'),
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
            : redirect()->back()->withInput()->withErrors($exception['error']);
    }

    public function destroy(string|int $find)
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
        return (property_exists($this->model, 'rules'))
            ? $this->model::$rules
            : [];
    }

    /**
     * Metodo para usar genericamente com uuid ou primary key da model
     *
     * @param $find
     * @return Model
     */
    private function modelFind(string|int $find): Model
    {
        return (method_exists($this->model, 'getUuidColumnName'))
            ? $this->model->findByUuid($find)
            : $this->model->findOrFail($find);
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
                        // para qdo for create, precisa do id para fazer o sync
                        $model->save();

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
