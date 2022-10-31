<?php

namespace OZiTAG\Tager\Backend\Crud\Jobs;

use Ozerich\FileStorage\Storage;
use OZiTAG\Tager\Backend\Utils\Helpers\Translit;

class StoreJob extends BaseCreateUpdateJob
{
    protected static $config = [];

    protected function hasPriority(): bool
    {
        return self::$config['hasPriority'] ?? false;
    }

    protected function getDefaultValues(): ?array
    {
        return self::$config['defaultValues'] ?? [];
    }

    protected function getUrlAliasFieldGenerator(): ?array
    {
        return self::$config['urlAliasGenerator'] ?? null;
    }

    protected function getUpdatedEventClass(): ?string
    {
        return self::$config['updateEventClass'] ?? null;
    }

    protected function getUrlAliasValue(string $field, string $nameField): ?string
    {
        $ind = 0;
        $name = trim($this->request->get($nameField));

        if (empty($name)) {
            return null;
        }

        while (true) {
            $alias = Translit::translit($name) . ($ind == 0 ? '' : '-' . $ind);

            $existed = $this->repository()->builder()->where($field, '=', $alias)->first();
            if (!$existed) {
                return $alias;
            }

            $ind = $ind + 1;
        }
    }

    public function handle()
    {
        $data = $this->getDefaultValues();

        $urlAliasFieldGenerator = $this->getUrlAliasFieldGenerator();
        if (!empty($urlAliasFieldGenerator)) {
            $urlAliasValue = $this->getUrlAliasValue($urlAliasFieldGenerator['field'], $urlAliasFieldGenerator['nameField']);
            $data[$urlAliasFieldGenerator['field']] = $urlAliasValue;
        }

        foreach ($this->fields() as $field => $requestField) {
            if (is_callable($requestField) && !is_string($requestField)) {
                $data[$field] = call_user_func($requestField, $this->request[$field]);
            } else {
                $parts = explode(':', $requestField);
                $data[$field] = $this->request[$parts[0]];

                if (count($parts) == 2) {
                    if ($parts[1] === 'file' && is_string($parts[0])) {
                        $data[$field] = Storage::fromUUIDtoId($data[$field]);
                    }  else if ($parts[1] === 'json' && is_string($parts[0])) {
                        $data[$field] = $data[$field] ? json_encode($data[$field]) : null;
                    }
                }
            }
        }

        if ($this->hasPriority()) {
            $maxPriorityItem = $this->repository()->findItemWithMaxPriority();
            $data['priority'] = $maxPriorityItem ? $maxPriorityItem->priority + 1 : 1;
        }

        $model = $this->repository()->fillAndSave($data);

        if (isset(self::$config['afterSaveJob'])) {
            $model = $this->run(self::$config['afterSaveJob'], [
                'model' => $this->model,
                'request' => $this->request
            ]);
        }

        $updatedEventClass = $this->getUpdatedEventClass();
        if ($updatedEventClass) {
            $event = new $updatedEventClass($model, null, $model->getAttributes());
            event($event);
        }

        return $model;
    }
}
