<?php

namespace OZiTAG\Tager\Backend\Crud\Requests;

use Ozerich\FileStorage\Rules\FileRule;
use OZiTAG\Tager\Backend\Core\Http\FormRequest;

class CrudFormWithSeoRequest extends CrudFormRequest
{
    public function fileScenarios()
    {
        return [
            'openGraphImage' => 'open-graph'
        ];
    }

    public function rules()
    {
        return [
            'pageTitle' => 'nullable|string',
            'pageDescription' => 'nullable|string',
            'openGraphImage' => ['nullable', new FileRule()],
        ];
    }
}
