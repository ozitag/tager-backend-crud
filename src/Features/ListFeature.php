<?php

namespace OZiTAG\Tager\Backend\Crud\Features;

use OZiTAG\Tager\Backend\Core\Features\Feature;
use OZiTAG\Tager\Backend\Core\Http\SomeRequest;
use OZiTAG\Tager\Backend\Core\Pagination\PaginationRequest;
use OZiTAG\Tager\Backend\Core\Repositories\EloquentRepository;
use OZiTAG\Tager\Backend\Core\Resources\ResourceCollection;
use OZiTAG\Tager\Backend\Crud\Contracts\IRepositoryCrudTree;
use OZiTAG\Tager\Backend\Crud\Resources\ModelResource;

class ListFeature extends Feature
{
    private $repository;

    private $resourceClassName;

    private $resourceFields;

    private $isTree;

    protected bool $hasPagination;

    public function __construct(
        EloquentRepository $repository, $resourceClassName, $resourceFields, $isTree, bool $hasPagination = false
    ) {
        $this->repository = $repository;

        $this->resourceClassName = $resourceClassName;

        $this->resourceFields = $resourceFields;

        $this->isTree = $isTree;

        $this->hasPagination = $hasPagination;
    }

    public function handle()
    {
        if ($this->hasPagination) {
            $this->registerPaginationRequest();
        }

        $items = $this->isTree
            ? $this->repository->toFlatTree()
            : $this->repository->get($this->hasPagination);

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
