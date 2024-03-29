<?php

namespace OZiTAG\Tager\Backend\Crud\Features;

use Illuminate\Contracts\Database\Eloquent\Builder as BuilderContract;
use OZiTAG\Tager\Backend\Core\Features\ModelFeature;
use OZiTAG\Tager\Backend\Crud\Jobs\GetModelResourceFieldsJob;
use OZiTAG\Tager\Backend\Crud\Resources\ModelResource;

class ViewFeature extends ModelFeature
{
    private $resourceClass;

    private $resourceFields;

    private $isAdmin;

    public function __construct($id, $jobGetByIdClass, $repository, $resourceClass, $resourceFields, $isAdmin, ?BuilderContract $builder = null)
    {
        parent::__construct($id, $jobGetByIdClass, $repository, $builder);
        $this->resourceClass = $resourceClass;
        $this->resourceFields = $resourceFields;
        $this->isAdmin = $isAdmin;
    }

    public function handle()
    {
        if (!empty($this->resourceClass)) {
            $resourceClass = $this->resourceClass;
            return new $resourceClass($this->model());
        }

        $resourceFields = $this->run(GetModelResourceFieldsJob::class, [
            'resourceFields' => $this->resourceFields,
            'isAdmin' => $this->isAdmin
        ]);

        ModelResource::setFields($resourceFields);
        return new ModelResource($this->model());
    }
}
