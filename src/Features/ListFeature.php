<?php

namespace OZiTAG\Tager\Backend\Crud\Features;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use OZiTAG\Tager\Backend\Core\Features\Feature;
use OZiTAG\Tager\Backend\Core\Repositories\EloquentRepository;
use OZiTAG\Tager\Backend\Core\Resources\ResourceCollection;
use OZiTAG\Tager\Backend\Crud\Actions\IndexAction;
use OZiTAG\Tager\Backend\Crud\Jobs\GetModelResourceFieldsJob;
use OZiTAG\Tager\Backend\Crud\Resources\ModelResource;
use OZiTAG\Tager\Backend\Files\Enums\TagerFileThumbnail;

class ListFeature extends Feature
{
    private $repository;

    private $resourceClassName;

    private $resourceFields;

    private IndexAction $action;

    protected $hasPagination = false;

    protected $hasQuery = false;

    protected $isAdmin = false;

    public function __construct(
        EloquentRepository $repository, $resourceClassName, $resourceFields, IndexAction $action,
        bool $isAdmin
    )
    {
        $this->repository = $repository;

        $this->resourceClassName = $resourceClassName;

        $this->resourceFields = $resourceFields;

        $this->action = $action;

        $this->isAdmin = $isAdmin;

        $this->hasPagination = $this->action->get('hasPagination');
        $this->hasQuery = $this->action->get('hasSearchByQuery');
    }

    public function handle(Request $request)
    {
        if ($this->hasPagination) {
            $this->registerPaginationRequest();
        }

        if ($this->hasQuery) {
            $this->registerQueryRequest();
        }

        $query = $this->hasQuery ? $request->get('query') : null;

        $getIndexActionBuilderJobClass = $this->action->getIndexActionBuilderJobClass();
        if ($getIndexActionBuilderJobClass) {
            $builder = $this->run($getIndexActionBuilderJobClass, [
                'request' => $request
            ]);

            if ($this->hasQuery) {
                $builder = $this->repository->searchByQuery($query, $builder);
            }

            if (!$builder) {
                $items = new Collection();
            } else if (!$this->hasPagination) {
                $items = $builder->get();
            } else {
                $items = $this->repository->paginate($builder);
            }
        } else {
            $items = $this->action->get('isTree')
                ? $this->repository->toFlatTree($this->hasPagination, $query)
                : $this->repository->get($this->hasPagination, $query);
        }

        if (!$this->resourceClassName) {
            $resourceFields = $this->run(GetModelResourceFieldsJob::class, [
                'resourceFields' => $this->resourceFields,
                'isAdmin' => $this->isAdmin,
            ]);

            ModelResource::setFields($resourceFields);
        }

        $items->transform(function ($item) {
            $class = $this->resourceClassName ?? ModelResource::class;
            return (new $class($item));
        });

        return new ResourceCollection($items);
    }
}
