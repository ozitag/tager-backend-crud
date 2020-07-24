<?php

namespace OZiTAG\Tager\Backend\Crud\Traits;

trait RepositoryMoveMethodsTrait
{
    public function findItemWithMaxPriority()
    {
        return $this->model->orderBy('priority', 'desc')->first();
    }

    public function findFirstWithLowerPriorityThan($priority)
    {
        return $this->model->where('priority', '<', $priority)->orderBy('priority', 'desc')->first();
    }

    public function findFirstWithHigherPriorityThan($priority)
    {
        return $this->model->where('priority', '>', $priority)->orderBy('priority', 'asc')->first();
    }
}
