<?php

namespace OZiTAG\Tager\Backend\Crud\Contracts;

interface IRepositoryCrudTreeRepository
{
    public function toFlatTree(bool $paginate = false, ?string $query = null);
}
