<?php

namespace OZiTAG\Tager\Backend\Crud\Controllers;

class AdminCrudController extends CrudController
{
    protected $hasIndexAction = true;

    protected $hasViewAction = true;

    protected $hasStoreAction = true;

    protected $hasUpdateAction = true;

    protected $hasDeleteAction = true;

    protected $hasMoveAction = false;
    
    protected $hasCountAction = false;
}
