<?php

namespace Megaads\DealsPage\Controllers\Services;

use Carbon\Carbon;
use Google\Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Megaads\DealsPage\Jobs\DealProductJob;
use Megaads\DealsPage\Models\Category;
use Megaads\DealsPage\Models\Deal;
use Megaads\DealsPage\Models\DealCategory;
use Megaads\DealsPage\Models\DealRelation;
use Megaads\DealsPage\Models\Store;
use Megaads\DealsPage\Repositories\CatalogRepository;
use Megaads\DealsPage\Repositories\DealRepository;
use Megaads\DealsPage\Repositories\ApiRequestRepository;

class DealService extends BaseService
{
    protected $apiRequestRepository;
    protected $dealRepository;
    protected $catalogRepository;
    protected $dealCategoryIds = NULL;

    public function __construct()
    {
        $this->apiRequestRepository = new ApiRequestRepository();
        $this->dealRepository = new DealRepository();
        $this->catalogRepository = new CatalogRepository();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function find(Request $request)
    {
        $response = $this->getDefaultStatus();
        try {
            $filters = $request->all();
            $result = $this->dealRepository->read($filters);
            if (!empty($result["data"])) {
                $filters['metrics'] = 'count';
                $totalResult = $this->dealRepository->read($filters);
                $result = array_merge(["recordsCount" => $totalResult['data']], $result);
                $response = $this->getSuccessStatus($result);
            }
        } catch (Exception $ex) {
            $response['message'] = 'Fail! Has some error. ';
            dealPageSysLog('error', 'DEAL_FIND: ', $ex);
        }

        return Response::json($response);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request) {
        $response = $this->getDefaultStatus();
        try {
            $params = $request->all();
            $buildParams = $this->buildData($params);
            $createResult = $this->dealRepository->create($buildParams);
            if (!empty($createResult)) {
                $newDealId = $createResult;
                $findDeal = $this->dealRepository->read(["id" => $newDealId, "metrics" => "first"]);
                $response = $this->getSuccessStatus(["data" => $findDeal["data"]]);
                if (!empty($this->dealCategoryIds)) {
                    $this->dealRepository->updateDealCategory($newDealId, $this->dealCategoryIds);
                }
            }
        } catch (\Exception $exception) {
            $response['message'] = 'Fail! Has some error';
            dealPageSysLog('error', 'CREATE_DEAL: ', $exception);
        }
        return Response::json($response);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request) {
        $response = $this->getDefaultStatus();
        try {
            $params = $request->all();
            $updateParams = $this->buildData($params);
            $id = isset($updateParams['id']) ? $updateParams['id'] : NULL;
            if (!empty($id)) {
                $this->dealRepository->update($id, $updateParams);
                $findDeal = $this->dealRepository->read(["id" => $id, "metrics" => "first"]);
                $response = $this->getSuccessStatus(["data" => $findDeal["data"]]);
                if (!empty($this->dealCategoryIds)) {
                    $this->dealRepository->updateDealCategory($id, $this->dealCategoryIds);
                }
            }
        } catch (\Exception $exception) {
            dealPageSysLog('error', 'UPDATE_DEAL', $exception);
        }
        return Response::json($response);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request) {
        $response = $this->getDefaultStatus();
        try {
            if ($request->has('id')) {
                $deleteRes = $this->dealRepository->delete($request->get('id'));
                if ($deleteRes) {
                    $response = $this->getSuccessStatus();
                } else {
                    $response['message'] = 'Fail! Has some error or Deal not found.';
                }
            }

        } catch (\Exception $exception) {
            $response['message'] = 'Fail! Has some error';
            dealPageSysLog('error', 'DELETE_DEAL: ', $exception);
        }

        return Response::json($response);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function dealMigration(Request $request) {
        $resposne = $this->getDefaultStatus();
        try {
            $sourceTable = $request->get('source', NULL);
            if (!empty($sourceTable)) {
                $oldData = \DB::table($sourceTable)->get();
                if (!empty($oldData)) {
                    $countInsert = 0;
                    foreach ($oldData as $oldItem) {
                        $saleOff = 0;
                        if ($oldItem->sale_price > 0 && $oldItem->sale_price < $oldItem->regular_price) {
                            $saleOff = floor((($oldItem->regular_price - $oldItem->sale_price) / $oldItem->regular_price) * 100);
                        }
                        $insertNewItems = [
                            "title" => $oldItem->title,
                            "slug" => $oldItem->slug,
                            "search_slug" => $oldItem->slug,
                            "content" => $oldItem->description,
                            "meta_description" => $oldItem->description,
                            "type" => "DEAL",
                            "image" => $oldItem->image_url,
                            "expired_time" => $oldItem->expired_time,
                            "price" => $oldItem->regular_price,
                            "sale_price" => $oldItem->sale_price,
                            "currency" => "USD",
                            "affiliate_link" => $oldItem->url,
                            "store_id" => $oldItem->store_id,
                            "category_id" => $oldItem->category_id,
                            "discount" => $saleOff
                        ];
                        $insertId = $this->dealRepository->create($insertNewItems);
                        if (!empty($insertId)) {
                            $countInsert++;
                            DealRelation::insert(["object_id" => $insertId, "target_id" => $oldItem->keypage_id]);
                        }
                    }
                    $resposne = $this->getSuccessStatus(["data" => $countInsert]);
                }
            } else {
                $resposne['message'] = 'Fail! ';
            }
        } catch (\Exception $exception) {
            $resposne['message'] = 'Fail! Has some error when migrate data';
            dealPageSysLog('error', 'MIGRATE_DEAL: ', $exception);
        }
        return Response::json($resposne);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkCreate(Request $request) {
        $response = $this->getDefaultStatus();
        try {
            if ($request->has('params')) {
                $params = $request->get('params');
                \Log::info('BULK_CREATE_DEAL');
                $insertResult = [];
                foreach ($params as $item) {
                    $findExists = Deal::query()->where('slug', $item->slug)->orWhere('origin_link', $item->origin_link)->first(['id']);
                    if (!empty($findExists)) {
                        $resultId = $this->dealRepository->create($item);
                        $insertResult[$resultId] = $item['category_id'];
                    }
                }
                if (count($insertResult) > 0) {
                    foreach ($insertResult as $dealId => $strCategoryId) {
                        $categoryIds = explode(',', $strCategoryId);
                        $bulkCateInsert = [];
                        foreach ($categoryIds as $cId) {
                            $bulkCateInsert = [
                                'deal_id' => $dealId,
                                'category_id' => trim($cId)
                            ];
                        }
                        if (count($bulkCateInsert) > 0) {
                            DealCategory::insert($bulkCateInsert);
                        }
                    }
                }
                $response = $this->getSuccessStatus();
            }
        } catch (\Exception $exception) {
            $response['message'] = 'Fail! ' . $exception->getMessage() . '. See full message in log';
            dealPageSysLog('error', 'BULK_CREATE_ERROR: ', $exception);
        }
        return \Response::json($response);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkCreateWithSchedule(Request $request) {
        $response = $this->getDefaultStatus();
        try {
            $catalogPage = 0;
            if (\Cache::has('dealCrawler::catalogPage')) {
                $catalogPage = \Cache::get('dealCrawler::catalogPage');
            }
            $count = $this->catalogRepository->read(['crawl_state' => 'processing', 'metrics' => 'count', 'columns' => ['id', 'cid', 'crawl_page']]);
            if ($count <= 0 ) {
                $response = $this->getSuccessStatus();
                $response['message'] = 'All Done';
                \Log::info('CRAWL_DEALS_IS_ALL_DONE');
                return \Response::json($response);
            }
            $catalog = $this->catalogRepository->read(['crawl_page' => $catalogPage, 'crawl_state' => 'processing', 'metrics' => 'first', 'columns' => ['id', 'cid', 'crawl_page']]);
            if (!empty($catalog)) {
                $catalogId = $catalog->cid;
                $pageId = $catalog->crawl_page + 1;
                $reqResult = $this->apiRequestRepository->readCatalogProducts($catalogId, $pageId);
                \Log::info('READ_FROM_API_IN_PAGE[' . $pageId . ']=' . count($reqResult));
                $expireAt = Carbon::now()->addDay(30);
                \Cache::put('dealCrawler::catalogPage', $catalogPage, $expireAt);
                if (!empty($reqResult)) {
                    $bulkInsertData = [];
                    foreach ($reqResult as $item) {
                        $bulkInsertData[] = $this->buildInsertDealItem($item);
                    }
                    $result = sendHttpRequest("https://couponforless.com/service/deal/bulk-create",
                        "POST",
                        ["params" => $bulkInsertData],
                        [
                            "Authorization: Basic YXBpOjEyM0AxMjNh",
                            "Accept: application/json, text/plain, */*",
                            "Content-Type: application/json;charset=utf-8"
                        ]);
                    \Log::info('DEAL' . json_encode($result));
                    if (isset($result['status']) && $result['status'] === 'successful') {
                        $runAt = Carbon::now()->addSeconds(30);
                        $job = (new DealProductJob())->delay($runAt);
                        $this->dispatch($job);
                    }
                    $this->catalogRepository->update($catalog->id, ['crawl_page' => $catalog->crawl_page + 1]);
                } else {
                    $this->catalogRepository->update($catalog->id, ['crawl_page' => 0, 'crawl_state' => 'done']);
                    $runAt = Carbon::now()->addSeconds(30);
                    $job = (new DealProductJob())->delay($runAt);
                    $this->dispatch($job);
                }
                $response = $this->getSuccessStatus();
                $response['message'] = 'Job was set!';
            } else {
                //When catalog current page crawl completely go to next page.
                \Log::info('DONE_ON_PAGE[' . $catalogPage . ']_GOTO_NEXT');
                $catalogPage = $catalogPage + 1;
                $expireAt = Carbon::now()->addDay(30);
                \Cache::put('dealCrawler::catalogPage', $catalogPage, $expireAt);
                $runAt = Carbon::now()->addSeconds(15);
                $job = (new DealProductJob())->delay($runAt);
                $this->dispatch($job);
                $response = $this->getSuccessStatus();
                $response['message'] = 'Job was set!';
            }

        } catch (\Exception $exception) {
            $response['message'] = $exception->getMessage();
            dealPageSysLog('error', 'BULK_CREATE_DEAL: ', $exception);
        }
        return \Response::json($response);
    }

    /**
     * @param $rawData
     * @return array
     */
    protected function buildInsertDealItem($rawData) {
        $saleOff = 0;
        if ($rawData['salePrice'] > 0 && $rawData['salePrice'] < $rawData['price']) {
            $saleOff = floor((($rawData['price'] - $rawData['salePrice']) / $rawData['price']) * 100);
        }
        $storeId = 0;
        $categoryIds = '';
        if (!empty($rawData['manufacturer'])) {
            $storeId = $this->findLocalStore($rawData['manufacturer']);
        }
        if (!empty($rawData['category'])) {
            $categoryIds = $this->findLocalCategory($rawData['category']);
        }
        $retVal = [
            "title" => $rawData["name"],
            "slug" => slugify($rawData["name"]),
            "crawl_id" => $rawData["pid"],
            "search_slug" => slugify($rawData["name"]),
            "image" => $rawData["imageUrl"],
            "affiliate_link" => $rawData["linkUrl"],
            "origin_link" => $rawData["deepLinkURL"],
            "content" => $rawData["description"],
            "meta_title" => $rawData["name"],
            "meta_description" => $rawData["shortDescription"],
            "meta_keywords" => $rawData["keywords"],
            "currency" => $rawData["priceCurrency"],
            "mpn" => $rawData["mpn"],
            "sku" => $rawData["sku"],
            "price" => $rawData["price"],
            "sale_price" => $rawData["salePrice"],
            "discount" => !empty($retVal['discount']) ? $retVal['discount'] : $saleOff,
            "in_stock" => $rawData["isInstock"],
            "store_id" => $storeId,
            "category_id" => $categoryIds
        ];

        return $retVal;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeDuplicateDeals(Request $request) {
        $response = $this->getDefaultStatus();
        try {
            $fields = $request->get('fields', 'slug');
            $totalDuplicate = Deal::query()
                                    ->having(\DB::raw('COUNT(*)'), '>', 1)
                                    ->select([$fields, \DB::raw('COUNT(*) AS total')])
                                    ->groupBy($fields)
                                    ->orderBy('total', 'DESC')
                                    ->get();
            $totalDeleted = 0;
            foreach ($totalDuplicate as $item) {
                $ids = Deal::where($fields, $item->$fields)->pluck('id');
                if (count($ids) > 0) {
                    $ids = $ids->toArray();
                    array_shift($ids);
                    if (count($ids) > 0) {
                        $totalDeleted++;
                        DealCategory::whereIn('deal_id', $ids)->delete();
                        Deal::whereIn('id', $ids)->delete();
                    }
                }
            }
            $response = $this->getSuccessStatus(['count' => $totalDeleted]);
        } catch (\Exception $ex) {
            dealPageSysLog('error', 'REMOVE_DUPLICATE_DEALS: ', $ex);
        }
        return \Response::json($response);
    }

    /**
     * @param $manufactureName
     * @return int
     */
    protected function findLocalStore($manufactureName) {
        $retVal = 0;
        $findStore = Store::where('title', 'like',"%" . trim($manufactureName) . "%")->get(['id']);
        if (!empty($findStore) && count($findStore) > 0) {
            $retVal = $findStore[0]->id;
        }
        return $retVal;
    }

    /**
     * @param $categoryName
     * @return string
     */
    protected function findLocalCategory($categoryName) {
        $retVal = '';
        $listName = explode('>', $categoryName);
        $findCategory = Category::where('title', 'like', "%" . trim($listName[0]) . "%")->pluck('id');
        if (!empty($findCategory)) {
            $retVal = $findCategory->toArray();
            $retVal = join(',', $retVal);
        }
        if (isset($listName[1]) && empty($retVal)) {
            $findCategory = Category::where('title', 'like', "%" . trim($listName[1]) . "%")->pluck('id');
            if (!empty($findCategory)) {
                $retVal = $findCategory->toArray();
                $retVal = join(',', $retVal);
            }
        }
        return $retVal;
    }

    /**
     * @param $params
     * @return array
     */
    protected function buildData($params) {
        $retVal = [];
        foreach ($params as $key => $val) {
            $formatKey = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $key));
            if ($formatKey == 'origin_url') {
                $formatKey = 'origin_link';
            } else if ($formatKey == 'affilidate_url') {
                $formatKey = 'affiliate_link';
            }
            $retVal[$formatKey] = $val;
        }
        if (isset($retVal['title'])) {
            $retVal['slug'] = slugify($retVal['title']);
            $storeId = isset($retVal['store_id']) ? $retVal['store_id'] : -1;
            $searchSlug = $retVal['title'];
            if ($storeId > 0) {
                $findStore = Store::where('id', $storeId)->first(['slug']);
                if (!empty($findStore)) {
                    $searchSlug .= ' ' . $findStore->slug;
                }
            }
            $retVal['search_slug'] = slugify($searchSlug);
        }
        $retVal['currency'] = 'USD';
        $retVal['sale_off'] = 0;
        if ($retVal['sale_price'] > 0 && $retVal['sale_price'] < $retVal['price']) {
            $retVal['sale_off'] = floor((($retVal['price'] - $retVal['sale_price']) / $retVal['price']) * 100);
        }
        $this->dealCategoryIds = $retVal['category_ids'];
        unset($retVal['publish_time']);
        unset($retVal['category_ids']);
        unset($retVal['tag_ids']);
        unset($retVal['is_pinned']);
        return $retVal;
    }

}