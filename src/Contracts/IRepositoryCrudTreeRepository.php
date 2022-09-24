<?php

namespace OZiTAG\Tager\Backend\Crud\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface IRepositoryCrudTreeRepository
{
    public function toFlatTree(Builder $builder, bool $paginate = false, ?string $query = null, ?array $filter = []);
}
