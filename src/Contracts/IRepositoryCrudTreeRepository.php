<?php

namespace OZiTAG\Tager\Backend\Crud\Contracts;

use Illuminate\Contracts\Database\Eloquent\Builder as BuilderContract;

interface IRepositoryCrudTreeRepository
{
    public function toFlatTree(BuilderContract $builder, bool $paginate = false, ?string $query = null, ?array $filter = []);
}
