<?php

namespace OZiTAG\Tager\Backend\Crud\Actions;

class DeleteAction extends DefaultAction
{
    protected mixed $validator = null;

    protected ?string $deletedModelEvent = null;
    
    protected ?string $jobClass = null;
    
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


    public function getJobClass(): ?string
    {
        return $this->jobClass;
    }

    public function setJobClass(?string $jobClass): self
    {
        if ($jobClass) {
            $this->jobClass = $jobClass;
        }

        return $this;
    }
}
