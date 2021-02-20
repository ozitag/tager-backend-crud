<?php

namespace OZiTAG\Tager\Backend\Crud\Features;

use OZiTAG\Tager\Backend\Core\Features\ModelFeature;
use OZiTAG\Tager\Backend\Core\Resources\FailureResource;
use OZiTAG\Tager\Backend\Core\Resources\SuccessResource;
use OZiTAG\Tager\Backend\HttpCache\HttpCache;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DeleteFeature extends ModelFeature
{
    private $jobDeleteClass;

    private $checkIfCanDeleteJobClass;

    private $repository;

    private $cacheNamespace;

    private $eventClass;

    public function __construct($id, $jobGetByIdClass, $repository, $checkIfCanDeleteJobClass, $jobDeleteClass, $cacheNamespace, $eventClass)
    {
        parent::__construct($id, $jobGetByIdClass, $repository);

        $this->jobDeleteClass = $jobDeleteClass;
        $this->checkIfCanDeleteJobClass = $checkIfCanDeleteJobClass;

        $this->repository = $repository;
        $this->cacheNamespace = $cacheNamespace;
        $this->eventClass = $eventClass;
    }

    public function handle(HttpCache $httpCache)
    {
        if ($this->checkIfCanDeleteJobClass) {
            $validate = $this->run($this->checkIfCanDeleteJobClass, ['model' => $this->model()]);

            if ($validate !== true) {
                $error = is_string($validate) ? $validate : 'Error delete model';
                throw new AccessDeniedHttpException($error);
            }
        }

        if ($this->jobDeleteClass) {
            $this->run($this->jobDeleteClass, ['model' => $this->model()]);
        } else if ($this->repository) {
            $model = $this->repository->find($this->id);
            if ($model) {
                $model->delete();
            } else {
                throw new NotFoundHttpException('Model not found');
            }
        } else {
            throw new \Exception('JobDeleteClass or Repository must be set');
        }

        if ($this->cacheNamespace) {
            $httpCache->clear($this->cacheNamespace);
        }

        if ($this->eventClass) {
            $eventClass = $this->eventClass;
            event(new $eventClass($model->getAttributes()));
        }

        return new SuccessResource();
    }
}
