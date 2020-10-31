<?php

namespace OZiTAG\Tager\Backend\Crud\Actions;

class DeleteAction extends DefaultAction
{
    protected ?string $canDeleteJobClass = null;

    public function __construct(?string $canDeleteJobClass = null)
    {
        $this->canDeleteJobClass = $canDeleteJobClass;
    }

    /**
     * @return string|null
     */
    public function getCanDeleteJobClass(): ?string {
        return $this->canDeleteJobClass;
    }
}
