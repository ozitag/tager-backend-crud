<?php

namespace OZiTAG\Tager\Backend\Crud\Traits;

trait RepositoryPriorityMethodsTrait
{
    public function findItemWithMinPriority($conditionalAttributes = [])
    {
        $query = $this->model->orderBy('priority', 'asc');

        if ($conditionalAttributes) {
            foreach ($conditionalAttributes as $field => $value) {
                $query->where($field, '=', $value);
            }
        }

        return $query->first();
    }

    public function findItemWithMaxPriority($conditionalAttributes = [])
    {
        $query = $this->model->orderBy('priority', 'desc');

        if ($conditionalAttributes) {
            foreach ($conditionalAttributes as $field => $value) {
                $query->where($field, '=', $value);
            }
        }

        return $query->first();
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
