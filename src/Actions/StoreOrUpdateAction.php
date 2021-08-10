<?php

namespace OZiTAG\Tager\Backend\Crud\Actions;

class StoreOrUpdateAction extends DefaultAction
{
    protected ?string $requestClass = null;
    protected ?string $jobClass = null;
    protected ?array $jobParams = null;
    protected ?string $eventClass = null;

    public function __construct(?string $requestClass = null, ?string $jobClass = null, ?array $jobParams = [], ?string $eventClass = null)
    {
        $this->requestClass = $requestClass;
        $this->jobClass = $jobClass;
        $this->jobParams = $jobParams;
        $this->eventClass = $eventClass;
    }

    public function getRequestClass(): ?string
    {
        return $this->requestClass;
    }

    public function getJobClass(): ?string
    {
        return $this->jobClass;
    }

    public function getJobParams(): ?array
    {
        return $this->jobParams;
    }

    public function getEventClass(): ?string
    {
        return $this->eventClass;
    }
}
