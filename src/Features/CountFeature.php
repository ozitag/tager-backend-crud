<?php

namespace OZiTAG\Tager\Backend\Crud\Features;

use Illuminate\Http\Resources\Json\JsonResource;
use OZiTAG\Tager\Backend\Core\Features\Feature;
use OZiTAG\Tager\Backend\Core\Repositories\EloquentRepository;
use OZiTAG\Tager\Backend\Crud\Contracts\IRepositoryCrudTree;
use OZiTAG\Tager\Backend\Crud\Resources\ModelResource;

class CountFeature extends Feature
{
    private $repository;

    public function __construct(EloquentRepository $repository)
    {
        $this->repository = $repository;
    }

    public function handle()
    {
        return new JsonResource([
            'count' => (int)$this->repository->count()
        ]);
    }
}
