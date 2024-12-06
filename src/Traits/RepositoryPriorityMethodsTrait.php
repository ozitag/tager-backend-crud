<?php

namespace OZiTAG\Tager\Backend\Crud\Traits;

trait RepositoryPriorityMethodsTrait
{
    private function addConditionalAttributesToQuery(&$query, ?array $conditionalAttributes = null)
    {
        if ($conditionalAttributes && is_array($conditionalAttributes)) {
            foreach ($conditionalAttributes as $param => $conditionalAttribute) {
                if (!array_is_list($conditionalAttributes)) {
                    $query->where($param, $conditionalAttribute);
                } else {
                    $query->where($conditionalAttribute['field'], $conditionalAttribute['operator'], $conditionalAttribute['operand']);
                }
            }
        }
    }

    public function findItemWithMinPriority($conditionalAttributes = [])
    {
        $query = $this->model->orderBy('priority', 'asc');
        $this->addConditionalAttributesToQuery($query, $conditionalAttributes);
        return $query->first();
    }

    public function findItemWithMaxPriority($conditionalAttributes = [])
    {
        $query = $this->model->orderBy('priority', 'desc');
        $this->addConditionalAttributesToQuery($query, $conditionalAttributes);
        return $query->first();
    }

    public function findFirstWithLowerPriorityThan($priority, $conditionalAttributes = [])
    {
        $query = $this->model::query()->where('priority', '<', $priority)->orderBy('priority', 'desc');
        $this->addConditionalAttributesToQuery($query, $conditionalAttributes);
        return $query->first();
    }

    public function findFirstWithHigherPriorityThan($priority, $conditionalAttributes = [])
    {
        $query = $this->model::query()->where('priority', '>', $priority)->orderBy('priority', 'asc');
        $this->addConditionalAttributesToQuery($query, $conditionalAttributes);
        return $query->first();
    }
}
