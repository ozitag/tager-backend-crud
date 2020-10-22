<?php

namespace OZiTAG\Tager\Backend\Crud\Jobs;

class UpdateJob extends BaseCreateUpdateJob
{
    protected static $config = [];

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

        $this->repository()->fillAndSave($data);

        return $this->model;
    }
}
