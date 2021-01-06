<?php

namespace OZiTAG\Tager\Backend\Crud\Features;

use Illuminate\Support\Facades\App;
use OZiTAG\Tager\Backend\Core\Features\ModelFeature;
use OZiTAG\Tager\Backend\Core\Repositories\EloquentRepository;
use OZiTAG\Tager\Backend\Crud\Jobs\GetModelResourceFieldsJob;
use OZiTAG\Tager\Backend\Crud\Jobs\ProcessFilesJob;
use OZiTAG\Tager\Backend\Crud\Resources\ModelResource;
use OZiTAG\Tager\Backend\Files\Enums\TagerFileThumbnail;
use OZiTAG\Tager\Backend\HttpCache\HttpCache;

class UpdateFeature extends ModelFeature
{
    private $requestClass;

    private $jobClass;

    private $resourceClass;

    private $resourceFields;

    private $cacheNamespace;

    private $isAdmin;

    public function __construct($id, $getByidJobClass, EloquentRepository $repository, $requestClass, $jobClass, $resourceClass, $resourceFields, $cacheNamespace, $isAdmin)
    {
        parent::__construct($id, $getByidJobClass, $repository);

        $this->requestClass = $requestClass;
        $this->jobClass = $jobClass;
        $this->resourceClass = $resourceClass;
        $this->resourceFields = $resourceFields;
        $this->cacheNamespace = $cacheNamespace;
        $this->isAdmin = $isAdmin;
    }

    public function handle(HttpCache $httpCache)
    {
        $request = App::make($this->requestClass);

        if (!empty($request->fileScenarios())) {
            $this->run(ProcessFilesJob::class, ['request' => $request]);
        }

        $model = $this->run($this->jobClass, [
            'model' => $this->model(),
            'request' => $request,
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
            return new ModelResource($this->model());
        }
    }
}
