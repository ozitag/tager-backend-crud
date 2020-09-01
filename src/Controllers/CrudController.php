<?php

namespace OZiTAG\Tager\Backend\Crud\Controllers;

use OZiTAG\Tager\Backend\Core\Controllers\Controller;
use OZiTAG\Tager\Backend\Core\Repositories\EloquentRepository;
use OZiTAG\Tager\Backend\Crud\Features\CountFeature;
use OZiTAG\Tager\Backend\Crud\Features\DeleteFeature;
use OZiTAG\Tager\Backend\Crud\Features\ListFeature;
use OZiTAG\Tager\Backend\Crud\Features\MoveFeature;
use OZiTAG\Tager\Backend\Crud\Features\StoreFeature;
use OZiTAG\Tager\Backend\Crud\Features\UpdateFeature;
use OZiTAG\Tager\Backend\Crud\Features\ViewFeature;
use OZiTAG\Tager\Backend\Crud\Jobs\StoreJob;
use OZiTAG\Tager\Backend\Crud\Jobs\UpdateJob;

class CrudController extends Controller
{
    protected $hasIndexAction = true;

    protected $hasViewAction = true;

    protected $hasStoreAction = true;

    protected $hasUpdateAction = true;

    protected $hasDeleteAction = true;

    protected $hasMoveAction = false;

    protected $hasCountAction = false;

    private $repository;

    private $indexActionIsTree = false;

    private $getModelJobClass;

    private $createRequestClass;

    private $createModelJobClass;

    private $createModelDefaultJobParams;

    private $updateRequestClass;

    private $updateModelJobClass;

    private $updateModelDefaultJobParams;

    private $deleteModelJobClass;

    private $shortResourceClass;

    private $shortResourceFields;

    private $fullResourceClass;

    private $fullResourceFields;

    private $cacheNamespace;

    private $checkIfCanDeleteJobClass;

    public function __construct(EloquentRepository $repository, $getModelJobClass = null)
    {
        $this->repository = $repository;
        $this->getModelJobClass = $getModelJobClass;
    }

    public function setCacheNamespace($namespace)
    {
        $this->cacheNamespace = $namespace;
    }

    public function setIndexAction($isTree)
    {
        $this->indexActionIsTree = $isTree;
    }

    public function setDeleteAction($checkIfCanDeleteJobClass = null)
    {
        $this->checkIfCanDeleteJobClass = $checkIfCanDeleteJobClass;
    }

    protected function setResourceClasses($shortResourceClass = null, $fullResourceClass = null)
    {
        $this->shortResourceClass = $shortResourceClass;
        $this->fullResourceClass = $fullResourceClass;
    }

    protected function setResourceFields($fields, $useFieldsForFullResource = false)
    {
        $this->shortResourceFields = $fields;

        if($useFieldsForFullResource){
            $this->setFullResourceFields($fields);
        }
    }

    protected function setFullResourceFields($fields)
    {
        $this->fullResourceFields = $fields;
    }

    protected function setStoreAction($createRequestClass = null, $createModelJobClass = null, $defaultCreateModelJobParams = [])
    {
        $this->createRequestClass = $createRequestClass;
        $this->createModelJobClass = $createModelJobClass;
        $this->createModelDefaultJobParams = $defaultCreateModelJobParams;
    }

    protected function setUpdateAction($updateRequestClass = null, $updateModelJobClass = null, $defaultUpdateModelJobParams = [])
    {
        $this->updateRequestClass = $updateRequestClass;
        $this->updateModelJobClass = $updateModelJobClass;
        $this->updateModelDefaultJobParams = $defaultUpdateModelJobParams;
    }

    protected function setStoreAndUpdateAction($requestClass, $defaultModelJobParams)
    {
        $this->setStoreAction($requestClass, null, $defaultModelJobParams);
        $this->setUpdateAction($requestClass, null, $defaultModelJobParams);
    }

    protected function getResourceFields()
    {
        return $this->shortResourceFields;
    }

    protected function getFullResourceFields()
    {
        return $this->fullResourceFields;
    }

    public function features()
    {
        $result = [];

        if ($this->hasIndexAction) {
            $result['index'] = [
                ListFeature::class,
                $this->repository,
                $this->shortResourceClass,
                $this->shortResourceFields,
                $this->indexActionIsTree
            ];
        }

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
                $this->checkIfCanDeleteJobClass,
                $this->deleteModelJobClass,
                $this->cacheNamespace
            ];
        }

        if ($this->hasMoveAction) {
            $result['move'] = [
                MoveFeature::class,
                $this->getModelJobClass,
                $this->repository,
                $this->cacheNamespace
            ];
        }

        if ($this->hasCountAction) {
            $result['count'] = [
                CountFeature::class,
                $this->repository
            ];
        }

        if ($this->hasStoreAction) {
            if (!$this->createModelJobClass) {
                if ($this->createModelDefaultJobParams) {
                    StoreJob::setConfig(array_merge($this->createModelDefaultJobParams, [
                        'hasPriority' => $this->hasMoveAction
                    ]));
                }
                $jobClass = StoreJob::class;
            } else {
                $jobClass = $this->createModelJobClass;
            }

            $result['store'] = [
                StoreFeature::class,
                $this->createRequestClass,
                $jobClass,
                $this->fullResourceClass,
                $this->fullResourceFields,
                $this->cacheNamespace
            ];
        }

        if ($this->hasUpdateAction) {
            if (!$this->updateModelJobClass) {
                if ($this->updateModelDefaultJobParams) {
                    UpdateJob::setConfig($this->updateModelDefaultJobParams);
                }
                $jobClass = UpdateJob::class;
            } else {
                $jobClass = $this->updateModelJobClass;
            }

            $result['update'] = [
                UpdateFeature::class,
                $this->getModelJobClass,
                $this->repository,
                $this->updateRequestClass,
                $jobClass,
                $this->fullResourceClass,
                $this->fullResourceFields,
                $this->cacheNamespace
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

    protected function count()
    {
        return $this->serve('count');
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
