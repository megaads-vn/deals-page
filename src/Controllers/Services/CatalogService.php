<?php

namespace Megaads\DealsPage\Controllers\Services;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Megaads\DealsPage\Jobs\CatalogJob;
use Megaads\DealsPage\Models\Catalog;
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
    public function bulkCreate() {
        $response = $this->getDefaultStatus();
        $totalInserted = $this->recursiveCreateCatalog(1);
        if ($totalInserted > 0) {
            $response = $this->getSuccessStatus();
            $response['message'] = 'Inserted ' . $totalInserted;
        }
        return Response::json($response);
    }

    protected function recursiveCreateCatalog($pageId, $total = 0)
    {
        if ($pageId == -1) {
            return $total;
        }
        $result = $this->apiRequestRepository->readCatalogs($pageId);
        if (!empty($result)) {
            $insertArray = [];
            foreach ($result as $item) {
                $exists = Catalog::where('cid', $item['cid'])->first();
                if (empty($exists)) {
                    $insertArray[] = $this->buildInsertCatalogs($item);
                }
            }
            $this->catalogRepository->bulkCreate($insertArray);
            $pageId += 1;
            $total += count($insertArray);
            return $this->recursiveCreateCatalog($pageId, $total);
        } else {
            $pageId = -1;
            return $total;
        }
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