<?php

namespace OZiTAG\Tager\Backend\Crud\Actions;

class MoveAction extends DefaultAction
{
    protected ?string $eventClass;

    public function __construct(?string $eventClass)
    {
        $this->eventClass = $eventClass;
    }

    public function getEventClass(): string
    {
        return $this->eventClass;
    }
}
