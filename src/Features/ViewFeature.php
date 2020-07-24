<?php

namespace OZiTAG\Tager\Backend\Crud\Features;

use OZiTAG\Tager\Backend\Core\Features\ModelFeature;

class ViewFeature extends ModelFeature
{
    private $resourceClass;

    public function __construct($id, $jobGetByIdClass, $repository, $resourceClass)
    {
        parent::__construct($id, $jobGetByIdClass, $repository);
        $this->resourceClass = $resourceClass;
    }

    public function handle()
    {
        $resourceClass = $this->resourceClass;
        return new $resourceClass($this->model());
    }
}
