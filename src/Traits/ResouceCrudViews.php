<?php

namespace Gsferro\ResourceCrudEasy\Traits;

use Illuminate\Support\Str;
use \OwenIt\Auditing\Auditable;

trait ResouceCrudViews
{
    // front
    protected ?string $viewIndex              = null;
    protected ?string $viewCreate             = null;
    protected ?string $viewEdit               = null;
    protected ?string $viewForm               = null;

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
    protected function getPathView($view)
    {
        return Str::of($this->getEntidade())->snake() . ".{$view}";
    }
}