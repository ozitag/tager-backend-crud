<?php

namespace OZiTAG\Tager\Backend\Crud\Features;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\JsonResource;
use OZiTAG\Tager\Backend\Core\Features\Feature;
use OZiTAG\Tager\Backend\Core\Repositories\EloquentRepository;

class CountFeature extends Feature
{
    protected EloquentRepository $repository;

    protected ?string $getBuilderJobClass;

    public function __construct(EloquentRepository $repository, ?string $getBuilderJobClass = null)
    {
        $this->repository = $repository;

        $this->getBuilderJobClass = $getBuilderJobClass;
    }

    public function handle()
    {
        $builder = $this->repository;

        if (!empty($this->getBuilderJobClass)) {
            $builder = $this->run($this->getBuilderJobClass);
        }

        return new JsonResource([
            'count' => (int)$builder->count()
        ]);
    }
}
