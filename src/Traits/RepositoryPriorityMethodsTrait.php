<?php

namespace OZiTAG\Tager\Backend\Crud\Traits;

trait RepositoryPriorityMethodsTrait
{
    public function findItemWithMaxPriority()
    {
        return $this->model->orderBy('priority', 'desc')->first();
    }

    public function findFirstWithLowerPriorityThan($priority, $conditionalAttributes = [])
    {
        $query = $this->model::query()->where('priority', '<', $priority)->orderBy('priority', 'desc');

        if ($conditionalAttributes) {
            foreach ($conditionalAttributes as $conditionalAttribute) {
                $query->where($conditionalAttribute['field'], $conditionalAttribute['operator'], $conditionalAttribute['operand']);
            }
        }

        return $query->first();
    }

    public function findFirstWithHigherPriorityThan($priority, $conditionalAttributes = [])
    {
        $query = $this->model::query()->where('priority', '>', $priority)->orderBy('priority', 'asc');

        if ($conditionalAttributes) {
            foreach ($conditionalAttributes as $conditionalAttribute) {
                $query->where($conditionalAttribute['field'], $conditionalAttribute['operator'], $conditionalAttribute['operand']);
            }
        }

        return $query->first();
    }
}
