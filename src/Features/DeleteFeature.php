<?php

namespace OZiTAG\Tager\Backend\Crud\Features;

use OZiTAG\Tager\Backend\Core\Features\ModelFeature;
use OZiTAG\Tager\Backend\Core\Resources\SuccessResource;

class DeleteFeature extends ModelFeature
{
    private $jobDeleteClass;

    private $repository;

    public function __construct($id, $jobGetByIdClass, $repository, $jobDeleteClass)
    {
        parent::__construct($id, $jobGetByIdClass, $repository);
        $this->jobDeleteClass = $jobDeleteClass;
        $this->repository = $repository;
    }

    public function handle()
    {
        if ($this->jobDeleteClass) {
            $this->run($this->jobDeleteClass, ['model' => $this->model()]);
        } else if ($this->repository) {
            $this->repository->find($this->id)->delete();
        } else {
            throw new \Exception('JobDeleteClass or Repository must be set');
        }

        return new SuccessResource();
    }
}
