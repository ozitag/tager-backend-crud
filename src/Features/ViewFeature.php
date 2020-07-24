<?php

namespace OZiTAG\Tager\Backend\Crud\Features;

use OZiTAG\Tager\Backend\Core\Features\ModelFeature;
use OZiTAG\Tager\Backend\Crud\Resources\ModelResource;

class ViewFeature extends ModelFeature
{
    private $resourceClass;

    private $resourceFields;

    public function __construct($id, $jobGetByIdClass, $repository, $resourceClass, $resourceFields)
    {
        parent::__construct($id, $jobGetByIdClass, $repository);
        $this->resourceClass = $resourceClass;
        $this->resourceFields = $resourceFields;
    }

    public function handle()
    {
        if (!empty($this->resourceClass)) {
            $resourceClass = $this->resourceClass;
            return new $resourceClass($this->model());
        }

        ModelResource::setFields($this->resourceFields);
        return new ModelResource($this->model());
    }
}
