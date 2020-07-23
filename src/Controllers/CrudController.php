<?php

namespace OZiTAG\Tager\Backend\Crud\Controllers;

use App\Http\Requests\Admin\CompletedProjects\CompletedProjectRequest;
use App\Http\Resources\Admin\CompletedProjectFullResource;
use App\Jobs\Sync\CompletedProject\CreateCompletedProjectJob;
use App\Jobs\Sync\CompletedProject\DeleteCompletedProjectJob;
use App\Jobs\Sync\CompletedProject\GetCompletedProjectByIdJob;
use App\Jobs\Sync\CompletedProject\UpdateCompletedProjectJob;
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
    const INDEX = 'index';
    const STORE = 'store';
    const VIEW = 'view';
    const UPDATE = 'update';
    const DELETE = 'delete';
    const MOVE = 'move';

    private $repository;

    private $getModelJobClass;

    private $createModelJobClass;

    private $createRequestClass;

    private $updateModelJobClass;

    private $updateRequestClass;

    private $deleteModelJobClass;

    private $shortResourceClass;

    private $fullResourceClass;

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

    public function features()
    {
        return [
            self::INDEX => [
                ListFeature::class,
                $this->repository,
                $this->shortResourceClass
            ],

            self::VIEW => [
                ViewFeature::class,
                $this->getModelJobClass,
                $this->fullResourceClass
            ],

            self::DELETE => [
                DeleteFeature::class,
                $this->getModelJobClass,
                $this->deleteModelJobClass
            ],

            self::MOVE => [
                MoveFeature::class,
                $this->getModelJobClass,
                $this->repository,
            ],

            self::STORE => [
                StoreFeature::class,
                $this->createRequestClass,
                $this->createModelJobClass,
                $this->fullResourceClass
            ],

            self::UPDATE => [
                UpdateFeature::class,
                $this->getModelJobClass,
                $this->updateRequestClass,
                $this->updateModelJobClass,
                $this->fullResourceClass
            ],
        ];
    }

    protected function index()
    {
        return $this->serve(self::INDEX);
    }

    protected function store()
    {
        return $this->serve(self::STORE);
    }

    public function view($id)
    {
        return $this->serve(self::VIEW, ['id' => $id]);
    }

    protected function update($id)
    {
        return $this->serve(self::UPDATE, ['id' => $id]);
    }

    public function delete($id)
    {
        return $this->serve(self::DELETE, ['id' => $id]);
    }

    public function move($id, $direction)
    {
        return $this->serve(self::MOVE, ['id' => $id, 'direction' => $direction]);
    }


}
