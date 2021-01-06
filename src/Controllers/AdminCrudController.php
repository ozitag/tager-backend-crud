<?php

namespace OZiTAG\Tager\Backend\Crud\Controllers;

class AdminCrudController extends CrudController
{
    protected bool $isAdmin = true;

    protected bool $hasIndexAction = true;

    protected bool $hasViewAction = true;

    protected bool $hasStoreAction = true;

    protected bool $hasUpdateAction = true;

    protected bool $hasDeleteAction = true;

    protected bool $hasMoveAction = false;

    protected bool $hasCountAction = false;
}
