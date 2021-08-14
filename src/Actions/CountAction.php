<?php

namespace OZiTAG\Tager\Backend\Crud\Actions;

class CountAction extends DefaultAction
{
    protected ?string $getCountBuilderJobClass = null;

    public function __construct(?string $getCountBuilderJobClass = null)
    {
        parent::__construct();

        $this->getCountBuilderJobClass = $getCountBuilderJobClass;
    }

    public function getCountBuilderJobClass(): ?string
    {
        return $this->getCountBuilderJobClass;
    }
}
