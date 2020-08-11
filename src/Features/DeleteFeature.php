<?php

namespace OZiTAG\Tager\Backend\Crud\Features;

use OZiTAG\Tager\Backend\Core\Features\ModelFeature;
use OZiTAG\Tager\Backend\Core\Resources\FailureResource;
use OZiTAG\Tager\Backend\Core\Resources\SuccessResource;
use OZiTAG\Tager\Backend\HttpCache\HttpCache;

class DeleteFeature extends ModelFeature
{
    private $jobDeleteClass;

    private $checkIfCanDeleteJobClass;

    private $repository;

    private $cacheNamespace;

    public function __construct($id, $jobGetByIdClass, $repository, $checkIfCanDeleteJobClass, $jobDeleteClass, $cacheNamespace)
    {
        parent::__construct($id, $jobGetByIdClass, $repository);

        $this->jobDeleteClass = $jobDeleteClass;
        $this->checkIfCanDeleteJobClass = $checkIfCanDeleteJobClass;

        $this->repository = $repository;
        $this->cacheNamespace = $cacheNamespace;
    }

    public function handle(HttpCache $httpCache)
    {
        if ($this->checkIfCanDeleteJobClass) {
            $validate = $this->run($this->checkIfCanDeleteJobClass, ['model' => $this->model()]);
            if ($validate !== true) {
                return new FailureResource(is_string($validate) ? $validate : 'Error delete model');
            }
        }

        if ($this->jobDeleteClass) {
            $this->run($this->jobDeleteClass, ['model' => $this->model()]);
        } else if ($this->repository) {
            $this->repository->find($this->id)->delete();
        } else {
            throw new \Exception('JobDeleteClass or Repository must be set');
        }

        if ($this->cacheNamespace) {
            $httpCache->clear($this->cacheNamespace);
        }

        return new SuccessResource();
    }
}
