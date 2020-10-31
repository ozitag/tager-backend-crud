<?php

namespace OZiTAG\Tager\Backend\Crud\Features;

use Illuminate\Http\Request;
use OZiTAG\Tager\Backend\Core\Features\Feature;
use OZiTAG\Tager\Backend\Core\Http\SomeRequest;
use OZiTAG\Tager\Backend\Core\Pagination\PaginationRequest;
use OZiTAG\Tager\Backend\Core\Repositories\EloquentRepository;
use OZiTAG\Tager\Backend\Core\Resources\ResourceCollection;
use OZiTAG\Tager\Backend\Crud\Actions\IndexAction;
use OZiTAG\Tager\Backend\Crud\Contracts\IRepositoryCrudTree;
use OZiTAG\Tager\Backend\Crud\Resources\ModelResource;

class ListFeature extends Feature
{
    private $repository;

    private $resourceClassName;

    private $resourceFields;

    private IndexAction $action;

    protected $hasPagination = false;

    protected $hasQuery = false;

    public function __construct(
        EloquentRepository $repository, $resourceClassName, $resourceFields, IndexAction $action
    ) {
        $this->repository = $repository;

        $this->resourceClassName = $resourceClassName;

        $this->resourceFields = $resourceFields;

        $this->action = $action;

        $this->hasPagination = $this->action->get('hasPagination');
        $this->hasQuery = $this->action->get('hasSearchByQuery');
    }

    public function handle(Request $request)
    {
        if ($this->hasPagination) {
            $this->registerPaginationRequest();
        }

        if ($this->hasQuery) {
            $this->registerQueryRequest();
        }

        $items = $this->action->get('isTree')
            ? $this->repository->toFlatTree()
            : $this->repository->get(
                $this->hasPagination, $this->hasQuery ? $request->get('query') : null
            );

        if (!$this->resourceClassName) {
            ModelResource::setFields($this->resourceFields);
        }

        $items->transform(function ($item) {
            $class = $this->resourceClassName ?? ModelResource::class;
            return (new $class($item));
        });

        return new ResourceCollection($items);
    }
}
