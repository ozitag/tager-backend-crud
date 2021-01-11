<?php

namespace OZiTAG\Tager\Backend\Crud\Events;

use App\Models\Product;
use App\Repositories\ProductRepository;
use Illuminate\Support\Facades\App;

class UpdateModelEvent
{
    protected int $id;

    protected array $oldAttributes;

    public function __construct(int $id, array $oldAttributes = [])
    {
        $this->id = $id;

        $this->oldAttributes = $oldAttributes;
    }

    public function getOldAttributes(): array
    {
        return $this->oldAttributes;
    }

    public function getModelId(): int
    {
        return $this->id;
    }
}
