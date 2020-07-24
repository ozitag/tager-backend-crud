<?php

namespace OZiTAG\Tager\Backend\Crud\Features;

use Illuminate\Support\Facades\App;
use OZiTAG\Tager\Backend\Core\Features\ModelFeature;
use OZiTAG\Tager\Backend\Core\Repositories\EloquentRepository;
use OZiTAG\Tager\Backend\Crud\Resources\ModelResource;

class UpdateFeature extends ModelFeature
{
    private $requestClass;

    private $jobClass;

    private $resourceClass;

    private $resourceFields;

    public function __construct($id, $getByidJobClass, EloquentRepository $repository, $requestClass, $jobClass, $resourceClass, $resourceFields)
    {
        parent::__construct($id, $getByidJobClass, $repository);

        $this->requestClass = $requestClass;
        $this->jobClass = $jobClass;
        $this->resourceClass = $resourceClass;
        $this->resourceFields = $resourceFields;
    }

    public function handle()
    {
        $request = App::make($this->requestClass);

        $model = $this->run($this->jobClass, [
            'model' => $this->model(),
            'request' => $request,
        ]);

        if (!empty($this->resourceClass)) {
            $resourceClass = $this->resourceClass;
            return new $resourceClass($model);
        } else {
            ModelResource::setFields($this->resourceFields);
            return new ModelResource($model);
        }
    }
}
