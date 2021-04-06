<?php

namespace OZiTAG\Tager\Backend\Crud\Jobs;

use Ozerich\FileStorage\Exceptions\InvalidFileForScenarioException;
use Ozerich\FileStorage\Storage;
use OZiTAG\Tager\Backend\Core\Jobs\Job;
use OZiTAG\Tager\Backend\Crud\Requests\CrudFormRequest;
use OZiTAG\Tager\Backend\Validation\Facades\Validation;

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

            $value = $value[$part];
        }

        return $value;
    }

    public function handle(Storage $storage)
    {
        $fileScenarios = $this->request->fileScenarios();
        foreach ($fileScenarios as $field => $scenario) {
            $pointPos = strpos($field, '.');
            $innerField = null;
            if ($pointPos !== false) {
                $innerField = substr($field, $pointPos + 1);
                $field = substr($field, 0, $pointPos);
            }

            $value = $this->getValue($field);

            if ($value) {
                if (is_array($value)) {
                    foreach ($value as $ind => $item) {
                        try {
                            if ($innerField && is_array($item) && isset($item[$innerField])) {
                                $storage->setFileScenario($item[$innerField], $scenario);
                            } else {
                                $storage->setFileScenario($item, $scenario);
                            }
                        } catch (InvalidFileForScenarioException $exception) {
                            Validation::throw(
                                $field . '.' . $ind . ($innerField ? '.' . $innerField : ''),
                                $exception->getMessage()
                            );
                        }
                    }
                } else {
                    try {
                        $storage->setFileScenario($value, $scenario);
                    } catch (InvalidFileForScenarioException $exception) {
                        Validation::throw($field, $exception->getMessage());
                    }
                }
            }
        }
    }
}
