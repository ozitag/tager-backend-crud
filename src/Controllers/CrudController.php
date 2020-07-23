<?php

namespace OZiTAG\Tager\Backend\Crud\Controllers;

use OZiTAG\Tager\Backend\Core\Controllers\Controller;

abstract class CrudController extends Controller
{
    const INDEX = 'index';
    const STORE = 'store';
    const VIEW = 'view';
    const UPDATE = 'update';
    const DELETE = 'delete';
    const MOVE = 'move';

    abstract function features();

    private function serveFeature($index, $key, $params = [])
    {
        $features = $this->features();

        if (isset($features[$index])) {
            return $this->serve($features[$index], $params);
        } else if (isset($features[$key])) {
            return $this->serve($features[$key], $params);
        }

        throw new \Exception('Feature not found');
    }

    protected function index()
    {
        return $this->serveFeature(0, self::INDEX);
    }

    protected function store()
    {
        return $this->serveFeature(1, self::STORE);
    }

    public function view($id)
    {
        return $this->serveFeature(2, self::VIEW, ['id' => $id]);
    }

    protected function update($id)
    {
        return $this->serveFeature(3, self::UPDATE, ['id' => $id]);
    }

    public function delete($id)
    {
        return $this->serveFeature(4, self::DELETE, ['id' => $id]);
    }

    public function move($id, $direction)
    {
        return $this->serveFeature(5, self::MOVE, ['id' => $id, 'direction' => $direction]);
    }
}
