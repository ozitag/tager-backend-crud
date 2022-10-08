<?php

namespace OZiTAG\Tager\Backend\Crud\Actions;

class DeleteAction extends DefaultAction
{
    protected mixed $validator = null;

    protected ?string $deletedModelEvent = null;

    public function __construct(?string $validatorJobClass = null, ?string $deletedModelEvent = null)
    {
        $this->setEvent($deletedModelEvent);

        $this->setValidator($validatorJobClass);
    }

    public function getValidator(): mixed
    {
        return $this->validator;
    }

    public function setValidator(string|callable|null $validator): self
    {
        if ($validator) {
            $this->validator = $validator;
        }

        return $this;
    }

    public function getEventName(): ?string
    {
        return $this->deletedModelEvent;
    }

    public function setEvent(?string $eventName): self
    {
        if ($eventName) {
            $this->deletedModelEvent = $eventName;
        }

        return $this;
    }
}
