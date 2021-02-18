<?php

namespace OZiTAG\Tager\Backend\Crud\Jobs;

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
                $data[$field] = $this->request->get($requestField);
            }
        }

        if ($this->hasPriority()) {
            $maxPriorityItem = $this->repository()->findItemWithMaxPriority();
            $data['priority'] = $maxPriorityItem ? $maxPriorityItem->priority + 1 : 1;
        }

        $model = $this->repository()->fillAndSave($data);

        $updatedEventClass = $this->getUpdatedEventClass();
        if ($updatedEventClass) {
            $event = new $updatedEventClass($model);
            event($event);
        }

        return $model;
    }
}
