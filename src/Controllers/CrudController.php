<?php

namespace OZiTAG\Tager\Backend\Crud\Controllers;

use OZiTAG\Tager\Backend\Core\Controllers\Controller;
use OZiTAG\Tager\Backend\Core\Repositories\EloquentRepository;
use OZiTAG\Tager\Backend\Crud\Features\DeleteFeature;
use OZiTAG\Tager\Backend\Crud\Features\ListFeature;
use OZiTAG\Tager\Backend\Crud\Features\MoveFeature;
use OZiTAG\Tager\Backend\Crud\Features\StoreFeature;
use OZiTAG\Tager\Backend\Crud\Features\UpdateFeature;
use OZiTAG\Tager\Backend\Crud\Features\ViewFeature;

class CrudController extends Controller
{
    protected $hasViewAction = true;

    protected $hasStoreAction = true;

    protected $hasUpdateAction = true;

    protected $hasDeleteAction = true;

    protected $hasMoveAction = false;

    private $repository;

    private $getModelJobClass;

    private $createModelJobClass;

    private $createRequestClass;

    private $updateModelJobClass;

    private $updateRequestClass;

    private $deleteModelJobClass;

    private $shortResourceClass;

    private $shortResourceFields;

    private $fullResourceClass;

    private $fullResourceFields;

    public function __construct(EloquentRepository $repository, $getModelJobClass = null, $createModelJobClass = null, $createRequestClass = null, $updateModelJobClass = null, $updateRequestClass = null, $deleteModelJobClass = null, $shortResourceClass = null, $fullResourceClass = null)
    {
        $this->repository = $repository;

        $this->getModelJobClass = $getModelJobClass;
        $this->createModelJobClass = $createModelJobClass;
        $this->createRequestClass = $createRequestClass;
        $this->updateModelJobClass = $updateModelJobClass;
        $this->updateRequestClass = $updateRequestClass;
        $this->deleteModelJobClass = $deleteModelJobClass;
        $this->shortResourceClass = $shortResourceClass;
        $this->fullResourceClass = $fullResourceClass;
    }

    protected function setResourceClasses($shortResourceClass = null, $fullResourceClass = null)
    {
        $this->shortResourceClass = $shortResourceClass;
        $this->fullResourceClass = $fullResourceClass;
    }

    protected function setResourceFields($fields)
    {
        $this->shortResourceFields = $fields;
    }

    protected function setFullResourceFields($fields)
    {
        $this->fullResourceFields = $fields;
    }

    protected function setStoreAction($createRequestClass = null, $createModelJobClass = null)
    {
        $this->createRequestClass = $createRequestClass;
        $this->createModelJobClass = $createModelJobClass;
    }

    protected function setUpdateAction($updateRequestClass = null, $updateModelJobClass = null)
    {
        $this->updateRequestClass = $updateRequestClass;
        $this->updateModelJobClass = $updateModelJobClass;
    }

    public function features()
    {
        $result = [];

        $result['index'] = [
            ListFeature::class,
            $this->repository,
            $this->shortResourceClass,
            $this->shortResourceFields
        ];

        if ($this->hasViewAction) {
            $result['view'] = [
                ViewFeature::class,
                $this->getModelJobClass,
                $this->repository,
                $this->fullResourceClass,
                $this->fullResourceFields,
            ];
        }

        if ($this->hasDeleteAction) {
            $result['delete'] = [
                DeleteFeature::class,
                $this->getModelJobClass,
                $this->repository,
                $this->deleteModelJobClass
            ];
        }

        if ($this->hasMoveAction) {
            $result['move'] = [
                MoveFeature::class,
                $this->getModelJobClass,
                $this->repository,
            ];
        }

        if ($this->hasStoreAction) {
            $result['store'] = [
                StoreFeature::class,
                $this->createRequestClass,
                $this->createModelJobClass,
                $this->fullResourceClass,
                $this->fullResourceFields
            ];
        }

        if ($this->hasUpdateAction) {
            $result['update'] = [
                UpdateFeature::class,
                $this->getModelJobClass,
                $this->repository,
                $this->updateRequestClass,
                $this->updateModelJobClass,
                $this->fullResourceClass,
                $this->fullResourceFields,
            ];
        }

        return $result;
    }

    protected function index()
    {
        return $this->serve('index');
    }

    protected function store()
    {
        return $this->serve('store');
    }

    public function view($id)
    {
        return $this->serve('view', ['id' => $id]);
    }

    protected function update($id)
    {
        return $this->serve('update', ['id' => $id]);
    }

    public function delete($id)
    {
        return $this->serve('delete', ['id' => $id]);
    }

    public function move($id, $direction)
    {
        return $this->serve('move', ['id' => $id, 'direction' => $direction]);
    }
}
