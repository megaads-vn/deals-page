<?php

namespace Megaads\DealsPage\Controllers\Services;

use Carbon\Carbon;
use Google\Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Megaads\DealsPage\Jobs\DealProductJob;
use Megaads\DealsPage\Models\Catalog;
use Megaads\DealsPage\Models\Category;
use Megaads\DealsPage\Models\CrawlerDeal;
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
    public function create(Request $request)
    {
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
    public function update(Request $request)
    {
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
    public function delete(Request $request)
    {
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
    public function dealMigration(Request $request)
    {
        set_time_limit(86400);
        $response = $this->getDefaultStatus();
        try {
            $sourceTable = $request->get('source', NULL);
            $destinationTable = $request->get('des', NULL);
            if (!empty($sourceTable) && !empty($destinationTable)) {
                $sourceQuery = \DB::table($sourceTable);
                if ($request->has('has_store')) {
                    $sourceQuery->where('store_id', '<>', 0);
                }
                if ($request->has('store_id') && $request->get('store_id') != '') {
                    $sourceQuery->where('store_id', $request->get('store_id'));
                }
                $total = (clone $sourceQuery)->count();
                if ($request->has('debug') && $request->get('debug') == 1) {
                    echo "<pre>";
                    print_r([
                        'query' => $sourceQuery->toSql()
                    ]);
                    echo "</pre>";
                    die;
                }
                $perpage = 300;
                $pageCount = ceil($total / $perpage);
                $totalPage = 0;
                for ($p = 0; $p < $pageCount; $p++) {
                    \Log::info('DEAL_MIGATION: [PID=' . $p . ']');
                    $offset = $p * $perpage;
                    $query = (clone $sourceQuery)->limit($perpage)->offset($offset);
                    $oldData = $query->get();
                    \Log::info('DEAL_MIGATION: [DATA=' . count($oldData) . ']');
                    if (!empty($oldData)) {
                        foreach ($oldData as $oldItem) {
                            $insertNewItems = [
                                "title" => $oldItem->title,
                                "slug" => $oldItem->slug,
                                "search_slug" => $oldItem->slug,
                                "content" => $oldItem->content,
                                "meta_title" => $oldItem->meta_title,
                                "meta_description" => $oldItem->meta_description,
                                "meta_keywords" => $oldItem->meta_keywords,
                                "image" => $oldItem->image,
                                "clicks" => $oldItem->clicks,
                                "expired_time" => $oldItem->expired_time,
                                "price" => $oldItem->price,
                                "sale_price" => $oldItem->sale_price,
                                "currency" => "USD",
                                "affiliate_link" => $oldItem->affiliate_link,
                                "origin_link" => $oldItem->origin_link,
                                "sorder" => $oldItem->sorder,
                                "sorder_in_category" => $oldItem->sorder_in_category,
                                "views" => $oldItem->views,
                                "vote_up" => $oldItem->vote_up,
                                "vote_down" => $oldItem->vote_down,
                                "store_id" => $oldItem->store_id,
                                "category_id" => $oldItem->category_id,
                                "discount" => $oldItem->discount,
                                "crawl_id" => $oldItem->crawl_id,
                                "mpn" => $oldItem->mpn,
                                "sku" => $oldItem->sku,
                                "manufacturer" => $oldItem->manufacturer,
                                "in_stock" => $oldItem->manufacturer,
                                "status" => $oldItem->status,
                                "type" => $oldItem->type,
                                "code" => $oldItem->code,
                                "publish_time" => $oldItem->publish_time,
                                "create_time" => $oldItem->create_time,
                                "update_time" => $oldItem->update_time,
                            ];
                            $insertId = \DB::table($destinationTable)->insertGetId($insertNewItems);
                            if (!empty($insertId)) {
                                DealRelation::where('object_id', $oldItem->id)->update([
                                    'object_id' => $insertId,
                                ]);
                            }
                        }
                        $totalPage++;
                    }
                }
                if ($totalPage > 0) {
                    $response = [
                        'status' => 'successful',
                        'message' => 'Total page ' . $totalPage . ' inserted'
                    ];
                } else {
                    $response = [
                        'status' => 'successful',
                        'message' => 'Null data'
                    ];
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
    public function bulkCreate(Request $request)
    {
        $response = $this->getDefaultStatus();
        try {
            if ($request->has('params')) {
                $params = $request->get('params');
                \Log::info('BULK_CREATE_DEAL');
                $insertResult = [];
                foreach ($params as $item) {
                    if (!isset($item['slug']) || empty($item['slug'])) {
                        \Log::info('EMPTY_DEAL_SLUG: ' . json_encode($item));
                        continue;
                    }
                    $findExists = Deal::query()
                        ->where('slug', $item['slug'])
                        // ->orWhere('origin_link', $item['origin_link'])
                        ->first(['id']);
                    if (empty($findExists)) {
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
    public function bulkCreateWithSchedule(Request $request)
    {
        set_time_limit(30);
        $response = $this->getDefaultStatus();
        try {
            $today = date('Y-m-d 00:00:00');
            $catalog = $this->catalogRepository->read(['metrics' => 'first', 'updated_to' => $today,'columns' => ['id', 'cid', 'crawl_page', 'name']]);

            if (!empty($catalog)) {
                $catalogId = $catalog->cid;
                $startGetCatalog = microtime(true);
                $reqResult = $this->apiRequestRepository->readCatalogProducts($catalogId, 1);
                $getCatalogTimeElapsed = microtime(true) - $startGetCatalog;
//                \Log::info('MEASURE_GET_CATALOG= ' . $getCatalogTimeElapsed . ' secs');
                $totalDeals = 0;
                if (!empty($reqResult)) {
                    $bulkInsertData = [];
                    if (isset($reqResult['message']) && $reqResult['message'] == 'No Valid or Approved Catalog found for your domain') {
                        $response = $this->getSuccessStatus();
                        $response['message'] = 'Job was set!';
                        $this->catalogRepository->update($catalog->id, ['crawl_page' => $catalog->crawl_page + 1]);
                        return response()->json($response);
                    }
                    foreach ($reqResult as $item) {
                        $bulkInsertData[] = $this->buildInsertDealItem($item);
                    }
                    $totalDeals = count($bulkInsertData);
                    if ($totalDeals > 0) {
                        $this->bulkCreateItem($bulkInsertData);
                    }
                    $this->catalogRepository->update($catalog->id, ['update_time' => date('Y-m-d H:i:s')]);
                } else {
                    $this->catalogRepository->update($catalog->id, ['crawl_page' => 0, 'crawl_state' => 'done', 'update_time' => date('Y-m-d H:i:s')]);
                }
                $response = $this->getSuccessStatus();
                $response['cataLog'] = $catalog->name;
                $response['totalDeal'] = $totalDeals;
                $response['message'] = 'Job was set!';
            }

        } catch (\Exception $exception) {
            $response['message'] = $exception->getMessage();
            dealPageSysLog('error', 'BULK_CREATE_DEAL: ', $exception);
        }
        return \Response::json($response);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function crawlDeals(Request $request)
    {
        set_time_limit(84600);
        $catalogQuery = Catalog::query();
        $perPage = 100;
        $totalCatalog = $catalogQuery->count();
        $pageCount = ceil($totalCatalog / $perPage);
        $pageDone = 0;
        for ($p = 0; $p < $pageCount; $p++) {
            $offset = $perPage * $p;
            $pageDone++;
            $result = $catalogQuery->offset($offset)->limit($perPage)->get(['cid']);
            if (!empty($result)) {
                foreach ($result as $idx => $item) {
                    $catalogId = $item->cid;
                    $crawlResult = $this->apiRequestRepository->readCatalogProducts($catalogId, 1);
                    if (!empty($crawlResult)) {
                        if (isset($crawlResult['message']) && $crawlResult['message'] == 'No Valid or Approved Catalog found for your domain') {
                            $this->catalogRepository->update($catalogId, ['crawl_state' => 'invalid']);
                        } else {
                            $bulkInsertData = [];
                            foreach ($crawlResult as $item) {
                                $bulkInsertData[] = $this->buildInsertDealItem($item);
                            }
                            if (count($bulkInsertData)) {
                                sendHttpRequest(config('deals-page.app_url') . "/service/deal/bulk-create",
                                    "POST",
                                    ["params" => $bulkInsertData],
                                    [
                                        "Authorization: Basic YXBpOjEyM0AxMjNh",
                                        "Accept: application/json, text/plain, */*",
                                        "Content-Type: application/json;charset=utf-8"
                                    ]);
                            }
                        }
                    }
                    sleep(2);
                }
            }
        }
        return response()->json([
            'status' => 'successful',
            'totalPage' => $pageDone
        ]);
    }

    /**
     * @param $rawData
     * @return array
     */
    protected function buildInsertDealItem($rawData)
    {
        $saleOff = 0;
        $brandName = str_replace(['.com', '.co.uk', '.org'], '', $rawData['brand']);
        $advertiserName = $rawData['advertiserName'];
        if (!empty($brandName) && !empty($rawData['brand'])) {
            $brandName = $this->convertCamelToSnake($brandName);
            $storeId = $this->findLocalStore($brandName);
        } else if (!empty($advertiserName)) {
            $brandName = $advertiserName;
            $storeId = $this->findLocalStore($advertiserName);
        }
        if ($rawData['salePrice'] > 0 && $rawData['salePrice'] < $rawData['price']) {
            $saleOff = floor((($rawData['price'] - $rawData['salePrice']) / $rawData['price']) * 100);
        }
        $categoryIds = '';
        if (!empty($rawData['manufacturer']) && $storeId == 0) {
            $brandName = $rawData['manufacturer'];
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
            "store_tmp" => $rawData['manufacturer'],
            "category_id" => $categoryIds,
            'manufacturer' => $brandName,
            'raw_data' => json_encode($rawData),
            'upc_or_ean' => $rawData['upCorEAN']
        ];
        return $retVal;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeDuplicateDeals(Request $request)
    {
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
     * @return void
     */
    public function downDealImage()
    {
        $res = $this->getDefaultStatus();
        $diskSpace = $this->checkDiskSpace();

        if (isset($diskSpace['free_percent']) && $diskSpace['free_percent'] >= 3) {
            // Get Image.
            $dealImages = Deal::where('crawl_image', 'processing')
                ->limit(300)
                ->pluck('image', 'id');
            if (!empty($dealImages)) {
                $updatedResult = 0;
                foreach ($dealImages as $id => $imageUrl) {
                    $imageName = $this->getImageName($imageUrl);
                    $result = $this->saveUrlImage($imageUrl, $imageName);
                    if ($result !== "") {
                        Deal::where('id', $id)->update([
                            "image" => $result,
                            "crawl_image" => 'done'
                        ]);
                        $updatedResult++;
                    } else {
                        Deal::where('id', $id)->update([
                            "status" => "pending",
                            "crawl_image" => 'done'
                        ]);
                    }
                }
                $res = $this->getSuccessStatus(['success_count' => $updatedResult]);
            }
        } else {
            $res['message'] = 'Do not enough disk space. Free ' . $diskSpace['free'];
        }
        return response()->json($res);
    }

    public function fetchOriginLinkContent(Request $request)
    {
        $url = $request->get('url');
        $data = $this->urlGetContents($url, 'Mozilla/5.0 (Windows NT 6.2; WOW64; rv:17.0) Gecko/20100101 Firefox/17.0');
        return response()->json($data);
    }

    /**
     * @param $manufactureName
     * @return int
     */
    protected function findLocalStore($manufactureName)
    {
        $retVal = 0;
        $findStore = Store::where('title', 'like', "%" . trim($manufactureName) . "%")->get(['id']);
        if (!empty($findStore) && count($findStore) > 0) {
            $retVal = $findStore[0]->id;
        }
        return $retVal;
    }

    /**
     * @param $categoryName
     * @return string
     */
    protected function findLocalCategory($categoryName)
    {
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
    protected function buildData($params)
    {
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

    /**
     * @return array
     */
    protected function checkDiskSpace()
    {
        $total_space = disk_total_space("/");
        $free_space = disk_free_space("/");

        return [
            'total' => $this->formatSizeUnits($total_space),
            'free' => $this->formatSizeUnits($free_space),
            'usage_percent' => round(($total_space - $free_space) / $total_space * 100),
            'free_percent' => round(($free_space) / $total_space * 100),
        ];
    }

    /**
     * @param $bytes
     * @return string
     */
    protected function formatSizeUnits($bytes)
    {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            $bytes = $bytes . ' bytes';
        } elseif ($bytes == 1) {
            $bytes = $bytes . ' byte';
        } else {
            $bytes = '0 bytes';
        }

        return $bytes;
    }

    /**
     * @param $url
     * @param $imageName
     * @return bool
     */
    protected function saveUrlImage($url, $imageName)
    {
        $retVal = "";
        if ($this->isValidUrl($url)) {
            $filePath = public_path("frontend/images/deals");
            if (!file_exists($filePath)) {
                mkdir($filePath, 0777);
            }
            $fullPathImage = $filePath . "/" . $imageName;
            if (file_exists($fullPathImage)) {
                $retVal = "/frontend/images/deals/" . $imageName;
            } else {

                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                $data = curl_exec($ch);
                curl_close($ch);

                file_put_contents($fullPathImage, $data);
                $retVal = "/frontend/images/deals/" . $imageName;
            }
        }
        return $retVal;
    }

    /**
     * @param $url
     * @return bool
     */
    private function isValidUrl($url)
    {
        // Kiểm tra xem URL có bắt đầu bằng một trong các giao thức sau không
        if (preg_match('/^(http:\/\/|https:\/\/|ftp:\/\/|ftps:\/\/|mailto:|tel:|data:|irc:|ircs:|news:|nntp:)/i', $url)) {
            // Kiểm tra xem URL có chứa tên miền hoặc địa chỉ IP của máy chủ không
            if (filter_var($url, FILTER_VALIDATE_URL)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $url
     * @return mixed
     */
    private function getImageName($url)
    {
        $removeUrlParams = '/\?.*$/';
        $url = preg_replace($removeUrlParams, '', $url);

        $pattern = '/\/([^\/]+\.(jpg|png|jpeg))/i';
        preg_match($pattern, $url, $matches);
        $filename = isset($matches[1]) ? $matches[1] : "";
        $filename = str_replace(",", '_', $filename);
        if ($filename === "") {
            $filePaths = explode("/", $url);
            $filename = end($filePaths) . ".jpg";
        }
        return $filename;
    }

    protected function convertCamelToSnake($string)
    {
        $string = strtolower(preg_replace('/(?<!^)[A-Z]/', ' $0', $string));
        return ucwords($string, ' ');
    }

    /**
     * @param $params
     * @return void
     */
    protected function bulkCreateItem($params) {
        try {
            \Log::info('BULK_CREATE_DEAL');
            $insertResult = [];
            $startCreate = microtime(true);
            foreach ($params as $item) {
                if (!isset($item['slug']) || empty($item['slug'])) {
                    \Log::info('EMPTY_DEAL_SLUG: ' . json_encode($item));
                    continue;
                }
                $findExists = Deal::query()
                    ->where('slug', $item['slug'])
                    // ->orWhere('origin_link', $item['origin_link'])
                    ->first(['id']);
                if (empty($findExists)) {
                    $resultId = $this->dealRepository->create($item);
                    $insertResult[$resultId] = $item['category_id'];
                }
            }
            $timeElapsedSecsCreate = microtime(true) - $startCreate;
//            \Log::info('MEASURE_CREATE=' . $timeElapsedSecsCreate . ' secs');
            if (count($insertResult) > 0) {
                $startAddRelation = microtime(true);
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
                $timeElapsedSecsRelation = microtime(true) - $startAddRelation;
//                \Log::info('MEASURE_RELATION=' . $timeElapsedSecsRelation . ' secs');
            }
        } catch (\Exception $exception) {
            \Log::error('CREATE_DEAL_ITEMS: ' . $exception->getMessage() . '. Line: ' . $exception->getLine() . '. File: ' . $exception->getFile());
        }
    }
}
