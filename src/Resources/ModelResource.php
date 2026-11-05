<?php

namespace OZiTAG\Tager\Backend\Crud\Resources;

use \OZiTAG\Tager\Backend\Core\Resources\ModelResource as CoreModelResource;

class ModelResource extends CoreModelResource
{
    /**
     * Process-wide fallback field map.
     *
     * Kept for backwards compatibility with callers that still use
     * setFields(). First-party features pass the field map per instance via
     * the constructor, so one controller's field map no longer bleeds into
     * another's inside a long-lived worker (Octane, queue workers) or a single
     * request that serves two CRUD controllers.
     */
    private static $fields = [];

    private ?array $instanceFields = null;

    public function __construct($resource, ?array $fields = null)
    {
        parent::__construct($resource);

        $this->instanceFields = $fields;
    }

    public static function setFields($fields)
    {
        self::$fields = $fields;
    }

    protected function fields()
    {
        return $this->instanceFields ?? self::$fields;
    }
}
