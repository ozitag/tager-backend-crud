<?php

namespace OZiTAG\Tager\Backend\Crud\Jobs;

use Ozerich\FileStorage\Storage;

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
            if (is_callable($requestField) && !is_string($requestField)) {
                $data[$field] = call_user_func($requestField, $this->request[$field]);
            } else {
                $parts = explode(':', $requestField);
                $data[$field] = $this->request[$parts[0]];

                if (count($parts) == 2) {
                    if($parts[1] === 'file' && is_string($parts[0])){
                        $data[$field] = Storage::fromUUIDtoId($data[$field]);
                    }
                }
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
