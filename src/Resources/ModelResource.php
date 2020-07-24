<?php

namespace OZiTAG\Tager\Backend\Crud\Resources;

use \OZiTAG\Tager\Backend\Core\Resources\ModelResource as CoreModelResource;

class ModelResource extends CoreModelResource
{
    private static $fields = [];

    public static function setFields($fields)
    {
        self::$fields = $fields;
    }

    protected function fields()
    {
        return self::$fields;
    }
}
