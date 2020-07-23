<?php

namespace OZiTAG\Tager\Backend\Crud\Features;

use OZiTAG\Tager\Backend\Core\Features\ModelFeature;
use OZiTAG\Tager\Backend\Core\Repositories\EloquentRepository;
use OZiTAG\Tager\Backend\Core\Resources\SuccessResource;

class MoveFeature extends ModelFeature
{
    private $direction;

    private $repository;

    public function __construct($id, $direction, $jobGetByIdClass, EloquentRepository $repository)
    {
        parent::__construct($id, $jobGetByIdClass);

        $this->direction = $direction;
        $this->repository = $repository;
    }

    public function handle()
    {
        $model = $this->model();

        if ($this->direction == 'up') {
            $other = $this->repository->findFirstWithLowerPriorityThan($model->priority);
        } else {
            $other = $this->repository->findFirstWithHigherPriorityThan($model->priority);
        }

        if ($other) {
            $a = $other->priority;
            $other->priority = $model->priority;
            $model->priority = $a;

            $model->save();
            $other->save();
        }

        return new SuccessResource();
    }
}
