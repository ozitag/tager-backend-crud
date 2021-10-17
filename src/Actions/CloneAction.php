<?php

namespace OZiTAG\Tager\Backend\Crud\Actions;

class CloneAction extends DefaultAction
{
    protected string $copyEntityJobClass;

    public function __construct(string $copyEntityJobClass)
    {
        $this->copyEntityJobClass = $copyEntityJobClass;
    }

    public function getCopyEntityJobClass(): string
    {
        return $this->copyEntityJobClass;
    }
}
