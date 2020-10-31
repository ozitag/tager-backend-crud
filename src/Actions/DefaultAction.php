<?php

namespace OZiTAG\Tager\Backend\Crud\Actions;
use OZiTAG\Tager\Backend\Crud\Contracts\IAction;

class DefaultAction implements IAction
{

    public function __construct() {}

    /**
     * @param string $key
     * @return bool
     */
    public function get(string $key) {
        return property_exists($this, $key) ? $this->$key : false;
    }
}
