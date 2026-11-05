<?php

namespace OZiTAG\Tager\Backend\Crud\Tests\Fixtures;

use OZiTAG\Tager\Backend\Core\Models\TModel;

class Article extends TModel
{
    protected $table = 'articles';

    protected $guarded = [];

    protected $casts = [
        'is_published' => 'boolean',
    ];
}
