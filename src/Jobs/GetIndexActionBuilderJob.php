<?php

namespace OZiTAG\Tager\Backend\Crud\Jobs;

use Illuminate\Http\Request;
use OZiTAG\Tager\Backend\Core\Jobs\Job;

abstract class GetIndexActionBuilderJob extends Job
{
    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }
}
