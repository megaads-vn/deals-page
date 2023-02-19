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
                if (!empty($reqResult)) {
                    $bulkInsertData = [];
                    foreach ($reqResult as $item) {
                        $bulkInsertData[] = $this->buildInsertDealItem($item);
                    }
                    $result = $this->dealRepository->bulkInsert($bulkInsertData);
                    if ($result) {
                        $response = $this->getSuccessStatus();
                    }
                }
            } else {
                $response['message'] = "Some params has required!";
            }

        } catch (\Exception $exception) {
            $response['message'] = $exception->getMessage();
            dealPageSysLog('error', 'BULK_CREATE_DEAL: ', $exception);
        }
        return \Response::json($response);
    }

    protected function buildInsertDealItem($rawData) {
        $retVal = [
            "title" => $rawData["name"],
            "slug" => slugify($rawData["name"]),
            "crawl_id" => $rawData["pid"],
            "search_slug" => slugify($rawData["name"]),
            "crawl_id" => $rawData["pid"],
            "image" => $rawData["imageUrl"],
            "affiliate_link" => $rawData["linkUrl"],
            "origin_link" => $rawData["deepLinkURL"],
            "content" => $rawData["description"],
            "meta_title" => $rawData["name"],
            "meta_description" => $rawData["shortDescription"],
            "meta_keywords" => $rawData["keywords"],
            "currency" => $rawData["priceCurrency"],
            "country" => $rawData["advertiserCountry"],
            "advertiser" => $rawData["advertiserName"],
            "advertiser_id" => $rawData["aid"],
            "catalogs_name" => $rawData["catalogName"],
            "catalogs_id" => $rawData["cid"],
            "crawl_id" => $rawData["pid"],
            "mpn" => $rawData["mpn"],
            "sku" => $rawData["sku"],
            "price" => $rawData["price"],
            "sale_price" => $rawData["salePrice"],
            "final_price" => $rawData["finalPrice"],
            "discount" => $rawData["discount"],
            "in_stock" => $rawData["isInstock"],
            "manufacturer" => $rawData["manufacturer"],
        ];

        return $retVal;
    }
}