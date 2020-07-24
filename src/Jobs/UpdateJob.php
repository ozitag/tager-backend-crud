<?php

namespace OZiTAG\Tager\Backend\Crud\Jobs;

class UpdateJob extends BaseCreateUpdateJob
{
    public function process()
    {
        foreach ($this->fields() as $field => $requestField) {
            $this->model->{$field} = $this->request->{$requestField};
        }

        $this->model->save();

        return $this->model;
    }
}
