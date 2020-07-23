<?php

namespace OZiTAG\Tager\Backend\Crud\Features;

use OZiTAG\Tager\Backend\Core\Features\Feature;
use OZiTAG\Tager\Backend\Core\Repositories\EloquentRepository;

class ListFeature extends Feature
{
    private $repository;

    private $resourceClassName;

    public function __construct(EloquentRepository $repository, $resourceClassName)
    {
        $this->repository = $repository;

        $this->resourceClassName = $resourceClassName;
    }

    public function handle()
    {
        return call_user_func($this->resourceClassName . '::collection', $this->repository->all());
    }
}
