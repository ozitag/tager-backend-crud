<?php

namespace OZiTAG\Tager\Backend\Crud\Jobs;

use Ozerich\FileStorage\Storage;

class UpdateJob extends BaseCreateUpdateJob
{
    protected static $config = [];

    /**
     * @return string
     */
    protected function getUpdatedEventClass()
    {
        $config = $this->config();

        return isset($config['updateEventClass']) ? $config['updateEventClass'] : [];
    }

    public function handle()
    {
        $this->repository()->set($this->model);

        $data = [];
        foreach ($this->fields() as $field => $requestField) {
            if (is_callable($requestField) && !is_string($requestField)) {
                $data[$field] = call_user_func($requestField, $this->request);
            } else {
                $parts = explode(':', $requestField);

                // A mapped field the payload does not carry is left untouched
                // rather than overwritten with NULL, so a partial payload can
                // no longer silently wipe columns it did not mention. A key
                // present with a null value is still written.
                if (!$this->request->has($parts[0])) {
                    continue;
                }

                $data[$field] = $this->request[$parts[0]];

                if (count($parts) == 2) {
                    if ($parts[1] === 'file' && is_string($parts[0])) {
                        $data[$field] = Storage::fromUUIDtoId($data[$field]);
                    } else if ($parts[1] === 'json' && is_string($parts[0])) {
                        $data[$field] = $data[$field] ? json_encode($data[$field]) : null;
                    }
                }

            }
        }

        $this->model = $this->repository()->fillAndSave($data);

        if (isset($this->config()['afterSaveJob'])) {
            $this->model = $this->run($this->config()['afterSaveJob'], [
                'model' => $this->model,
                'request' => $this->request
            ]);
        }

        $updatedEventClass = $this->getUpdatedEventClass();
        if ($updatedEventClass) {
            $event = new $updatedEventClass($this->model);
            event($event);
        }

        return $this->model;
    }
}
