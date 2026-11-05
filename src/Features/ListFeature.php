<?php

namespace OZiTAG\Tager\Backend\Crud\Features;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use OZiTAG\Tager\Backend\Core\Features\Feature;
use OZiTAG\Tager\Backend\Core\Repositories\EloquentRepository;
use OZiTAG\Tager\Backend\Core\Repositories\IFilterable;
use OZiTAG\Tager\Backend\Core\Repositories\ISearchable;
use OZiTAG\Tager\Backend\Core\Repositories\ISortable;
use OZiTAG\Tager\Backend\Core\Resources\ResourceCollection;
use OZiTAG\Tager\Backend\Core\Structures\SortAttributeCollection;
use OZiTAG\Tager\Backend\Crud\Actions\IndexAction;
use OZiTAG\Tager\Backend\Crud\Jobs\GetModelResourceFieldsJob;
use OZiTAG\Tager\Backend\Crud\Resources\ModelResource;

class ListFeature extends Feature
{
    private $repository;
    private $resourceClassName;
    private $resourceFields;
    private IndexAction $action;

    protected bool $hasPagination = false;
    protected bool $hasQuery = false;
    protected bool $hasSort = false;

    protected $isAdmin = false;

    public function __construct(
        EloquentRepository $repository,
                           $resourceClassName,
                           $resourceFields,
        IndexAction        $action,
        bool               $isAdmin,
        protected ?array   $resourceFieldsByView = []
    )
    {
        $this->repository = $repository;
        $this->resourceClassName = $resourceClassName;
        $this->resourceFields = $resourceFields;
        $this->action = $action;
        $this->isAdmin = $isAdmin;
        $this->hasPagination = $this->action->get('hasPagination');
        $this->hasQuery = $this->action->get('hasSearchByQuery');
        $this->hasSort = $this->action->get('hasSort');
    }

    /**
     * Выбрать колонки собственной таблицы — но только если вызывающий код
     * не выбрал их сам.
     *
     * Безусловный select('<таблица>.*') затирал любой select, выставленный
     * на билдере до прихода сюда: по приджойненной колонке можно было
     * фильтровать и сортировать, но нельзя было её показать. Join при этом
     * оставался, так что поломка выглядела как «колонка молча пропала».
     *
     * Умолчание прежнее: когда колонки не заданы, выбирается '<таблица>.*',
     * иначе join'ы из searchByQuery()/filter() растащили бы в выборку чужие
     * одноимённые колонки.
     */
    private function selectOwnColumns(Builder|EloquentBuilder $builder): void
    {
        $query = $builder instanceof EloquentBuilder ? $builder->getQuery() : $builder;

        if (empty($query->columns)) {
            $builder->select($this->repository->getTableName() . '.*');
        }
    }

    public function handle(Request $request)
    {
        $view = $request->get('view');

        if ($this->hasPagination) {
            $this->registerPaginationRequest();
        }

        if ($this->hasQuery) {
            $this->registerQueryRequest();
        }

        if ($this->hasSort) {
            $this->registerSortRequest();
        }

        $this->registerFilterRequest();

        $query = $this->hasQuery ? $request->get('query') : null;

        $filter = $request->get('filter');
        $sort = $request->get('sort');

        $builder = $this->action->getQueryBuilder();

        if ($builder) {
            $this->selectOwnColumns($builder);
            if ($query !== null && $this->hasQuery && $this->repository instanceof ISearchable) {
                $builder = $this->repository->searchByQuery($query, $builder);
            }

            if ($filter && $this->repository instanceof IFilterable) {
                $builder = $this->repository->filter($filter, $builder);
            }

            if ($sort && $this->repository instanceof ISortable) {
                $builder = $this->repository->sort($sort, $builder);
            }

            if (!empty($this->action->getWith())) {
                $builder->with($this->action->getWith());
            }

            if (!$builder) {
                $items = new Collection();
            } else if (!$this->hasPagination) {
                $items = $builder->get();
            } else {
                $items = $this->repository->paginate($builder);
            }
        } else {
            $builder = $this->isAdmin ? $this->repository->adminBuilder() : $this->repository->builder();

            $this->selectOwnColumns($builder);
            if (!empty($this->action->getWith())) {
                $builder->with($this->action->getWith());
            }

            $items = $this->action->get('isTree')
                ? $this->repository->toFlatTree($builder, $this->hasPagination, $query, $filter, $sort)
                : $this->repository->get($builder, $this->hasPagination, $query, $filter, $sort);
        }


        if($view && array_key_exists($view, $this->resourceFieldsByView)){
            $resourceFields = $this->run(GetModelResourceFieldsJob::class, [
                'resourceFields' => $this->resourceFieldsByView[$view],
                'isAdmin' => $this->isAdmin,
            ]);

            ModelResource::setFields($resourceFields);
        } else if (!$this->resourceClassName) {
            $resourceFields = $this->run(GetModelResourceFieldsJob::class, [
                'resourceFields' => $this->resourceFields,
                'isAdmin' => $this->isAdmin,
            ]);

            ModelResource::setFields($resourceFields);
        }

        $items->transform(function ($item) {
            $class = $this->resourceClassName ?? ModelResource::class;
            return (new $class($item));
        });

        $meta = [];
        $metaDataJob = $this->action->getMetaDataJob();
        if($metaDataJob){
            $meta = $this->run($metaDataJob, [
                'builder' => $builder
            ]);
        }

        return new ResourceCollection($items, $meta);
    }
}
