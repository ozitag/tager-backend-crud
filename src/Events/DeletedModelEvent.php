<?php

namespace OZiTAG\Tager\Backend\Crud\Events;

use App\Models\Product;
use App\Repositories\ProductRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

class DeletedModelEvent
{
    protected array $modelAttributes;

    public function __construct(array $modelAttributes)
    {
        $this->modelAttributes = $modelAttributes;
    }

    public function getModelAttributes(): array
    {
        return $this->modelAttributes;
    }
}
