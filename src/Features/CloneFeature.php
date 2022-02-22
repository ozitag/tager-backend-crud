<?php

namespace OZiTAG\Tager\Backend\Crud\Features;

use OZiTAG\Tager\Backend\Core\Features\ModelFeature;
use OZiTAG\Tager\Backend\Core\Repositories\EloquentRepository;
use OZiTAG\Tager\Backend\Crud\Jobs\GetModelResourceFieldsJob;
use OZiTAG\Tager\Backend\Crud\Resources\ModelResource;
use OZiTAG\Tager\Backend\HttpCache\HttpCache;

class CloneFeature extends ModelFeature
{
    protected ?EloquentRepository $repository;
    protected ?string $cloneModelJobClass;
    protected ?string $resourceClass;
    protected ?array $resourceFields;
    protected ?string $cacheNamespace;
    protected bool $isAdmin;

    public function __construct(int $id, ?string $jobGetByIdClass, EloquentRepository $repository, $cloneModelJobClass, $resourceClass, $resourceFields, $cacheNamespace, $isAdmin)
    {
        parent::__construct($id, $jobGetByIdClass, $repository);

        $this->repository = $repository;

        $this->cloneModelJobClass = $cloneModelJobClass;

        $this->resourceClass = $resourceClass;

        $this->resourceFields = $resourceFields;

        $this->cacheNamespace = $cacheNamespace;

        $this->isAdmin = $isAdmin;
    }

    public function handle(HttpCache $httpCache)
    {
        $model = $this->model();

        $model = $this->run($this->cloneModelJobClass, [
            'model' => $model
        ]);

        if ($this->cacheNamespace) {
            $httpCache->clear($this->cacheNamespace);
        }

        if (!empty($this->resourceClass)) {
            $resourceClass = $this->resourceClass;
            return new $resourceClass($model);
        } else {
            $resourceFields = $this->run(GetModelResourceFieldsJob::class, [
                'resourceFields' => $this->resourceFields,
                'isAdmin' => $this->isAdmin,
            ]);

            ModelResource::setFields($resourceFields);
            return new ModelResource($model);
        }
    }
}
