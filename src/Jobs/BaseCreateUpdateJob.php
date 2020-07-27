<?php

namespace OZiTAG\Tager\Backend\Crud\Jobs;

use Illuminate\Database\Eloquent\Model;
use Ozerich\FileStorage\Storage;
use OZiTAG\Tager\Backend\Core\Http\FormRequest;
use OZiTAG\Tager\Backend\Core\Jobs\Job;
use OZiTAG\Tager\Backend\Core\Repositories\EloquentRepository;

abstract class BaseCreateUpdateJob extends Job
{
    abstract function process();

    protected static $config = [];

    public static function setConfig($config)
    {
        static::$config = $config;
    }

    /**
     * @return EloquentRepository
     */
    protected function repository()
    {
        if (isset(static::$config['repository'])) {
            return static::$config['repository'];
        } else {
            throw new \Exception('Repository not found');
        }
    }

    /**
     * @return array
     */
    protected function fields()
    {
        if (isset(static::$config['fields'])) {
            $fields = static::$config['fields'];

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
     * @return array
     */
    protected function fileScenarios()
    {
        if (isset(static::$config['fileScenarios'])) {
            return static::$config['fileScenarios'];
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

    /**
     * @var Storage
     */
    protected $fileStorage;

    public function __construct(FormRequest $request, $model = null)
    {
        $this->request = $request;

        $this->model = $model;
    }
}
