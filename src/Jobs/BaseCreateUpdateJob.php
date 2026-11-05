<?php

namespace OZiTAG\Tager\Backend\Crud\Jobs;

use Illuminate\Database\Eloquent\Model;
use Ozerich\FileStorage\Storage;
use OZiTAG\Tager\Backend\Core\Http\FormRequest;
use OZiTAG\Tager\Backend\Core\Jobs\Job;
use OZiTAG\Tager\Backend\Core\Repositories\EloquentRepository;

abstract class BaseCreateUpdateJob extends Job
{
    /**
     * Process-wide fallback configuration.
     *
     * Kept for backwards compatibility with modules that call setConfig()
     * directly. First-party callers pass the configuration per instance via
     * the constructor instead, so the static value does not leak between
     * controllers inside a long-lived worker.
     */
    protected static $config = [];

    protected ?array $instanceConfig = null;

    public static function setConfig($config)
    {
        static::$config = $config;
    }

    /**
     * Configuration for this run: the per-instance value when one was passed,
     * otherwise the static fallback.
     */
    protected function config(): array
    {
        return $this->instanceConfig ?? static::$config;
    }

    /**
     * @return EloquentRepository
     */
    protected function repository()
    {
        $config = $this->config();

        if (isset($config['repository'])) {
            return $config['repository'];
        } else {
            throw new \Exception('Repository not found');
        }
    }

    /**
     * @return array
     */
    protected function fields()
    {
        $config = $this->config();

        if (isset($config['fields'])) {
            $fields = $config['fields'];

            $result = [];
            foreach ($fields as $field => $requestField) {
                if (is_numeric($field)) {
                    $field = $requestField;
                }
                $result[$field] = $requestField;
            }
            return $result;
        } else {
            return [];
        }
    }

    /**
     * @var FormRequest
     */
    protected $request;

    /**
     * @var Model
     */
    protected $model;

    public function __construct(FormRequest $request, $model = null, ?array $config = null)
    {
        $this->request = $request;

        $this->model = $model;

        $this->instanceConfig = $config;
    }
}
