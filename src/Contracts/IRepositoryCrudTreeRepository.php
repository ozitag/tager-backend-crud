<?php

namespace OZiTAG\Tager\Backend\Crud\Contracts;

interface IRepositoryCrudTreeRepository
{
    public function toFlatTree($paginate = false, ?string $query = null);
}
