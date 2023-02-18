<?php

namespace Megaads\DealsPage\Controllers\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Megaads\DealsPage\Repositories\DealRepository;
use Megaads\DealsPage\Repositories\ApiRequestRepository;

class DealService extends BaseService
{
    protected $apiRequestRepository;
    protected $dealRepository;

    public function __construct()
    {
        $this->apiRequestRepository = new ApiRequestRepository();
        $this->dealRepository = new DealRepository();
    }

    public function find()
    {

    }

    public function bulkCreate(Request $request) {
        $response = $this->getDefaultStatus();
        try {
            $rules = [
                'catalog_id' => 'required'
            ];
            $validtor = \Validator::make($request->all(), $rules);
            if (!$validtor->fails()) {
                $catalogId = $request->get('catalog_id');
                $reqResult = $this->apiRequestRepository->readCatalogProducts($catalogId);
                echo "<pre>";
                print_r($reqResult);
                echo "</pre>";
                die;
            } else {
                $response['message'] = "Some params has required!";
            }

        } catch (\Exception $exception) {
            $response['message'] = $exception->getMessage();
            dealPageSysLog('error', 'BULK_CREATE_DEAL: ', $exception);
        }
        return \Response::json($response);
    }
}