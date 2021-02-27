<?php

namespace OZiTAG\Tager\Backend\Crud\Actions;

class IndexAction extends DefaultAction
{
    protected ?string $getIndexActionBuilderJobClass = null;

    protected bool $hasSearchByQuery = true;
    protected bool $hasPagination = true;
    protected bool $hasFilter = false;
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

    public function enablePagination(): static
    {
        $this->hasPagination = true;
        return $this;
    }

    public function enableSearchByQuery(): static
    {
        $this->hasSearchByQuery = true;
        return $this;
    }

    public function disablePagination(): static
    {
        $this->hasPagination = false;
        return $this;
    }

    public function disableSearchByQuery(): static
    {
        $this->hasSearchByQuery = false;
        return $this;
    }

    public function enableFilter(): static
    {
        $this->hasFilter = true;
        return $this;
    }

    public function enableTree(): static
    {
        $this->isTree = true;
        return $this;
    }

    public function setResource(string $resourceClass): static
    {
        $this->resourceClass = $resourceClass;
        return $this;
    }

    public function setResourceFields(array $resourceFields): static
    {
        $this->resourceFields = $resourceFields;
        return $this;
    }
}
