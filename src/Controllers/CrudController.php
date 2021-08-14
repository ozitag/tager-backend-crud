<?php

namespace OZiTAG\Tager\Backend\Crud\Controllers;

use OZiTAG\Tager\Backend\Core\Controllers\Controller;
use OZiTAG\Tager\Backend\Core\Repositories\EloquentRepository;
use OZiTAG\Tager\Backend\Crud\Actions\CountAction;
use OZiTAG\Tager\Backend\Crud\Actions\DefaultAction;
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

    protected bool $isAdmin = false;

    protected bool $hasIndexAction = true;

    protected bool $hasViewAction = true;

    protected bool $hasStoreAction = true;

    protected bool $hasUpdateAction = true;

    protected bool $hasDeleteAction = true;

    protected bool $hasMoveAction = false;

    protected bool $hasCountAction = false;

    private EloquentRepository $repository;

    private ?string $getModelJobClass = null;

    private ?string $deleteModelJobClass = null;

    private ?string $shortResourceClass = null;

    private ?array $shortResourceFields = null;

    private ?string $fullResourceClass = null;

    private ?array $fullResourceFields = null;

    private string|array|null $cacheNamespace = null;


    // ***** Actions ****** //

    protected ?IndexAction $indexAction = null;

    protected ?CountAction $countAction = null;

    protected ?StoreOrUpdateAction $storeAction = null;

    protected ?StoreOrUpdateAction $updateAction = null;

    protected ?DeleteAction $deleteAction = null;

    protected array $customActions = [];

    // ***** ******* ****** //

    public function __construct(EloquentRepository $repository, ?string $getModelJobClass = null)
    {
        $this->repository = $repository;

        $this->getModelJobClass = $getModelJobClass;
    }

    public function setCacheNamespace(string|array $namespace)
    {
        $this->cacheNamespace = $namespace;
    }

    // ***** Actions ****** //

    public function setIndexAction(IndexAction $action)
    {
        $this->indexAction = $action;
    }

    public function setCountAction(CountAction $action)
    {
        $this->countAction = $action;
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

    protected function addAction($actionName, DefaultAction $action)
    {
        $this->customActions[$actionName] = $action;
    }

    // ***** ******* ****** //

    protected function setResourceFields(array $fields, bool $useFieldsForFullResource = false)
    {
        $this->shortResourceFields = $fields;

        if ($useFieldsForFullResource) {
            $this->setFullResourceFields($fields);
        }
    }

    protected function setShortResourceClass(string $class)
    {
        $this->shortResourceClass = $class;
    }

    protected function setFullResourceClass(string $class)
    {
        $this->fullResourceClass = $class;
    }

    protected function setResourceClasses(?string $shortResourceClass = null, ?string $fullResourceClass = null)
    {
        if ($shortResourceClass) {
            $this->setShortResourceClass($shortResourceClass);
        }

        if ($fullResourceClass) {
            $this->setFullResourceClass($fullResourceClass);
        }
    }

    protected function setFullResourceFields(array $fields)
    {
        $this->fullResourceFields = $fields;
    }

    protected function getResourceFields(): array
    {
        return $this->shortResourceFields;
    }

    protected function getFullResourceFields(): array
    {
        return $this->fullResourceFields;
    }

    public function features(): array
    {
        $result = [];

        if ($this->hasIndexAction) {
            $result['index'] = [
                ListFeature::class,
                $this->repository,
                $this->shortResourceClass,
                $this->shortResourceFields,
                $this->indexAction ?? new IndexAction(),
                $this->isAdmin
            ];
        }

        if ($this->hasViewAction) {
            $result['view'] = [
                ViewFeature::class,
                $this->getModelJobClass,
                $this->repository,
                $this->fullResourceClass,
                $this->fullResourceFields,
                $this->isAdmin
            ];
        }

        if ($this->hasDeleteAction) {
            $result['delete'] = [
                DeleteFeature::class,
                $this->getModelJobClass,
                $this->repository,
                $this->deleteAction ? $this->deleteAction->getCanDeleteJobClass() : null,
                $this->deleteModelJobClass,
                $this->cacheNamespace,
                $this->deleteAction ? $this->deleteAction->getDeletedModelEvent() : null
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
                $this->repository,
                $this->countAction ? $this->countAction->getCountBuilderJobClass() : null
            ];
        }

        if ($this->hasStoreAction && $this->storeAction) {
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
                $this->cacheNamespace,
                $this->storeAction->getEventClass()
            ];
        }

        if ($this->hasUpdateAction && $this->updateAction) {
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
                $this->cacheNamespace,
                $this->updateAction->getEventClass(),
                $this->isAdmin
            ];
        }

        foreach ($this->customActions as $actionName => $action) {
            if ($action instanceof IndexAction) {
                $result[$actionName] = [
                    ListFeature::class,
                    $this->repository,
                    $action->resourceClass ?? $this->shortResourceClass,
                    $action->resourceFields ?? $this->shortResourceFields,
                    $action,
                    $this->isAdmin
                ];
            }
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

    public function __call($name, $arguments)
    {
        if (isset($this->customActions[$name])) {
            return $this->serve($name);
        }

        parent::__call($name, $arguments);
    }
}
