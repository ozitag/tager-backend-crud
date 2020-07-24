<?php

namespace OZiTAG\Tager\Backend\Crud\Jobs;

use App\Enums\FileScenario;
use App\Http\Requests\Admin\PartnerRequest;
use App\Models\Partner;
use OZiTAG\Tager\Backend\Core\Jobs\Job;

class StoreJob extends BaseCreateUpdateJob
{
    public function process()
    {
        $data = [];
        foreach ($this->fields() as $field => $requestField) {
            $data[$field] = $this->request->{$requestField};
        }

        $maxPriorityItem = $this->repository()->findItemWithMaxPriority();
        $data['priority'] = $maxPriorityItem ? $maxPriorityItem->priority + 1 : 1;

        return $this->repository()->fillAndSave($data);
    }
}
