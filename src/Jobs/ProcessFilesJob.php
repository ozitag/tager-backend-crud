<?php

namespace OZiTAG\Tager\Backend\Crud\Jobs;

use Ozerich\FileStorage\Storage;
use OZiTAG\Tager\Backend\Core\Jobs\Job;
use OZiTAG\Tager\Backend\Crud\Requests\CrudFormRequest;

class ProcessFilesJob extends Job
{
    /** @var CrudFormRequest $request */
    private $request;

    public function __construct(CrudFormRequest $request)
    {
        $this->request = $request;
    }

    private function getValue($field)
    {
        $parts = explode('.', $field);
        $value = $this->request;

        $isArray = false;

        for ($i = 0; $i < count($parts); $i++) {
            $part = $parts[$i];
            if ($part == '*') {
                if ($i == count($parts) - 1) {
                    return $value;
                } else {
                    $isArray = true;
                    continue;
                }
            }

            if ($isArray) {
                $result = [];
                foreach ($value as $item) {
                    $result[] = $item[$part];
                }
                return $result;
            }

            $value = $i == 0 ? $value->{$part} : $value[$part];
        }

        return $value;
    }

    public function handle(Storage $storage)
    {
        $fileScenarios = $this->request->fileScenarios();
        foreach ($fileScenarios as $field => $scenario) {
            $value = $this->getValue($field);

            if ($value) {
                if (is_array($value)) {
                    foreach ($value as $item) {
                        $storage->setFileScenario($item, $scenario);
                    }
                } else {
                    $storage->setFileScenario($value, $scenario);
                }
            }
        }
    }
}
