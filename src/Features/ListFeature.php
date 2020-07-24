<?php

namespace OZiTAG\Tager\Backend\Crud\Features;

use OZiTAG\Tager\Backend\Core\Features\Feature;
use OZiTAG\Tager\Backend\Core\Repositories\EloquentRepository;
use OZiTAG\Tager\Backend\Crud\Resources\ModelResource;

class ListFeature extends Feature
{
    private $repository;

    private $resourceClassName;

    private $resourceFields;

    public function __construct(EloquentRepository $repository, $resourceClassName, $resourceFields)
    {
        $this->repository = $repository;

        $this->resourceClassName = $resourceClassName;

        $this->resourceFields = $resourceFields;
    }

    public function handle()
    {
        if (!empty($this->resourceClassName)) {
            return call_user_func($this->resourceClassName . '::collection', $this->repository->all());
        }

        ModelResource::setFields($this->resourceFields);
        return ModelResource::collection($this->repository->all());
    }
}
