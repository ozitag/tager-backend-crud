<?php

namespace OZiTAG\Tager\Backend\Crud\Events;

use App\Models\Product;
use App\Repositories\ProductRepository;
use Illuminate\Support\Facades\App;

class UpdateModelEvent
{
    protected int $id;

    protected ?array $oldAttributes;

    protected array $newAttributes;

    public function __construct(int $id, ?array $oldAttributes = null, array $newAttributes = [])
    {
        $this->id = $id;

        $this->oldAttributes = $oldAttributes;

        $this->newAttributes = $newAttributes;
    }

    public function getOldAttributes(): ?array
    {
        return $this->oldAttributes;
    }

    public function getNewAttributes(): array
    {
        return $this->newAttributes;
    }

    public function getModelId(): int
    {
        return $this->id;
    }
}
