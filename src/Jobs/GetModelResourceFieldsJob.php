<?php

namespace OZiTAG\Tager\Backend\Crud\Jobs;

use Ozerich\FileStorage\Storage;
use OZiTAG\Tager\Backend\Core\Jobs\Job;

class GetModelResourceFieldsJob extends Job
{
    protected $resourceFields;

    protected $isAdmin;

    public function __construct(array $resourceFields, bool $isAdmin)
    {
        $this->resourceFields = $resourceFields;

        $this->isAdmin = $isAdmin;
    }

    private function rec($resourceFields)
    {
        return array_map(function ($resourceField) {
            if (is_array($resourceField)) {
                return $this->rec($resourceField);
            }

            if (!is_string($resourceField)) return $resourceField;

            $fieldParts = explode(':', $resourceField);
            if (count($fieldParts) == 3 && $fieldParts[1] == 'file' && $fieldParts[2] == 'url') {
                return $resourceField . ':tager-admin-list';
            }
            if (count($fieldParts) == 3 && $fieldParts[1] == 'file' && $fieldParts[2] == 'model') {
                return $resourceField . ':tager-admin-view';
            }

            return $resourceField;
        }, $resourceFields);
    }

    public function handle()
    {
        if (!$this->isAdmin) {
            $resourceFields = $this->resourceFields;
        } else {
            $resourceFields = $this->rec($this->resourceFields);
        }

        return $resourceFields;
    }
}
