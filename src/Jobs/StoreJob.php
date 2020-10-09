<?php

namespace OZiTAG\Tager\Backend\Crud\Jobs;

class StoreJob extends BaseCreateUpdateJob
{
    protected static $config = [];

    /**
     * @return bool
     */
    protected function hasPriority()
    {
        return isset(self::$config['hasPriority']) && self::$config['hasPriority'];
    }

    protected function getDefaultValues()
    {
        return isset(self::$config['defaultValues']) ? self::$config['defaultValues'] : [];
    }

    public function handle()
    {
        $data = $this->getDefaultValues();

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
