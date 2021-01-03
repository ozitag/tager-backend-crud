<?php

namespace OZiTAG\Tager\Backend\Crud\Controllers;

class PublicCrudController extends CrudController
{
    protected bool $hasIndexAction = true;

    protected bool $hasViewAction = true;

    protected bool $hasStoreAction = false;

    protected bool $hasUpdateAction = false;

    protected bool $hasDeleteAction = false;

    protected bool $hasMoveAction = false;
}
