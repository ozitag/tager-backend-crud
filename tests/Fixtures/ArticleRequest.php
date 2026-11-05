<?php

namespace OZiTAG\Tager\Backend\Crud\Tests\Fixtures;

use OZiTAG\Tager\Backend\Crud\Requests\CrudFormRequest;

class ArticleRequest extends CrudFormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:100',
            'body' => 'nullable|string',
            'isPublished' => 'nullable|boolean',
        ];
    }
}
