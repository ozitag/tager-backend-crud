<?php

namespace OZiTAG\Tager\Backend\Crud\Actions;

use OZiTAG\Tager\Backend\Crud\Contracts\IAction;

class IndexAction extends DefaultAction
{
    protected bool $hasPagination = false;
    protected bool $hasSearchByQuery = false;
    protected bool $isTree = false;

    /**
     * @return IAction
     */
    public function enablePagination(): IAction {
        $this->hasPagination = true;
        return $this;
    }
    /**
     * @return IAction
     */
    public function enableSearchByQuery(): IAction {
        $this->hasSearchByQuery = true;
        return $this;
    }

    /**
     * @return IAction
     */
    public function enableTree(): IAction {
        $this->isTree = true;
        return $this;
    }
}
