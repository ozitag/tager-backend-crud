<?php

namespace OZiTAG\Tager\Backend\Crud\Features;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\JsonResource;
use OZiTAG\Tager\Backend\Core\Features\Feature;
use OZiTAG\Tager\Backend\Core\Repositories\EloquentRepository;
use Symfony\Component\HttpFoundation\Request;

class CountFeature extends Feature
{
    protected EloquentRepository $repository;

    protected mixed $queryBuilder;

    public function __construct(EloquentRepository $repository, mixed $queryBuilder = null)
    {
        $this->repository = $repository;

        $this->queryBuilder = $queryBuilder;
    }

    private function getQueryBuilder(Request $request): ?Builder
    {
        if (is_string($this->queryBuilder)) {
            return dispatch_sync(new $this->queryBuilder());
        } else if (is_callable($this->queryBuilder)) {
            return call_user_func($this->queryBuilder, $request);
        } else if ($this->queryBuilder instanceof Builder) {
            return $this->queryBuilder;
        } else {
            return null;
        }
    }

    public function handle(Request $request)
    {
        if (is_string($this->queryBuilder)) {
            $builder = dispatch_sync(new $this->queryBuilder());
        } else if (is_callable($this->queryBuilder)) {
            $builder = call_user_func($this->queryBuilder, $request);
        } else if ($this->queryBuilder instanceof Builder) {
            $builder = $this->queryBuilder;
        } else {
            $builder = $this->repository->builder();
        }

        return new JsonResource([
            'count' => (int)$builder->count()
        ]);
    }
}
