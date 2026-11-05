<?php

namespace OZiTAG\Tager\Backend\Crud\Jobs;

use Ozerich\FileStorage\Storage;
use OZiTAG\Tager\Backend\Crud\Contracts\IModelPriorityConditional;
use OZiTAG\Tager\Backend\Utils\Helpers\Translit;

class StoreJob extends BaseCreateUpdateJob
{
    protected static $config = [];

    protected function hasPriority(): bool
    {
        return $this->config()['hasPriority'] ?? false;
    }

    protected function getDefaultValues(): ?array
    {
        return $this->config()['defaultValues'] ?? [];
    }

    protected function getUrlAliasFieldGenerator(): ?array
    {
        return $this->config()['urlAliasGenerator'] ?? null;
    }

    protected function getUpdatedEventClass(): ?string
    {
        return $this->config()['updateEventClass'] ?? null;
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
                $data[$field] = call_user_func($requestField, $this->request);
            } else {
                $parts = explode(':', $requestField);

                // A mapped field that the payload does not carry is left out of
                // the INSERT entirely. Writing an explicit NULL here used to
                // override the column's database default and break NOT NULL
                // columns. A key present with a null value is still written.
                if (!$this->request->has($parts[0])) {
                    continue;
                }

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

            $conditionalAttributesWithFields = [];

            $model = $this->repository()->createModelInstance();
            if ($model instanceof IModelPriorityConditional) {
                $conditionalAttributes = $model->getPriorityConditionalAttributes();
                foreach ($model->getPriorityConditionalAttributes() as $field) {
                    if(array_key_exists($field, $data)){
                        $conditionalAttributesWithFields[] = [
                            'field' => $field,
                            'operator' => '=',
                            'operand' => $data[$field]
                        ];
                    }
                }
            }

            $maxPriorityItem = $this->repository()->findItemWithMaxPriority($conditionalAttributesWithFields);
            $data['priority'] = $maxPriorityItem ? $maxPriorityItem->priority + 1 : 1;
        }

        $model = $this->repository()->fillAndSave($data);

        if (isset($this->config()['afterSaveJob'])) {
            $model = $this->run($this->config()['afterSaveJob'], [
                'model' => $model,
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
