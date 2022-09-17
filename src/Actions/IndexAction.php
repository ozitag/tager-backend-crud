<?php

namespace OZiTAG\Tager\Backend\Crud\Actions;

class IndexAction extends DefaultAction
{
    protected ?string $getIndexActionBuilderJobClass = null;

    protected bool $hasSearchByQuery = true;

    protected bool $hasPagination = true;

    protected bool $hasSort = true;

    protected bool $isTree = false;

    protected array $with = [];

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

    public function disableSort(): static
    {
        $this->hasSort = false;
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

    public function getWith(): array
    {
        return $this->with;
    }

    public function with($relations = []): static
    {
        $this->with = $relations;
        return $this;
    }
}
