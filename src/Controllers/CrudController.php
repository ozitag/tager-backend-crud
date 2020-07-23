<?php

namespace OZiTAG\Tager\Backend\Crud\Controllers;

use Illuminate\Support\Collection;

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

        $feature = null;

        if (isset($features[$index])) {
            $feature = $features[$index];
        } else if (isset($features[$key])) {
            $feature = $features[$key];
        }

        if (!$feature) {
            throw new \Exception('Feature not found');
        }

        if (is_string($feature)) {
            return $this->serve($feature, $params);
        } else if (is_array($feature)) {
            $featureName = array_shift($feature);

            $reflection = new \ReflectionClass($featureName);
            $constructorParams = $reflection->getConstructor()->getParameters();

            $featureParams = $params;
            foreach ($constructorParams as $ind => $param) {
                if ($ind < count($params)) continue;
                $featureParams[$param->getName()] = $feature[$ind - count($params)];
            }

            return $this->serve($featureName, $featureParams);
        } else {
            return $this->dispatchNow($feature);
        }
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
