<?php

namespace OZiTAG\Tager\Backend\Crud\Jobs;

class UpdateJob extends BaseCreateUpdateJob
{
    protected static $config = [];

    public function process()
    {
        foreach ($this->fields() as $field => $requestField) {
            $this->model->{$field} = $this->request->{$requestField};
        }

        $this->model->save();

        return $this->model;
    }
}
