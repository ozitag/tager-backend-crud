<?php

namespace OZiTAG\Tager\Backend\Crud\Features;

use OZiTAG\Tager\Backend\Core\Features\Feature;
use OZiTAG\Tager\Backend\Core\Repositories\EloquentRepository;
use OZiTAG\Tager\Backend\Crud\Contracts\IRepositoryCrudTree;
use OZiTAG\Tager\Backend\Crud\Resources\ModelResource;

class ListFeature extends Feature
{
    private $repository;

    private $resourceClassName;

    private $resourceFields;

    private $isTree;

    public function __construct(EloquentRepository $repository, $resourceClassName, $resourceFields, $isTree)
    {
        $this->repository = $repository;

        $this->resourceClassName = $resourceClassName;

        $this->resourceFields = $resourceFields;

        $this->isTree = $isTree;
    }

    public function handle()
    {
        $items = $this->isTree ? $this->repository->toFlatTree() : $this->repository->all();

        if (!empty($this->resourceClassName)) {
            return call_user_func($this->resourceClassName . '::collection', $items);
        }

        ModelResource::setFields($this->resourceFields);
        return ModelResource::collection($items);
    }
}
