<?php

namespace OZiTAG\Tager\Backend\Crud\Controllers;

use OZiTAG\Tager\Backend\Core\Controllers\Controller;

class CrudController extends Controller
{
    const INDEX = 'index';
    const STORE = 'store';
    const VIEW = 'view';
    const UPDATE = 'update';
    const DELETE = 'delete';
    const MOVE = 'move';

    protected function index()
    {
        return $this->serve(self::INDEX);
    }

    protected function store()
    {
        return $this->serve(self::STORE);
    }

    public function view($id)
    {
        return $this->serve(self::VIEW, ['id' => $id]);
    }

    protected function update($id)
    {
        return $this->serve(self::UPDATE, ['id' => $id]);
    }

    public function delete($id)
    {
        return $this->serve(self::DELETE, ['id' => $id]);
    }

    public function move($id, $direction)
    {
        return $this->serve(self::MOVE, ['id' => $id, 'direction' => $direction]);
    }
}
