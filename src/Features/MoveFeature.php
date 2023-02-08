<?php

namespace OZiTAG\Tager\Backend\Crud\Features;

use Illuminate\Contracts\Database\Eloquent\Builder as BuilderContract;
use OZiTAG\Tager\Backend\Core\Features\ModelFeature;
use OZiTAG\Tager\Backend\Core\Repositories\EloquentRepository;
use OZiTAG\Tager\Backend\Core\Resources\SuccessResource;
use OZiTAG\Tager\Backend\Crud\Contracts\IModelPriorityConditional;
use OZiTAG\Tager\Backend\Crud\Contracts\IRepositoryWithPriorityMethods;
use OZiTAG\Tager\Backend\HttpCache\HttpCache;

class MoveFeature extends ModelFeature
{
    private $direction;

    protected ?EloquentRepository $repository;

    private $cacheNamespace;

    private $eventClass;

    public function __construct($id, $direction, $jobGetByIdClass, ?EloquentRepository $repository, $cacheNamespace, ?string $eventClass, ?BuilderContract $builder)
    {
        parent::__construct($id, $jobGetByIdClass, $repository, $builder);

        $this->direction = $direction;
        $this->repository = $repository;
        $this->cacheNamespace = $cacheNamespace;
        $this->eventClass = $eventClass;
    }

    public function handle(HttpCache $httpCache)
    {
        $model = $this->model();

        if (method_exists($model, 'up') && method_exists($model, 'down')) {
            if ($this->direction == 'up') {
                $model->up();
            } else {
                $model->down();
            }
        } else {

            if ($this->repository instanceof IRepositoryWithPriorityMethods == false) {
                throw new \Exception('Repository must implements IRepositoryWithPriorityMethods interface');
            }

            $conditionalAttributes = [];
            if ($model instanceof IModelPriorityConditional) {
                $conditionalAttributes = $model->getPriorityConditionalAttributes();
            }

            $conditionalAttributesWithFields = [];
            foreach ($conditionalAttributes as $field) {
                $conditionalAttributesWithFields[] = [
                    'field' => $field,
                    'operator' => '=',
                    'operand' => $model->{$field}
                ];
            }

            if ($this->direction == 'up') {
                $other = $this->repository->findFirstWithLowerPriorityThan($model->priority, $conditionalAttributesWithFields);
            } else {
                $other = $this->repository->findFirstWithHigherPriorityThan($model->priority, $conditionalAttributesWithFields);
            }

            if ($other) {
                $a = $other->priority;
                $other->priority = $model->priority;
                $model->priority = $a;

                $model->save();
                $other->save();
            }
        }

        if ($this->cacheNamespace) {
            $httpCache->clear($this->cacheNamespace);
        }

        if($this->eventClass){
            $eventClass = $this->eventClass;
            event(new $eventClass);
        }

        return new SuccessResource();
    }
}
