<?php

namespace OZiTAG\Tager\Backend\Crud\Features;

use OZiTAG\Tager\Backend\Core\Features\ModelFeature;
use OZiTAG\Tager\Backend\Crud\Jobs\GetModelResourceFieldsJob;
use OZiTAG\Tager\Backend\Crud\Resources\ModelResource;
use OZiTAG\Tager\Backend\Files\Enums\TagerFileThumbnail;

class ViewFeature extends ModelFeature
{
    private $resourceClass;

    private $resourceFields;

    private $isAdmin;

    public function __construct($id, $jobGetByIdClass, $repository, $resourceClass, $resourceFields, $isAdmin)
    {
        parent::__construct($id, $jobGetByIdClass, $repository);
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
