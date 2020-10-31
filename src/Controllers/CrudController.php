<?php

namespace OZiTAG\Tager\Backend\Crud\Controllers;

use OZiTAG\Tager\Backend\Core\Controllers\Controller;
use OZiTAG\Tager\Backend\Core\Repositories\EloquentRepository;
use OZiTAG\Tager\Backend\Crud\Actions\DeleteAction;
use OZiTAG\Tager\Backend\Crud\Actions\IndexAction;
use OZiTAG\Tager\Backend\Crud\Actions\StoreOrUpdateAction;
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
    public bool $pagination = false;

    protected $hasIndexAction = true;

    protected $hasViewAction = true;

    protected $hasStoreAction = true;

    protected $hasUpdateAction = true;

    protected $hasDeleteAction = true;

    protected $hasMoveAction = false;

    protected $hasCountAction = false;

    private $repository;

    private $getModelJobClass;

    private $deleteModelJobClass;

    private $shortResourceClass;

    private $shortResourceFields;

    private $fullResourceClass;

    private $fullResourceFields;

    private $cacheNamespace;


    // ***** Actions ****** //

    protected ?IndexAction $indexAction = null;

    protected ?StoreOrUpdateAction $storeAction = null;

    protected ?StoreOrUpdateAction $updateAction = null;

    protected ?DeleteAction $deleteAction = null;

    // ***** ******* ****** //

    public function __construct(EloquentRepository $repository, $getModelJobClass = null)
    {
        $this->repository = $repository;
        $this->getModelJobClass = $getModelJobClass;
    }

    public function setCacheNamespace($namespace)
    {
        $this->cacheNamespace = $namespace;
    }

    // ***** Actions ****** //

    public function setIndexAction(IndexAction $action)
    {
        $this->indexAction = $action;
    }

    public function setDeleteAction(DeleteAction $action)
    {
        $this->deleteAction = $action;
    }

    protected function setStoreAction(StoreOrUpdateAction $action)
    {
        $this->storeAction = $action;
    }

    protected function setUpdateAction(StoreOrUpdateAction $action)
    {
        $this->updateAction = $action;
    }

    protected function setStoreAndUpdateAction(StoreOrUpdateAction $action)
    {
        $this->updateAction = $this->storeAction = $action;
    }

    // ***** ******* ****** //

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
                $this->indexAction ?? new IndexAction(),
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
                $this->deleteAction ? $this->deleteAction->getCanDeleteJobClass() : null,
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
            if (!$this->storeAction->getJobClass()) {
                if ($this->storeAction->getJobParams()) {
                    StoreJob::setConfig(array_merge($this->storeAction->getJobParams(), [
                        'hasPriority' => $this->hasMoveAction
                    ]));
                }
                $jobClass = StoreJob::class;
            } else {
                $jobClass = $this->storeAction->getJobClass();
            }

            $result['store'] = [
                StoreFeature::class,
                $this->storeAction->get('requestClass'),
                $jobClass,
                $this->fullResourceClass,
                $this->fullResourceFields,
                $this->cacheNamespace
            ];
        }

        if ($this->hasUpdateAction) {
            if (!$this->updateAction->getJobClass()) {
                if ($this->updateAction->getJobParams()) {
                    UpdateJob::setConfig($this->updateAction->getJobParams());
                }
                $jobClass = UpdateJob::class;
            } else {
                $jobClass = $this->updateAction->getJobClass();
            }

            $result['update'] = [
                UpdateFeature::class,
                $this->getModelJobClass,
                $this->repository,
                $this->updateAction->getRequestClass(),
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
