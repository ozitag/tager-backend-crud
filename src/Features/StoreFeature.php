<?php

namespace OZiTAG\Tager\Backend\Crud\Features;

use Doctrine\DBAL\Driver\PDO\Exception;
use Illuminate\Support\Facades\App;
use OZiTAG\Tager\Backend\Core\Features\Feature;
use OZiTAG\Tager\Backend\Crud\Events\ModelChanged;
use OZiTAG\Tager\Backend\Crud\Jobs\ProcessFilesJob;
use OZiTAG\Tager\Backend\Crud\Resources\ModelResource;
use OZiTAG\Tager\Backend\HttpCache\HttpCache;

class StoreFeature extends Feature
{
    private $requestClass;

    private $jobClass;

    private $resourceClass;

    private $resourceFields;

    private $cacheNamespace;

    private $eventClass;

    public function __construct($requestClass, $jobClass, $resourceClass, $resourceFields, $cacheNamespace, $eventClass)
    {
        $this->requestClass = $requestClass;
        $this->jobClass = $jobClass;
        $this->resourceClass = $resourceClass;
        $this->resourceFields = $resourceFields;
        $this->cacheNamespace = $cacheNamespace;
        $this->eventClass = $eventClass;
    }

    public function handle(HttpCache $httpCache)
    {
        $request = App::make($this->requestClass);

        if (!empty($request->fileScenarios())) {
            $this->run(ProcessFilesJob::class, ['request' => $request]);
        }

        $model = $this->run($this->jobClass, ['request' => $request]);
        if(!$model){
            throw new \Exception('Failure create model');
        }

        if ($this->cacheNamespace) {
            $httpCache->clear($this->cacheNamespace);
        }

        if ($this->eventClass) {
            $eventClass = $this->eventClass;
            event(new $eventClass($model->id));
        }

        if (!empty($this->resourceClass)) {
            $resourceClass = $this->resourceClass;
            return new $resourceClass($model);
        } else {
            ModelResource::setFields($this->resourceFields);
            return new ModelResource($model);
        }
    }
}
