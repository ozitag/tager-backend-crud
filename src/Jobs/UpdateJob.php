<?php

namespace OZiTAG\Tager\Backend\Crud\Jobs;

class UpdateJob extends BaseCreateUpdateJob
{
    protected static $config = [];

    /**
     * @return string
     */
    protected function getUpdatedEventClass()
    {
        return isset(self::$config['updateEventClass']) ? self::$config['updateEventClass'] : [];
    }

    public function handle()
    {
        $this->repository()->set($this->model);

        $data = [];
        foreach ($this->fields() as $field => $requestField) {
            if (is_callable($requestField)) {
                $data[$field] = call_user_func($requestField, $this->request{$field});
            } else {
                $data[$field] = $this->request->{$requestField};
            }
        }

        $this->model = $this->repository()->fillAndSave($data);

        $updatedEventClass = $this->getUpdatedEventClass();
        if ($updatedEventClass) {
            $event = new $updatedEventClass($this->model);
            event($event);
        }

        return $this->model;
    }
}
