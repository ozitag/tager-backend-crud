<?php

namespace OZiTAG\Tager\Backend\Crud\Actions;

class StoreOrUpdateAction extends DefaultAction
{
    protected ?string $requestClass = null;
    protected ?string $jobClass = null;
    protected ?array $jobParams = null;

    public function __construct(?string $requestClass = null, ?string $jobClass = null, array $jobParams = [])
    {
        $this->requestClass = $requestClass;
        $this->jobClass = $jobClass;
        $this->jobParams = $jobParams;
    }

    /**
     * @return string|null
     */
    public function getRequestClass(): ?string {
        return $this->requestClass;
    }

    /**
     * @return string|null
     */
    public function getJobClass(): ?string {
        return $this->jobClass;
    }

    /**
     * @return array|null
     */
    public function getJobParams(): ?array {
        return $this->jobParams;
    }
}
