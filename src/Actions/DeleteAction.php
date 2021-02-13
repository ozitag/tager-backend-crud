<?php

namespace OZiTAG\Tager\Backend\Crud\Actions;

class DeleteAction extends DefaultAction
{
    protected ?string $canDeleteJobClass = null;

    protected ?string $deletedModelEvent = null;

    public function __construct(?string $canDeleteJobClass = null, ?string $deletedModelEvent = null)
    {
        $this->canDeleteJobClass = $canDeleteJobClass;

        $this->deletedModelEvent = $deletedModelEvent;
    }

    /**
     * @return string|null
     */
    public function getCanDeleteJobClass(): ?string
    {
        return $this->canDeleteJobClass;
    }

    /**
     * @return string|null
     */
    public function getDeletedModelEvent(): ?string
    {
        return $this->deletedModelEvent;
    }
}
