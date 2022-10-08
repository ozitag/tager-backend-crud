<?php

namespace OZiTAG\Tager\Backend\Crud\Actions;

class CountAction extends DefaultAction
{
    protected mixed $queryBuilder = null;

    public function __construct(?string $getCountBuilderJobClass = null)
    {
        parent::__construct();

        $this->setQueryBuilder($getCountBuilderJobClass);
    }

    public function setQueryBuilder(mixed $queryBuilder): static
    {
        $this->queryBuilder = $queryBuilder;
        return $this;
    }

    public function getQueryBuilder(): mixed
    {
        return $this->queryBuilder;
    }
}
