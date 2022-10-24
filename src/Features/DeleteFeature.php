<?php

namespace OZiTAG\Tager\Backend\Crud\Features;

use Illuminate\Database\Eloquent\Builder;
use OZiTAG\Tager\Backend\Core\Features\ModelFeature;
use OZiTAG\Tager\Backend\Core\Resources\FailureResource;
use OZiTAG\Tager\Backend\Core\Resources\SuccessResource;
use OZiTAG\Tager\Backend\HttpCache\HttpCache;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DeleteFeature extends ModelFeature
{
    public function __construct($id, $jobGetByIdClass, $repository,
                                protected mixed $validator,
                                protected ?string $jobDeleteClass,
                                protected ?string $cacheNamespace,
                                protected ?string $eventName,
                                ?Builder $builder = null
    )
    {
        parent::__construct($id, $jobGetByIdClass, $repository,$builder);

    }

    private function validate()
    {
        if (!$this->validator) return;

        if (is_string($this->validator)) {
            $validateResult = $this->run($this->validator, ['model' => $this->model()]);
        } else if (is_callable($this->validator)) {
            $validateResult = call_user_func($this->validator, $this->model());
        } else {
            throw new \Exception('Validator should be job class or callable');
        }

        if ($validateResult !== true) {
            $error = is_string($validateResult) ? $validateResult : 'Model can\'t be deleted';
            throw new AccessDeniedHttpException($error);
        }
    }

    public function handle(HttpCache $httpCache)
    {
        $this->validate();

        if ($this->jobDeleteClass) {
            $this->run($this->jobDeleteClass, ['model' => $this->model()]);
        } else if ($this->repository) {
            $model = $this->repository->find($this->id);
            if ($model) {
                $model->delete();
            } else {
                throw new NotFoundHttpException('Model not found');
            }
        } else {
            throw new \Exception('JobDeleteClass or Repository must be set');
        }

        if ($this->cacheNamespace) {
            $httpCache->clear($this->cacheNamespace);
        }

        if ($this->eventName) {
            $eventClass = $this->eventName;
            event(new $eventClass($model->getAttributes()));
        }

        return new SuccessResource();
    }
}
