<?php

namespace OZiTAG\Tager\Backend\Crud\Features;

use Illuminate\Support\Facades\App;
use OZiTAG\Tager\Backend\Core\Features\ModelFeature;
use OZiTAG\Tager\Backend\Core\Repositories\EloquentRepository;

class UpdateFeature extends ModelFeature
{
    private $requestClass;

    private $jobClass;

    private $resourceClass;

    public function __construct($id, $getByidJobClass, EloquentRepository $repository, $requestClass, $jobClass, $resourceClass)
    {
        parent::__construct($id, $getByidJobClass, $repository);

        $this->requestClass = $requestClass;
        $this->jobClass = $jobClass;
        $this->resourceClass = $resourceClass;
    }

    public function handle()
    {
        $request = App::make($this->requestClass);

        $model = $this->run($this->jobClass, [
            'model' => $this->model(),
            'request' => $request,
        ]);

        $resourceClass = $this->resourceClass;
        return new $resourceClass($model);
    }
}
