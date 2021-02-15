<?php

namespace OZiTAG\Tager\Backend\Crud\Features;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use OZiTAG\Tager\Backend\Core\Features\Feature;
use OZiTAG\Tager\Backend\Core\Repositories\EloquentRepository;
use OZiTAG\Tager\Backend\Core\Resources\ResourceCollection;
use OZiTAG\Tager\Backend\Crud\Actions\IndexAction;
use OZiTAG\Tager\Backend\Crud\Jobs\GetModelResourceFieldsJob;
use OZiTAG\Tager\Backend\Crud\Resources\ModelResource;

class ListFeature extends Feature
{
    private $repository;
    private $resourceClassName;
    private $resourceFields;
    private IndexAction $action;
    protected $hasPagination = false;
    protected $hasQuery = false;
    protected $hasFilter = false;
    protected $isAdmin = false;

    public function __construct(
        EloquentRepository $repository,
        $resourceClassName,
        $resourceFields,
        IndexAction $action,
        bool $isAdmin
    ) {
        $this->repository = $repository;
        $this->resourceClassName = $resourceClassName;
        $this->resourceFields = $resourceFields;
        $this->action = $action;
        $this->isAdmin = $isAdmin;
        $this->hasPagination = $this->action->get('hasPagination');
        $this->hasQuery = $this->action->get('hasSearchByQuery');
        $this->hasFilter = $this->action->get('hasFilter');
    }

    public function handle(Request $request)
    {
        if ($this->hasPagination) {
            $this->registerPaginationRequest();
        }

        if ($this->hasQuery) {
            $this->registerQueryRequest();
        }

        if ($this->hasQuery) {
            $this->registerFilterRequest();
        }

        $query = $this->hasQuery ? $request->get('query') : null;

        $filter = $this->hasFilter ? $request->get('filter') : [];

        $getIndexActionBuilderJobClass = $this->action->getIndexActionBuilderJobClass();
        if ($getIndexActionBuilderJobClass) {
            $builder = $this->run($getIndexActionBuilderJobClass);

            if ($this->hasQuery) {
                $builder = $this->repository->searchByQuery($query, $builder);
            }

            if ($this->hasFilter) {
                $builder = $this->repository->filter($filter, $builder);
            }

            if (!$builder) {
                $items = new Collection();
            } else if (!$this->hasPagination) {
                $items = $builder->get();
            } else {
                $items = $this->repository->paginate($builder);
            }
        } else {
            $items = $this->action->get('isTree')
                ? $this->repository->toFlatTree($this->hasPagination, $query, $filter)
                : $this->repository->get($this->hasPagination, $query, $filter);
        }

        if (!$this->resourceClassName) {
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

        return new ResourceCollection($items);
    }
}
