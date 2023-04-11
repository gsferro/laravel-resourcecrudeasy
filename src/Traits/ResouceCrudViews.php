<?php

namespace Gsferro\ResourceCrudEasy\Traits;

use Illuminate\Support\Str;

trait ResouceCrudViews
{
    protected string $sessionName;
    // front
    protected ?string $viewIndex  = null;
    protected ?string $viewCreate = null;
    protected ?string $viewEdit   = null;
    protected ?string $viewForm   = null;

    /**
     * Verifica se foi setado um caminho para a view index ou retorna via conveção
     *
     * @return string
     */
    public function getViewIndex(): string
    {
        if (!empty($this->viewIndex)) {
            return $this->viewIndex;
        }

        return $this->getPathView('index');
    }

    public function getViewCreate(): string
    {
        if (!empty($this->viewCreate)) {
            return $this->viewCreate;
        }

        return $this->getPathView('create');
    }

    public function getViewEdit(): string
    {
        if (!empty($this->viewEdit)) {
            return $this->viewEdit;
        }

        return $this->getPathView('edit');
    }

    /**
     * Verifica se foi setado um caminho para a view form ou retorna via convenção
     *
     * @return string
     */
    public function getViewForm(): string
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
    protected function getPathView($view): string
    {
        return Str::of($this->getEntidade())->snake() . ".{$view}";
    }
}