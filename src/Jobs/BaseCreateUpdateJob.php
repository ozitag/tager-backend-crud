<?php

namespace OZiTAG\Tager\Backend\Crud\Jobs;

use App\Enums\FileScenario;
use App\Http\Requests\Admin\PartnerRequest;
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
        self::$config = $config;
    }

    /**
     * @return EloquentRepository
     */
    protected function repository()
    {
        if (isset(self::$config['repository'])) {
            return self::$config['repository'];
        } else {
            throw new \Exception('Repository not found');
        }
    }

    /**
     * @return array
     */
    protected function fields()
    {
        if (isset(self::$config['fields'])) {
            $fields = self::$config['fields'];

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
        if (isset(self::$config['fileScenarios'])) {
            return self::$config['fileScenarios'];
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

    public function handle(Storage $fileStorage)
    {
        foreach ($this->fileScenarios() as $fileField => $fileScenario) {
            if ($this->request->{$fileField}) {
                $fileStorage->setFileScenario($this->request->{$fileField}, $fileScenario);
            }
        }

        return $this->process();
    }
}
