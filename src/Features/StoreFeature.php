<?php

namespace OZiTAG\Tager\Backend\Crud\Features;

use Illuminate\Support\Facades\App;
use OZiTAG\Tager\Backend\Core\Features\Feature;

class StoreFeature extends Feature
{
    private $requestClass;

    private $jobClass;

    private $resourceClass;

    public function __construct($requestClass, $jobClass, $resourceClass)
    {
        $this->requestClass = $requestClass;
        $this->jobClass = $jobClass;
        $this->resourceClass = $resourceClass;
    }

    public function handle()
    {
        $request = App::make($this->requestClass);

        $model = $this->run($this->jobClass, ['request' => $request]);

        $resourceClass = $this->resourceClass;
        return new $resourceClass($model);
    }
}
