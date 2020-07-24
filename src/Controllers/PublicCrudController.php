<?php

namespace OZiTAG\Tager\Backend\Crud\Controllers;

class PublicCrudController extends CrudController
{
    protected $hasIndexAction = true;

    protected $hasViewAction = true;

    protected $hasStoreAction = false;

    protected $hasUpdateAction = false;

    protected $hasDeleteAction = false;

    protected $hasMoveAction = false;
}
