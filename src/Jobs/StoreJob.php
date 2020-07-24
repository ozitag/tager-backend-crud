<?php

namespace OZiTAG\Tager\Backend\Crud\Jobs;

class StoreJob extends BaseCreateUpdateJob
{
    /**
     * @return bool
     */
    private function hasPriority()
    {
        return isset(self::$config['hasPriority']) && self::$config['hasPriority'];
    }

    public function process()
    {
        $data = [];
        foreach ($this->fields() as $field => $requestField) {
            $data[$field] = $this->request->{$requestField};
        }

        if ($this->hasPriority()) {
            $maxPriorityItem = $this->repository()->findItemWithMaxPriority();
            $data['priority'] = $maxPriorityItem ? $maxPriorityItem->priority + 1 : 1;
        }

        return $this->repository()->fillAndSave($data);
    }
}
