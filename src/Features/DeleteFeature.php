<?php

namespace OZiTAG\Tager\Backend\Crud\Features;

use OZiTAG\Tager\Backend\Core\Features\ModelFeature;
use OZiTAG\Tager\Backend\Core\Resources\SuccessResource;

class DeleteFeature extends ModelFeature
{
    private $jobDeleteClass;

    public function __construct($id, $jobGetByIdClass, $jobDeleteClass)
    {
        parent::__construct($id, $jobGetByIdClass);
        $this->jobDeleteClass = $jobDeleteClass;
    }

    public function handle()
    {
        $this->run($this->jobDeleteClass, ['model' => $this->model()]);
        return new SuccessResource();
    }
}
