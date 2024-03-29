<?php

namespace OZiTAG\Tager\Backend\Crud\Features;

use Illuminate\Contracts\Database\Eloquent\Builder as BuilderContract;
use Illuminate\Support\Facades\App;
use OZiTAG\Tager\Backend\Core\Features\ModelFeature;
use OZiTAG\Tager\Backend\Core\Repositories\EloquentRepository;
use OZiTAG\Tager\Backend\Crud\Jobs\GetModelResourceFieldsJob;
use OZiTAG\Tager\Backend\Crud\Jobs\ProcessFilesJob;
use OZiTAG\Tager\Backend\Crud\Resources\ModelResource;
use OZiTAG\Tager\Backend\HttpCache\HttpCache;

class UpdateFeature extends ModelFeature
{
    private ?string $requestClass;

    private ?string $jobClass;

    private ?string $resourceClass;

    private ?array $resourceFields;

    private string|array|null $cacheNamespace;

    private ?string $eventClass;

    private bool $isAdmin;

    public function __construct($id, $getByidJobClass, EloquentRepository $repository,
        $requestClass, $jobClass, $resourceClass, $resourceFields, $cacheNamespace, $eventClass, $isAdmin,
                                ?BuilderContract $builder = null
    )
    {
        parent::__construct($id, $getByidJobClass, $repository, $builder);

        $this->requestClass = $requestClass;
        $this->jobClass = $jobClass;
        $this->resourceClass = $resourceClass;
        $this->resourceFields = $resourceFields;
        $this->cacheNamespace = $cacheNamespace;
        $this->eventClass = $eventClass;
        $this->isAdmin = $isAdmin;
    }

    public function handle(HttpCache $httpCache)
    {
        $request = App::make($this->requestClass);

        $oldAttributes = $this->model()->attributesToArray();

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

        if ($this->eventClass) {
            $eventClass = $this->eventClass;
            event(new $eventClass($model->id, $oldAttributes, $model->getAttributes()));
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
