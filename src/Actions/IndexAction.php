<?php

namespace OZiTAG\Tager\Backend\Crud\Actions;

use OZiTAG\Tager\Backend\Crud\Contracts\IAction;

class IndexAction extends DefaultAction
{
    protected ?string $getIndexActionBuilderJobClass = null;

    protected bool $hasPagination = false;
    protected bool $hasFilter = false;
    protected bool $hasSearchByQuery = false;
    protected bool $isTree = false;

    public ?string $resourceClass = null;
    public ?array $resourceFields = null;

    public function __construct(?string $getIndexBuilderJobClass = null)
    {
        parent::__construct();

        $this->getIndexActionBuilderJobClass = $getIndexBuilderJobClass;
    }

    public function getIndexActionBuilderJobClass(): ?string
    {
        return $this->getIndexActionBuilderJobClass;
    }

    public function enablePagination(): IAction
    {
        $this->hasPagination = true;
        return $this;
    }

    public function enableSearchByQuery(): IAction
    {
        $this->hasSearchByQuery = true;
        return $this;
    }

    public function enableFilter(): IAction
    {
        $this->hasFilter = true;
        return $this;
    }

    public function enableTree(): IAction
    {
        $this->isTree = true;
        return $this;
    }

    public function setResource(string $resourceClass): IAction
    {
        $this->resourceClass = $resourceClass;
        return $this;
    }

    public function setResourceFields(array $resourceFields): IAction
    {
        $this->resourceFields = $resourceFields;
        return $this;
    }
}
