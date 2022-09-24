<?php

namespace OZiTAG\Tager\Backend\Crud\Actions;

use Doctrine\DBAL\Query\QueryBuilder;
use Illuminate\Database\Eloquent\Builder;

class IndexAction extends DefaultAction
{
    protected bool $hasSearchByQuery = true;

    protected bool $hasPagination = true;

    protected bool $hasSort = true;

    protected mixed $queryBuilder = null;

    protected bool $isTree = false;

    protected array $with = [];

    public ?string $resourceClass = null;

    public ?array $resourceFields = null;

    public function __construct(?string $getBuilderJobClass = null)
    {
        parent::__construct();

        if ($getBuilderJobClass) {
            $this->setQueryBuilder($getBuilderJobClass);
        }
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

    public function with($relations = []): static
    {
        $this->with = $relations;
        return $this;
    }

    public function getWith(): array
    {
        return $this->with;
    }

    public function setQueryBuilder(mixed $queryBuilder): static
    {
        $this->queryBuilder = $queryBuilder;
        return $this;
    }

    public function getQueryBuilder(): ?Builder
    {
        if (is_string($this->queryBuilder)) {
            return dispatch_sync(new $this->queryBuilder());
        } else if (is_callable($this->queryBuilder)) {
            return call_user_func($this->queryBuilder);
        } else if ($this->queryBuilder instanceof Builder) {
            return $this->queryBuilder;
        } else {
            return null;
        }
    }
}
