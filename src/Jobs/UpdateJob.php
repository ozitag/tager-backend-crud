<?php

namespace OZiTAG\Tager\Backend\Crud\Jobs;

class UpdateJob extends BaseCreateUpdateJob
{
    protected static $config = [];

    public function handle()
    {
        foreach ($this->fields() as $field => $requestField) {
            if (is_callable($requestField)) {
                $data[$field] = call_user_func($requestField, $this->request{$field});
            } else {
                $data[$field] = $this->request->{$requestField};
            }
        }

        $this->model->save();

        return $this->model;
    }
}
