<?php

namespace OZiTAG\Tager\Backend\Crud\Tests\Fixtures;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use OZiTAG\Tager\Backend\Core\Repositories\EloquentRepository;
use OZiTAG\Tager\Backend\Core\Repositories\IFilterable;
use OZiTAG\Tager\Backend\Core\Repositories\ISearchable;
use OZiTAG\Tager\Backend\Core\Repositories\ISortable;
use OZiTAG\Tager\Backend\Crud\Contracts\IRepositoryWithPriorityMethods;
use OZiTAG\Tager\Backend\Crud\Traits\RepositoryPriorityMethodsTrait;

class ArticleRepository extends EloquentRepository implements
    ISearchable,
    IFilterable,
    ISortable,
    IRepositoryWithPriorityMethods
{
    use RepositoryPriorityMethodsTrait;

    public function __construct(?Article $model = null)
    {
        parent::__construct($model ?? new Article());
    }

    public function searchByQuery(?string $query, ?Builder $builder = null): ?Builder
    {
        return ($builder ?? $this->builder())->where('title', 'like', '%' . $query . '%');
    }

    public function filter(?array $filter = [], ?Builder $builder = null): ?Builder
    {
        $builder = $builder ?? $this->builder();

        if (isset($filter['published'])) {
            $builder->where('is_published', (bool) $filter['published']);
        }

        return $builder;
    }

    public function sort(?string $sort = null, ?Builder $builder = null): ?Builder
    {
        $builder = $builder ?? $this->builder();

        $direction = str_starts_with((string) $sort, '-') ? 'desc' : 'asc';

        return $builder->orderBy(ltrim((string) $sort, '-'), $direction);
    }
}
