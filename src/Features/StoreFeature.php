<?php

namespace OZiTAG\Tager\Backend\Crud\Features;

use Illuminate\Support\Facades\App;
use OZiTAG\Tager\Backend\Core\Features\Feature;
use OZiTAG\Tager\Backend\Crud\Resources\ModelResource;

class StoreFeature extends Feature
{
    private $requestClass;

    private $jobClass;

    private $resourceClass;

    private $resourceFields;

    public function __construct($requestClass, $jobClass, $resourceClass, $resourceFields)
    {
        $this->requestClass = $requestClass;
        $this->jobClass = $jobClass;
        $this->resourceClass = $resourceClass;
        $this->resourceFields = $resourceFields;
    }

    public function handle()
    {
        $request = App::make($this->requestClass);

        $model = $this->run($this->jobClass, ['request' => $request]);

        if (!empty($this->resourceClass)) {
            $resourceClass = $this->resourceClass;
            return new $resourceClass($model);
        } else {
            ModelResource::setFields($this->resourceFields);
            return new ModelResource($model);
        }
    }
}
