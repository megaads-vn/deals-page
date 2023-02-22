<?php

namespace Megaads\DealsPage\Controllers\Services;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;

class BaseService extends BaseController
{
    use DispatchesJobs;

    protected function getDefaultStatus() {
        return ['status' => 'fail'];
    }

    protected function getSuccessStatus($data = []) {
        $retVal = [
            'status' => 'successful'
        ];
        if (!empty($data)) {
            $retVal["result"] = $data;
        }
        return $retVal;
    }
}