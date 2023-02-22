<?php

namespace Megaads\DealsPage\Controllers\Services;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Megaads\DealsPage\Jobs\CatalogJob;
use Megaads\DealsPage\Repositories\ApiRequestRepository;
use Megaads\DealsPage\Repositories\CatalogRepository;

class CatalogService extends BaseService
{
    protected $apiRequestRepository;
    protected $catalogRepository;


    public function __construct()
    {
        $this->apiRequestRepository = new ApiRequestRepository();
        $this->catalogRepository = new CatalogRepository();
    }

    public function find(Request $request) {
        $response = $this->getDefaultStatus();
        $filters = $request->all();
        $result = $this->catalogRepository->read($filters);
        if (!empty($result)) {
            $response = $this->getSuccessStatus($result);
        }
        return Response::json($response);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkCreate(Request $request) {
        $response = $this->getDefaultStatus();
        $pageId = $request->get('pageId', 1);
        $result = $this->apiRequestRepository->readCatalogs($pageId);
        \Log::info('CATALOG_BULK: PAGE[' . $pageId . ']:: COUNT[' . count($result)  . ']');
        if (!empty($result)) {
            $insertArray = [];
            foreach ($result as $item) {
                $insertArray[] = $this->buildInsertCatalogs($item);
            }
            $insertRs = $this->catalogRepository->bulkCreate($insertArray);
            if ($insertRs) {
                $pageId += 1;
                $runAt = Carbon::now()->addMinutes(1);
                $job = (new CatalogJob($pageId))->delay($runAt);
                $this->dispatch($job);
                $response = $this->getSuccessStatus();
            }
        }
        return Response::json($response);
    }

    /**
     * @param $rawItem
     * @return array
     */
    protected function buildInsertCatalogs($rawItem)
    {
        $retVal = [];
        $retVal['cid'] = $rawItem['cid'];
        $retVal['name'] = $rawItem['name'];
        $retVal['slug'] = slugify($rawItem['name']);
        $retVal['url'] = $rawItem['url'];
        $retVal['advertiser'] = $rawItem['advertiser'];
        $retVal['country'] = $rawItem['country'];
        $retVal['currency'] = $rawItem['currency'];
        $retVal['create_time'] = new \DateTime();
        return $retVal;
    }
}