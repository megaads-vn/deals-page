<?php

namespace Megaads\DealsPage\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Author;
use App\Models\Coupon;
use App\Models\StoreContact;
use App\Models\StoreEmbed;
use App\Models\StoreKeyword;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Megaads\DealsPage\Models\Category;
use Megaads\DealsPage\Models\Deal;
use Megaads\DealsPage\Models\DealCategory;
use Megaads\DealsPage\Models\Store;
use Megaads\DealsPage\Models\DealRelation;
use App\Utils\Utils;
use Illuminate\Support\Facades\Input;
use Megaads\DealsPage\Models\StoreCategory;
use Megaads\DealsPage\Models\StoreReview;
use Megaads\DealsPage\Repositories\CategoryRepository;
use Megaads\DealsPage\Repositories\DealRepository;
use Megaads\DealsPage\Repositories\StoreRepository;
use PHPExcel_Cell;
use PHPExcel_IOFactory;
use Illuminate\Http\Request as HttpRequest;

class DealsController extends Controller {

    const test = 1111;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    private $dealPageTable;
    private $dealPageColumns;
    protected $dealRepository;
    protected $categoryRepository;
    protected $storeRepository;

    public function __construct() {
        parent::__construct();
        $this->dealPageTable = \Config::get('deals-page.deal_related_page.name', 'store_n_keyword');
        $this->dealPageColumns = \Config::get('deals-page.deal_related_page.name', ['id', 'keyword']);
        $this->dealRepository = new DealRepository();
        $this->categoryRepository = new CategoryRepository();
        $this->storeRepository = new StoreRepository();
        view()->share('allDealTitle', 'All Deals');
    }

    /**
     * @param $slug
     * @return \Illuminate\Contracts\View\View
     */
    public function index($slug)
    {
        $getDeal = Deal::from('deals')
                    ->where('slug', $slug)
                    ->where('status', Deal::STATUS_ACTIVE)
                    ->first();
        if (empty($getDeal)) {
            abort(404);
        }
        $otherStoreDeal = $this->getOtherDealsOfStore($getDeal);
        $relatedDeal = $this->getRelatedDeal($getDeal->id);
        $relatedCateDeal = $this->getRelatedCateDeal($getDeal->id);
        $relatedStoreReviews = $this->relatedStoreReview($getDeal->id);
        $categories = $getDeal->categories()->first(['id', 'title', 'slug']);
        $store = $getDeal->store()->first(['id', 'title', 'slug', 'vote_up', 'vote_down']);

        $breadcrumbs = [];
        $canonicalLink = route('deal::all');
        
        if (!empty($categories)) {
            $breadcrumbs[] = [
                'title' => $categories->title,
                'url' => route('frontend::category::deals', ['slug' => $categories->slug])
            ];
        }
        
        if (!empty($store)) {
            $breadcrumbs[] = [
                'title' => $store->title,
                'url' => route('frontend::store::listDeal', ['slug' => $store->slug])
            ];
            $canonicalLink = route('frontend::store::listDeal', ['slug' => $store->slug]);
        }
        $breadcrumbs[] = [
            'title' => $getDeal->title,
            'url' => ''
        ];
        
        if (empty($store) && !empty($categories)) {
            $canonicalLink = route('frontend::category::deals', ['slug' => $categories->slug]);
        }
        view()->share('canonicalLink', $canonicalLink);

        $retVal['detailItem'] = $getDeal;
        $retVal['dealCategories'] = $categories;
        $retVal['dealStore'] = $store;
        $retVal['otherStoreDeal'] = $otherStoreDeal;
        $retVal['relatedDeal'] = $relatedDeal;
        $retVal['relatedCateDeal'] = $relatedCateDeal;
        $retVal['relatedStoreReviews'] = $relatedStoreReviews;
        $retVal['meta'] = ['title' => $getDeal->title];
        $retVal['title'] = $getDeal->title;
        $retVal['breadcrumbs'] = $breadcrumbs;
        return \View::make('deals-page::deals.detail', $retVal);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\View\View
     */
    public function allDeals()
    {
        $keyConfig = 'top.product.search';
        $categories = $this->categoryRepository->getListCategory();
        $deals = $this->dealRepository->getList();
        $stories = $this->storeRepository->getListStore();
        $dealsTopSearch = $this->dealRepository->getListFromConfig($keyConfig);
        $storiesReview = $this->storeRepository->getListStoreOfReview();
        $totalDeals = $this->dealRepository->getTotalDealActive();

        return view('deals-page::deals.all',[
            'categories' => $categories,
            'deals' => $deals,
            'stories' => $stories,
            'dealsTopSearch' => $dealsTopSearch,
            'storiesReview' => $storiesReview,
            'totalDeals' => $totalDeals,
        ]);
    }

    /**
     * @param $itemId
     * @param \Request $request
     * @return \Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function dealDetail($itemId, \Request  $request)
    {
        return redirect('/');
        $segment = request()->segment(2);
        $filterDeal = [
            'id' => $itemId,
            'metrics' => 'first',
            'columns' => ['id', 'title', 'slug',
                'image', 'content', 'price',
                'sale_price', 'discount', 'store_id',
                'expire_time', 'origin_link', 'affiliate_link',
                'create_time', 'modifier_name', 'modifier_id', 'views']
        ];
        $dataDeal = $this->dealRepository->read($filterDeal);
        if ( isset($dataDeal['data'])) {
            $dealId = $dataDeal['data']->id;
            $views = isset($dataDeal['data']->views) ? $dataDeal['data']->views : 0;
            $this->dealRepository->update($dealId, ["views" => $views + 1]);
            $retVal['dataDeal'] = $dataDeal['data'];

            if ( $segment == 'c' ) {
                $retVal['showPopup'] = true;
            }
            $retVal['title'] = 'All Deals';
            $retVal['meta']['title'] = 'All Deals';
            return view('deals-page::deals.deal-detail', $retVal);
        } else {
            return view('errors.404');
        }

    }

    /**
     * @param $slug
     * @param \Request $request
     * @return \Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function listByStore($slug, \Request $request)
    {
        return redirect('/');
        $retVal = [];
        $findStore = Store::query()->where('slug', $slug)->first(['id', 'title', 'slug']);
        if (empty($findStore))
            abort(404);

        $dealFilter = [
            'storeId' => $findStore->id,
            'columns' => ['id', 'title', 'slug',
                'image', 'content', 'price',
                'sale_price', 'discount', 'store_id',
                'expire_time', 'origin_link', 'affiliate_link',
                'create_time', 'modifier_name', 'modifier_id'],
            'order_by' => 'discount::DESC',
            'pageId' => 0,
            'pageSize' => 52
        ];
        if (isset($_GET['p'])) {
            $dealFilter['pageId'] = $_GET['p'] - 1;
        }

        $retVal['brands'] = NULL;
        $retVal['stores'] = $this->getDealStore();
        $retVal['title'] = !empty($findStore) ? 'All Deals in ' . $findStore->title : '';
        $retVal['meta']['title'] = !empty($findStore) ? 'All Deals in ' . $findStore->title : '';
        $findResult = $this->dealRepository->read($dealFilter);
        if ($findResult['status'] = 'successful') {
            $retVal['deals'] = $findResult['data'];
            $dealFilter['metrics'] = 'count';
            unset($dealFilter['pageId']);
            unset($dealFilter['pageSize']);
            $getTotal = $this->dealRepository->read($dealFilter);
            $totalCount  = 0;
            $pageCount = 0;
            if (isset($getTotal['data'])) {
                $totalCount = $getTotal['data'];
                $pageCount = ceil($totalCount / $findResult['pageSize']);
            }
            $retVal['pagination'] = [
                'page_count' => $pageCount,
                'total_count' => $totalCount,
            ];
        }
        view()->share('allDealTitle', 'All ' . $findStore->title . ' Deals');
        return view('deals-page::deals.alldeals', $retVal);
    }

    /**
     * @param $slug
     * @return \Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|void
     */
    public function goUrl($slug)
    {
        $query = Deal::query();
        if (!is_numeric($slug)) {
            $query->where('slug', $slug);
        } else {
            $query->where('id', $slug);
        }
        $deals = $query->first(['affiliate_link', 'store_id', 'id', 'clicks']);
        if (!empty($deals) && !empty($deals->affiliate_link)) {
            $click = isset($deals->clicks) ? $deals->clicks : 0;
            $this->dealRepository->update($deals->id, ["clicks" => $click +1 ]);
            $url = $this->addXcust($deals->affiliate_link,$deals->store_id,$deals->id, 'deal');
            return redirect($url);
        } else {
            abort(404);
        }
    }

    /**
     * @param $item
     * @return string
     */
    private function saveDealsImage($item) {
        $imageUrl = $item->imageUrl;
        $imageUrl = explode('?', $imageUrl)[0];
        $dealsPath = "images/deals";
        $absolutePath = public_path($dealsPath);
        if (!file_exists($absolutePath)) {
            mkdir($absolutePath, 0775);
        }

        $extractImage = explode("/", $imageUrl);
        $imageName = end($extractImage);
        $fullImageSavedPath = $absolutePath . "/" . $imageName;
        if (!file_exists($fullImageSavedPath)) {
            $ch = curl_init($imageUrl);
            $fp = fopen($fullImageSavedPath, 'wb');
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_exec($ch);
            curl_close($ch);
            fclose($fp);
        }
        return "/" . $dealsPath . "/" . $imageName;
    }

    /**
     * @param $url
     * @param $storeId
     * @param $couponId
     * @param $type
     * @return mixed|string
     */
    protected function addXcust($url, $storeId, $couponId = 0, $type = 'coupon')
    {
        $uId = app('session')->get('uId');
        if (!$uId) {
            $uId = substr(str_shuffle(str_repeat("0123456789abcdefghijklmnopqrstuvwxyz", 29)), 0, 29);
        }
        $customId = $uId . (substr(time(), -3));
        if ($type == 'deal'){
            $customId = 'deal' . substr($customId, 4);
        }
        $network = '';
        $regex = '/(go.redirectingat.com)/i';
        preg_match($regex, $url, $match);
        if ($uId && count($match) > 0) {
            $network = 'skimlink';
            $url .= '&xcust=' . $customId;
        };
        preg_match('/(redirect.viglink.com)/i', $url,$match2);
        if ($uId && count($match2) > 0) {
            $network = 'viglink';
            $url .= '&cuid=' . $customId;
        };
        preg_match('/(track.flexlinkspro.com)/i', $url, $match3);
        if ($uId && count($match3) > 0) {
            $network = 'flexoffer';
            $url .= '&fobs=' . $customId;
        };
        $metaData = '';
        $params = Input::all();
        $dataFromReferer = $this->getDataFromReferer();
        if(count($dataFromReferer) > 0) {
            $params = array_merge($dataFromReferer, $params);
        }
        if (count($params) > 0) {
            $metaData = json_encode($params);
        }
        $this->netGoTracking([
            'url' => $url,
            'network' => $network,
            'store' => $storeId,
            'coupon_id' => $couponId,
            'custom_id' => $customId,
            'meta_data' => $metaData
        ]);
        return $url;
    }

    /**
     * @return array
     */
    private function getDataFromReferer()
    {
        $retVal = [];
        try {
            $referer = app('session')->get('referer');
            if ($referer && strpos($referer, 'getcouponhere.com') !== false) {
                $queryString = parse_url($referer, PHP_URL_QUERY);
                parse_str($queryString, $arrParams);
                if (array_key_exists('keyword', $arrParams)) {
                    $retVal['keyword'] = $arrParams['keyword'];
                }
            }
        } catch (\Exception $ex) {

        }
        return $retVal;
    }

    /**
     * @param $params
     * @return void
     */
    protected function netGoTracking($params = [])
    {
        try {
            $refererUrl = isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : '';
            $params = array_merge($params, [
                'site' => config("app.siteName"),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'],
                'ip' => Utils::get_ip_address(),
                'refer_url' => $refererUrl
            ]);
            $env = app('session')->get('gch_env');
            if ($env) {
                $params['device'] = $env;
            }
            $mactr = app('session')->get('mactr');
            if ($mactr) {
                $params['mactr'] = $mactr;
            }
            $netGoRequestUrl = config("app.netgoDomain") . "/tracking";
            sendHttpRequest($netGoRequestUrl, 'POST', $params);
        } catch (\Exception $e) {
            \Log::useDailyFiles(storage_path() . '/logs/type-network.log');
            \Log::error("netGoTracking:" . $e->getMessage() . ":" . $e->getLine());
        }
    }

    /**
     * @return null
     */
    protected function getDealStore()
    {
        $retVal = NULL;
        $dealStoreIds = Deal::query()->where('store_id', '>', 0)->distinct()->pluck('store_id');
        if (count($dealStoreIds) > 0) {
            $storeIds = $dealStoreIds->toArray(); 
            $retVal = Store::whereIn('id', $storeIds)->orderBy('title', 'ASC')->select(['id', 'title', 'slug'])->get();
        }
        return $retVal;
    }

    /**
     * @param $itemId
     * @param $slug
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\View\View
     */
    protected function showDeal($itemId, $slug = '')
    {
        $retVal = [];
        $dealFilter = [
            'columns' => ['id', 'title', 'slug',
                'image', 'content', 'price',
                'sale_price', 'discount', 'store_id',
                'expire_time', 'origin_link', 'affiliate_link',
                'create_time', 'modifier_name', 'modifier_id'],
            'order_by' => 'discount::DESC'
        ];
        $retVal['brands'] = NULL;
        $retVal['stores'] = $this->getDealStore();
        $findResult = $this->dealRepository->read($dealFilter);
        if ($findResult['status'] = 'successful') {
            $retVal['deals'] = $findResult['data'];
            $dealFilter['metrics'] = 'count';
            $getTotal = $this->dealRepository->read($dealFilter);
            $totalCount  = 0;
            $pageCount = 0;
            if (isset($getTotal['data'])) {
                $getTotal = $getTotal['data'];
                $pageCount = ceil($getTotal / $findResult['pageSize']);
            }
            $retVal['pagination'] = [
                'page_count' => $pageCount,
                'total_count' => $totalCount,
            ];
        }
        return view('deals-page::deals.alldeals', $retVal);
    }


    /**
     * @param $slug
     * @param $param1
     * @param $param2
     * @return \Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|void
     */
    public function storeDeal(HttpRequest $request, $slug="", $param1 = 0, $param2 = 0)
    {
        $storeDomain = Utils::getSubdomainFromCurrentHostName($request->url());
        $routeParameters = \Route::current()->parameters();
        if (isset($routeParameters['slug'])) {
            $slug = $routeParameters['slug'];
        } else if (!isset($routeParameters['slug']) && $storeDomain) {
            $slug = $storeDomain;
        }
        $canonicalLink = route('frontend::store::listDeal', $slug);
        if (config('app.wildcard_store_domain', false)) {
            $canonicalLink = $request->url();
        }
        $retVal = [];
        $store = $this->getStore($slug);
        if (empty($store)) {
            return redirect(route('frontend::store::listByStore', ['slug' => $slug]));
        }
        $relatedStore = $this->getRelatedStoreDeal($store->id);
        $relatedCategory = $this->getRelatedCategoryDealById($store->id);
        $dataCoupon = NULL;
        $relatedCoupon = NULL;
        if(!empty(Request::segment(4)) && Request::segment(4) == 'c'){
            $dataCoupon = $this->getDataInternalRequests('/service/coupon/find', ['id' => $param1, 'host' => 'local']);
            $relatedCoupon = $this->getRelatedCoupon($dataCoupon['storeId'], $param1);
        }

        if(Request::input('c')){
            $dataCoupon = $this->getDataInternalRequests('/service/coupon/find', ['id' => Request::input('c'), 'host' => 'local']);
            $relatedCoupon = $this->getRelatedCoupon($dataCoupon['storeId'], Request::input('c'));
        }
        $this->getStoreContact($store);
        $dealFilters = [
            'store_id' => $store->id
        ];
        if (isset($_POST['dealType']) && $_POST['dealType'] == 'price') {
            $dealFilters['minPrice'] = $_POST['minPrice'];
            $dealFilters['maxPrice'] = $_POST['maxPrice'];
            $dealFilters['dealType'] = $_POST['dealType'];
        } else if (isset($_POST['dealType'])) {
            $dealFilters['dealType'] = $_POST['dealType'];
        }
        $dealResult = $this->getDealLists($dealFilters);

        if (count($dealResult['data']) <= 0 && (!isset($dealFilters['dealType']) || (isset($dealFilters['dealType']) && $dealFilters['dealType'] == 'all'))) {
            return redirect(route('frontend::store::listByStore', ['slug' => $store->slug]));
        }

        $totalDealHidden = $dealResult['total_deal'] - $dealResult['per_page'] * $dealResult['current_page'];

        $retVal['totalDealHidden'] = $totalDealHidden > 0 ? $totalDealHidden : 0;
        $retVal['listDeals'] = $dealResult['data'];
        $retVal['hasNextPage'] = ($dealResult['current_page'] < $dealResult['page_count']) ? true : false;
        $retVal['currentPage'] = $dealResult['current_page'];

        $breadcrumbs = $this->getBreadcrumbs(json_decode(json_encode($store), true), 'store');

        $author = $this->getStoreAuthor($breadcrumbs);

        $this->formatStoreContent($store, $retVal, $breadcrumbs);

        $store->metaTitle = "";
        $store->metaDescription = "";
        $store->metaKeywords = "";

        $retVal['store'] = $store;
        $defaultMeta = Utils::getDefaultMeta('store', '');
        $defaultMetaTitle = '';
        if (isset($defaultMeta->metaTitle)) {
            $defaultMetaTitle = $defaultMeta->metaTitle;
            $defaultMetaTitle = str_replace("{text}", $store->title, $defaultMetaTitle);
        }

        view()->share('canonicalLink', $canonicalLink);
        $retVal['storeEmbed'] = $this->_getStoreEmbedCoupons($store->id);
        $retVal['title'] = !empty($store->metaTitle)? \App\Utils\Utils::replaceMonthYeah(str_replace("{text}", $store->title, $store->metaTitle)):\App\Utils\Utils::replaceMonthYeah(str_replace("{text}", $store->title, $defaultMetaTitle));
        $retVal['storeNameTracking'] = $store->title;
        $retVal['storeIdTracking'] = $store->id;
        $retVal['slug'] = $slug;
        $retVal['listCoupon'] = $this->getListCoupons($store->id);
        $retVal['breadcrumbs'] = $breadcrumbs;
        $retVal['author'] = $author;
        $retVal['dealFilterActivated'] = isset($_POST['dealType']) ? $_POST['dealType'] : 'all';
        $retVal['placehoderImage'] = "/images/blank.png";
        $retVal['localSchema'] = ''; //$this->buildSchema($store,$couponResult['result']['data']);
        $retVal['dataCoupon'] = $dataCoupon;
        $retVal['relatedCoupon'] = $relatedCoupon;
        $retVal['relatedStore'] = $relatedStore;
        $retVal['relatedCategory'] = $relatedCategory;
        return view('deals-page::deals.list-by-store', $retVal);
    }


    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function loadMoreDeal()
    {
        $response = [
            'status' => 'fail'
        ];
        $filters = Input::all();
        $result = $this->getDealLists($filters);

        $totalItemHidden = $result['total_deal'] - ($result['current_page'] * $result['per_page']);
        
        if (!empty($result['data'])) {
            $response = [
                'status' => 'successful',
                'total_items_display' => $result['current_page'] * $result['per_page'],
                'total_items_hidden' => $totalItemHidden > 0 ? $totalItemHidden : 0,
                'has_next' => ($result['current_page'] < $result['page_count']) ? true : false,
                'current_page' => $result['current_page'],
                'data' => view('deals-page::common.widgets.list-deal', ['listDeal' => $result['data']])->render()
            ];
        }

        return response()->json($response);
    }

    /**
     * @param $slug
     * @return \Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function redirect($slug)
    {
        return redirect(route('frontend::store::listDeal', ['slug' => $slug]), 301);
    }


    /**
     * @param $slug
     * @param $param1
     * @param $param2
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\View\View|null
     */
    public function categoryDeals($slug="", $param1=0, $param2=0)
    {
        \View::share('isShowStore', true);

        $canonicalLink = route('frontend::category::deals', $slug);
        if(isset($param1) && $param1 > 1) {
            $canonicalLink .= '/' . $param1;
        }

        $breadcrumbs = [];
        $category = $this->getDataInternalRequests('/service/category/find',[ 'slug' => $slug ,'type'=>'category']);

        if(empty($category)){
            return abort(404);
        }
        $category = (object) $category[0];
        $storeReplacement = $this->getStoreForTemplate($category->id);
        $contentTemplates = $this->getContentTemplate(['[title]'=>$category->title,'[date]'=>Utils::timeOnGoing(date('y-m-d')),'[stores]' => $storeReplacement],'category.contentTemplate',true);

        if ( !isset($category->image) ) {
            $category->image = '/frontend/images/categories/Cate_promo.png';
        }

        $dataCoupon = $relatedCoupon = array();
        if(!empty(Request::segment(3)) && Request::segment(3) == 'c'){
            $dataCoupon = $this->getDataInternalRequests('/service/coupon/find', ['id' => $param1, 'host' => 'local']);
            $relatedCoupon = $this->getRelatedCoupon($dataCoupon['storeId'], $param1);
            $pagination = 0;

            $canonicalLink = route('frontend::category::deals', $slug);
        }

        if(!empty(Request::segment(4)) && Request::segment(4) == 'c'){
            $dataCoupon = $this->getDataInternalRequests('/service/coupon/find', ['id' => $param2, 'host' => 'local']);
            $relatedCoupon = $this->getRelatedCoupon($dataCoupon['storeId'], $param2);
        }

        if(Request::input('c')){
            $dataCoupon = $this->getDataInternalRequests('/service/coupon/find', ['id' => Request::input('c'), 'host' => 'local']);
            $relatedCoupon = $this->getRelatedCoupon($dataCoupon['storeId'], Request::input('c'));
        }

        $dealFilters = [
            'category_id' => $category->id
        ];
        if (isset($_POST['dealType']) && $_POST['dealType'] == 'price') {
            $dealFilters['minPrice'] = $_POST['minPrice'];
            $dealFilters['maxPrice'] = $_POST['maxPrice'];
            $dealFilters['dealType'] = $_POST['dealType'];
        } else if (isset($_POST['dealType'])) {
            $dealFilters['dealType'] = $_POST['dealType'];
        }
        $dealResult = $this->getDealLists($dealFilters);

//        if (count($dealResult['data']) <= 0 && (!isset($dealFilters['dealType']) || (isset($dealFilters['dealType']) && $dealFilters['dealType'] == 'all'))) {
//            return redirect(route('frontend::category::listByCategory', ['slug' => $category->slug]));
//        }
        $listDeals = $dealResult['data'];

        if (empty($listDeals) || count($listDeals) <= 0) {
          $canonicalLink = route('frontend::category::listByCategory', $category->slug);
        }

        $hasNextPage = ($dealResult['current_page'] < $dealResult['page_count']) ? true : false;
        $currentPage = $dealResult['current_page'];

        $breadCrumbsParam = json_decode(json_encode($category), true);

        $breadcrumbs = $this->getBreadcrumbs($breadCrumbsParam, 'category');
        $listCoupon = Category::find($category->id)->coupons()
                        ->where(['status' => Coupon::STATUS_ACTIVE])
                        ->orderBy('code', 'desc')
                        ->orderBy('type', 'desc')
                        ->orderBy('id', 'desc')
                        ->with('store')
                        ->take(5)
                        ->get();
        $activeCoupons = [];
        foreach ($listCoupon as $key => $coupon) {
            if (isset($coupon->store->slug) && isset($coupon->store->title) && isset($coupon->store->image)) {
                $listCoupon[$key]->storeSlug = isset($coupon->store->slug) ? $coupon->store->slug : '';
                $listCoupon[$key]->storeTitle = isset($coupon->store->title) ? $coupon->store->title : '';
                $listCoupon[$key]->storeImage = isset($coupon->store->image) ? $coupon->store->image : '';
                $activeCoupons[] = $listCoupon[$key];
            }
        }

        if(empty($category->metaTitle)) {
            $title = Utils::getDefaultMeta('deals', 'metaTitle');
        } else {
            $title = $category->metaTitle;
        }

        $saleStoreIds = \DB::table('store_n_category as sc')
                            ->join('deals as d', 'd.store_id', '=', 'sc.store_id')
                            ->where('sc.category_id', '=', $category->id)
                            ->groupBy('d.store_id')
                            ->pluck('sc.store_id');
        if (empty($listDeals) && !empty($saleStoreIds)) {
            $buildResult = $this->rebuildCategoryDealByStore($saleStoreIds, $category->id);
            if ($buildResult) {
                $dealResult = $this->getDealLists($dealFilters);
                $listDeals = $dealResult['data'];
            }
        }
        $responseResult = \App\Utils\Utils::getInternalRequests('/service/store/find', [
            'status' => 'enable',
            'ids' => join(",", $saleStoreIds),
            'orderBy' => 'couponCountDesc',
            'pageSize' => 15
        ]);
        $stores = NULL;

        if ($responseResult['status'] === 'successful') {
            $stores = $responseResult['result']['data'];
        }
        $similarSaleCate = [];
        $allDealCategories = \DB::table('deal_n_category')
                            ->where('category_id', '<>',$category->id)
                            ->where('category_id', '<>', 0)
                            ->select(\DB::raw('DISTINCT category_id'))
                            ->pluck('category_id');
        if (!empty($allDealCategories)) {
            $similarSaleCate = \DB::table('category as c')
                                ->where('c.depth', $category->depth)
                                ->whereIn('c.id', $allDealCategories)
                                ->select(['c.id', 'c.title', 'c.slug'])
                                ->get();
            $this->countSimilarCateProduct($similarSaleCate);
        }


        view()->share('canonicalLink', $canonicalLink);
        return view('deals-page::deals.list-by-category', [
            'slug' => $slug,
            'listDeals' => $listDeals,
            'currentPage' => $currentPage,
            'hasNextPage' => $hasNextPage,
            'category' => $category,
            'breadcrumbs' => $breadcrumbs,
            'activeCoupons' => $activeCoupons,
            'contentTemplateTitle' => $contentTemplates['title'],
            'contentTemplate' => $contentTemplates['contentTemplate'],
            'dealFilterActivated' => isset($_POST['dealType']) ? $_POST['dealType'] : 'all',
            'title' => \App\Utils\Utils::replaceMonthYeah(str_replace("{text}", $category->title, $title)),
            'topSaleStore' => $stores,
            'similarSaleCate' => $similarSaleCate
        ]);
    }


    /**
     * @return void
     */
    public function buildDealCategory()
    {
        set_time_limit(86400);
        $query = Deal::query()->whereNotNull('category_id')->where('category_id', '<>', '');
        $total = $query->count();
        $perPage = 500;
        $pageCount = ceil($total / $perPage);
        for($p = 0; $p < $pageCount; $p++) {
            $offset = $perPage * $p;
            $data = $query->limit($perPage)->offset($offset)->get(['id', 'category_id']);
            if (!empty($data)) {
                $insertItem = [];
                foreach ($data as $item) {
                    $categoryIds = explode(',', $item->category_id);
                    foreach ($categoryIds as $idItem) {
                        $insertItem[] = [
                            'deal_id' => $item->id,
                            'category_id' => $idItem
                        ];
                    }
                }
                if (!empty($insertItem)) {
                    DealCategory::insert($insertItem);
                }
            }
        }
    }

    /**
     * @param $content
     * @return mixed
     */
    private function getImageFromContent($content) {
        preg_match_all('/(https?:\/\/\S+\.(?:jpg|png|gif))/', $content, $matches);
        return $matches;
    }

    /**
     * @param $slug
     * @return object|null
     */
    protected function getStore($slug)
    {
        $result = NULL;
        $stores = $this->getDataInternalRequests('/service/store/find', ['slug' => $slug,'status' => \App\Models\Store::STATUS_ENABLE]);
        if (!empty($stores)) {
            $result = (object) $stores[0];
        }
        return $result;
    }

    /**
     * @param $store
     * @return void
     */
    protected function getStoreContact(&$store)
    {
        $contact = StoreContact::where('store_id', '=', $store->id)->first();
        if (!empty($contact)) {
            $store->contact = $contact->toArray();
        }
    }

    /**
     * @param $breadcrumbs
     * @return \Illuminate\Database\Query\Builder|mixed|string|null
     */
    protected function getStoreAuthor($breadcrumbs)
    {
        $author = '';
        if (isset($breadcrumbs['category'])){
            $categoryIds = [];
            foreach ($breadcrumbs['category'] as $category ){
                $categoryIds[] = $category['id'];
            }
            $author = DB::table('author')
                ->join('author_n_category','author.id','=','author_n_category.author_id')
                ->where('author.status','=',Author::STATUS_ENABLE)
                ->whereIn('author_n_category.category_id',$categoryIds)->orderBy(DB::raw('RAND()'))->first();
        }
        return $author;
    }

    /**
     * @param $store
     * @param $retVal
     * @param $breadcrumbs
     * @return void
     */
    protected function formatStoreContent(&$store, &$retVal, $breadcrumbs)
    {
        $storeReplacement = "";
        if (isset($categoryIds)){
            $storeReplacement = $this->getStoreForTemplate($categoryIds[sizeof($categoryIds)-1]);
        }

        $categoryTag = '';
        if (isset($breadcrumbs['category']) && !empty($breadcrumbs['category'])){
            $lastCategory = $breadcrumbs['category'][0];
            $href = route('frontend::category::listByCategory',['slug' => $lastCategory['slug']]);
            $categoryTag = "<a href='$href'>" . $lastCategory['title'] . "</a>";
        }
        $trans = [
            '[title]'=>$store->title,
            '[date]'=>Utils::timeOnGoing(date('y-m-d')),
            '[stores]' => $storeReplacement,
            '[category]'=> $categoryTag,
            '[total]'=>$store->couponCount
        ];
        if (empty($store->content)){
            $retVal['contentTemplate'] = $this->getContentTemplate($trans,"store.contentTemplate");
        }else{
            $store->content = strtr($store->content, $trans);
        }
        $store->content = $this->addRelAttribute($store->content);
        $contents = Utils::makeTableOfContents($store->content);
        $retVal['toc'] = $contents['toc'];
        $store->content = $contents['content'];
        if (empty(strip_tags($store->description))){
            $store->description = $this->getContentTemplate($trans,"store.descriptionTemplate");
        }
        $store->content = Utils::replaceMonthYeah($store->content);
        $store->description = Utils::replaceMonthYeah($store->description);
        $configStorePreventedContent = $this->getDataInternalRequests('/service/cfg/find', ['type' => 'store', 'key' => 'store.prevented.content']);
        if($configStorePreventedContent) {
            if(isset($configStorePreventedContent[0]['value'])){
                try {
                    $storeIdPreventedContent = json_decode($configStorePreventedContent[0]['value']);
                    if(array_search($store['id'], $storeIdPreventedContent) !== false){
                        $store->content = '';
                        $retVal['contentTemplate'] = '';
                    }
                } catch (\Throwable $th) {
//                    echo "ERROR json_decode configStorePreventedContent!";
                }
            }
        }
    }

    /**
     * @param $storeId
     * @return mixed
     */
    protected function getListCoupons($storeId)
    {
        $result = NULL;
        $filter = [
            'storeId' => $storeId,
            'status' => Coupon::STATUS_ACTIVE,
            'pageId' => 0,
            'orderBy' => 'sorderAndPinned',
            'pageSize' => 5
        ];
        $result = $this->getInternalRequests('/service/coupon/find', $filter);
        return $result;
    }

    /**
     * @param $filters
     * @return array
     */
    protected function getDealLists($filters = array())
    {
        $perPage = isset($filters['per_page']) ? $filters['per_page'] : 45;
        $pageId = isset($filters['current_page']) ? $filters['current_page'] - 1 : 0;
        $dealFiler = [
//            'status' => Deal::STATUS_ACTIVE,
            'orderBy' => 'typeDesc',
            'pageId' => $pageId,
            'pageSize' => $perPage
        ];
        if (isset($filters['dealType'])) {
            if ($filters['dealType'] === 'code') {
//                $dealFiler['codeNotNull'] = true;
                $dealFiler['order_by'] = 'price::ASC';
            }
            if ($filters['dealType'] === 'offer') {
                $dealFiler['order_by'] = 'discount::DESC';
            }
            if ($filters['dealType'] === 'newest') {
                $dealFiler['order_by'] = 'id::DESC';
            }

            if ($filters['dealType'] === 'price') {
                if ((double) $filters['minPrice'] > 0) {
                    $dealFiler['priceFrom'] = (double) $filters['minPrice'];
                }
                if ((double) $filters['maxPrice'] > 0) {
                    $dealFiler['priceTo'] = (double) $filters['maxPrice'];
                }
                view()->share('priceRange', [$filters['minPrice'], $filters['maxPrice']]);
            }

        }
        if (isset($filters['store_id'])) {
            $dealFiler['storeId'] = $filters['store_id'];
        }
        if (isset($filters['category_id'])) {
            $dealFiler['categoryId'] = $filters['category_id'];
        }
        $getDeals = $this->dealRepository->read($dealFiler);
        $totalDeal = 0;
        $dealResult = [];
        if (isset($getDeals['data']) && count($getDeals['data']) > 0) {
            $dealResult = $getDeals['data'];
            unset($dealFiler['pageId']);
            unset($dealFiler['pageSize']);
            $dealFiler['metrics'] = 'count';
            $getTotal = $this->dealRepository->read($dealFiler);
            if (isset($getTotal['data'])) {
                $totalDeal = $getTotal['data'];
            }
        }

        $result = [
            'per_page' => $perPage,
            'page_count' => ceil($totalDeal / $perPage),
            'current_page' => $pageId + 1,
            'data' => $dealResult,
            'total_deal' => $totalDeal
        ];
        return $result;
    }

    /**
     * @param $content
     * @return mixed
     */
    private function addRelAttribute ($content)
    {
        if($content) {
            $cdnUrl = \App\Utils\Utils::getCdnUrl(0, 0);
            $blankSrc = ' src="/images/blank.gif" ';

            // $content = preg_replace("/<a((?!.*rel=[\"']).*)href=\"(https:\/\/|http:\/\/|\/\/)(?!couponforless)(.*?)\"(.*?)>(.*?)<\/a>/", "<a$1href=\"$2$3\" rel=\"nofollow noopener\" $4>$5</a>", $content, -1);
            $content = preg_replace('/(<img (.*)src=")(.*?)(".*>)/', "$1$cdnUrl$3$4", $content);
            $content = preg_replace('/(<img (.*)class=")(.*?)(".*>)/', '$1lazy $2$3$4', $content);
            $content = str_replace("src", "data-src", $content);
            $content = preg_replace('/(<img (.*)src=".*?")(.*\/>)/', "$1$blankSrc$3$4", $content);
            $content = preg_replace('/(<iframe\s+.*?\s*)(data-src)(=".*?".*?<\/iframe>)/', "$1src$3", $content);

            $doc = new \DOMDocument();
            @$doc->loadHTML($content);
            $elements = $doc->getElementsByTagName('a');

            foreach($elements as $el) {
                if(strpos($el->getAttribute('href'), 'https://couponforless.com') === false)
                    $el->setAttribute('rel', 'nofollow noopener');
            }

            $elements = $doc->getElementsByTagName('img');
            foreach($elements as $el) {
                if(strpos($el->getAttribute('data-src'), 'https://couponforless.com') !== false){
                    $dataSrcUrl = str_replace('https://couponforless.com', 'couponforless.com',$el->getAttribute('data-src'));
                    $el->setAttribute('data-src', $dataSrcUrl);
                }elseif(strpos($el->getAttribute('data-src'), 'http://couponforless.com') !== false){
                    $dataSrcUrl = str_replace('http://couponforless.com', 'couponforless.com',$el->getAttribute('data-src'));
                    $el->setAttribute('data-src', $dataSrcUrl);
                }
            }

            $content = $doc->saveHTML();
        }

        return $content;
    }

    /**
     * @param $storeId
     * @return array
     */
    private function _getStoreEmbedCoupons ($storeId) {
        $storeEmbed = StoreEmbed::with([
            'coupons' => function ($query) {
                $query->orderBy('embed_n_coupon.sorder', 'DESC');
            },
            'store' => function ($query) {
                $query->select('id', 'title', 'slug');
            }
        ])->where('store_id', '=', $storeId)->first();
        if (!empty($storeEmbed)) {
            $storeEmbed = $storeEmbed->toArray();
            $couponEmbeds = [];
            if (!empty($storeEmbed['coupons'])) {
                foreach ($storeEmbed['coupons'] as $coupon) {
                    $couponEmbeds[] = $coupon['id'];
                }
            }
            if (!empty($couponEmbeds)) {
                $couponEmbeds = $this->getDataInternalRequests('/service/coupon/find', [
                    'couponIn' => $couponEmbeds,
                    'status' => Coupon::STATUS_ACTIVE
                ]);
                $storeEmbed['coupons'] = $couponEmbeds;
            }
            return $storeEmbed;
        } else {
            return [];
        }
    }

    /**
     * @param $similarCates
     * @return void
     */
    private function countSimilarCateProduct(&$similarCates)
    {
        if (!empty($similarCates)) {
            $cateIds = [];
            foreach ($similarCates as $item) {
                $cateIds[] = $item->id;
            }
            $countProducts = Deal::from('deals as d')
                            ->join('deal_n_category as dc', 'dc.deal_id', '=', 'd.id')
                            ->whereIn('dc.category_id', $cateIds)
                            ->where('d.status', Deal::STATUS_ACTIVE)
                            ->groupBy('dc.category_id')
                            ->select(['dc.category_id', DB::raw('count(`d`.`id`) as active_total')])
                            ->pluck('active_total', 'category_id');
            if (!empty($countProducts)) {
                foreach ($similarCates as &$item) {
                    if (isset($countProducts[$item->id])) {
                        $item->total = $countProducts[$item->id];
                    }
                }
            }
        }
    }

    /**
     * @param $currentDeal
     * @return array
     */
    private function getOtherDealsOfStore($currentDeal)
    {
        $retVal = [];
        $totalDeals = 0;
        if (isset($currentDeal->store_id)) {
            $storeId = $currentDeal->store_id;
            $query = Deal::from('deals')
                    ->where('status', Deal::STATUS_ACTIVE)
                    ->where('store_id', $storeId);
            $totalDeals = (clone $query)->count();
            $deals = $query->limit(15)
                    ->get(['id', 'title', 'slug', 'image', 'price', 'sale_price', 'discount', 'expire_time']);
            if (!empty($deals)) {
                $retVal = $deals;
            }
        }
        view()->share('storeTotalDeals', $totalDeals);
        return $retVal;
    }

    /**
     * @param $categoryId
     * @return void
     */
    private function getRelatedDeal($dealId)
    {
        $retVal = [];
        $totalDeal = 0;
        $cacheKey = 'totalRelatedDeal::' . $dealId;
        $category = DealCategory::where('deal_id', $dealId)->first(['category_id']);
        if (!empty($category)) {
            $queryDealNCate = DealCategory::where('category_id', $category->category_id)
                                ->where('deal_id', '<>', $dealId);
            if (\Cache::has($cacheKey)) {
                $totalDeal = \Cache::get($cacheKey);
            } else {
                
                $totalDeal = $queryDealNCate->count();
                \Cache::put($cacheKey, $totalDeal, 24 * 60);
            }
            if ($totalDeal > 0) {
                $getDealNCate = $queryDealNCate->orderBy('deal_id', 'DESC')
                            ->limit(30)
                            ->pluck('deal_id');
                if (!empty($getDealNCate)) {
                    $getDealNCate = $getDealNCate->toArray();
                    $deals = Deal::whereIn('id', $getDealNCate)
                            ->where('status', Deal::STATUS_ACTIVE)
                            ->limit(15)
                            ->get(['id', 'title', 'slug', 'image', 'price', 'sale_price', 'discount', 'expire_time']);
                    if (!empty($deals)) {
                        $retVal = $deals;
                    }
                } 
            }  
        }
        view()->share('totalRelatedDeal', $totalDeal);
        return $retVal;
    }

    /**
     * @param $dealId
     * @return array
     */
    private function getRelatedCateDeal($dealId)
    {
        $retVal = [];
        $defaultImage = 'vendor/deals-page/images/category-default-logo.png';
        $totalDeal = 0;
        $parentCategory = Category::from('category as c')
                    ->join('deal_n_category as dc', 'dc.category_id', '=', 'c.id')
                    ->where('dc.deal_id', $dealId)
                    ->first(['c.parent_id']);
        if (!empty($parentCategory)) {
            $parentCateId = $parentCategory->parent_id;
            $categoryHasDeal = Category::from('category as c')
                                ->join('deal_n_category as dc', 'dc.category_id', '=', 'c.id')
                                ->where('c.parent_id', $parentCateId)
                                ->groupBy('dc.category_id')
                                ->limit(15)
                                ->get(['c.id', 'c.title', 'c.slug', 'c.image']);
            
            if (!empty($categoryHasDeal)) { 
                foreach ($categoryHasDeal as &$item) {
                    if (empty($item->image)) {
                        $item->image = $defaultImage;
                    } else if (!empty($item->image) && !file_exists(public_path($item->image))) {
                        $item->image = $defaultImage;
                    }
                }
                $retVal = $categoryHasDeal;
            }
        }
        return $retVal;
    }

    /**
     * @param $dealId
     * @return array
     */
    private function relatedStoreReview($dealId)
    {
        $retVal = [];
        $totalDeal = 0;
        $category = DealCategory::where('deal_id', $dealId)
            ->first(['category_id']);
        if (!empty($category)) {
            $storeByCate = StoreCategory::from('store_n_category as s')
                    ->where('s.category_id', $category->category_id)
                    ->pluck('s.store_id');
            if (!empty($storeByCate)) {
                $storeHasReviews = Store::from('store as s')
                                    ->join('store_reviews as sr', 'sr.store_id', '=', 's.id')
                                    ->whereIn('sr.store_id', $storeByCate->toArray())
                                    ->limit(15)
                                    ->groupBy('sr.store_id')
                                    ->get(['s.id', 's.title', 's.slug', 's.image', 's.vote_up', 's.vote_down']);

                if (!empty($storeHasReviews)) {
                    $retVal = $storeHasReviews;
                }
            }
        }

        return $retVal;
    }

    /**
     * @param $storeId
     * @return null
     */
    private function getRelatedStoreDeal($storeId)
    {
        $retVal = NULL;
        $categoryId = StoreCategory::query()->where('store_id', $storeId)->first(['category_id']);
        if (!empty($categoryId)) {
            $storeIds = StoreCategory::query()
                    ->where('store_id', '<>', $storeId)
                    ->where('category_id', $categoryId->category_id)
                    ->pluck('store_id');
            if (!empty($storeIds)) {
                $storeIds = $storeIds->toArray();
                $defaultField = array("id", "title", "slug", "type", "description", "sorder", "image as coverImage",
                    "auto_text as autoText", "website as websiteUrl", "affiliate_link as affiliateUrl", "origin_link as originUrl",
                    "related_store as relatedTerms", "status", "vote_up as voteUp", "vote_down as voteDown", "meta_title as metaTitle",
                    "meta_description as metaDescription", "meta_keywords as metaKeywords", "views", "clicks", "creator_id as creatorId",
                    "create_name as creatorName", "modifier_id as modifierId", "modifier_name as modifierName", "create_time as createTime",
                    "update_time as modifyTime", "coupon_count as couponCount", "inactive_affiliate as isInactiveAffiliate","content",
                    "config", "crawl_rating", "crawl_rating_count");
                $store = Store::has('deals')
                        ->whereIn('id', $storeIds)
                        ->get($defaultField);

                if (!empty($store)) {
                    $retVal = $store->toArray();
                }
            }
        }
        return $retVal;
    }

    /**
     * @param $storeIds
     * @param $categoryId
     * @return bool
     */
    private function rebuildCategoryDealByStore($storeIds, $categoryId)
    {
        $retVal = false;
        try {
            $dealIds = Deal::where('status', Deal::STATUS_ACTIVE)
                ->whereIn('store_id', $storeIds)
//                ->limit(100)
                ->pluck('id');

            $insertData = [];
            if (!empty($dealIds)) {
                foreach ($dealIds->toArray() as $item) {
                    $insertData[] = [
                        'category_id' => $categoryId,
                        'deal_id' => $item
                    ];
                }
            }
            if (count($insertData) > 0) {
                DealCategory::insert($insertData);
                $retVal = true;
            }
        } catch (\Exception $ex) {
            \Log::error('BUILD_CATEGORY_DEAL: ' . $ex->getMessage() . ' Line ' . $ex->getLine() . '. File ' . $ex->getFile());
        }
        return $retVal;
    }

    /**
     * @param $storeId
     * @return null
     */
    private function getRelatedCategoryDealById($storeId)
    {
        $retVal = NULL;
        $categoryId = StoreCategory::query()->where('store_id', $storeId)->first(['category_id']);
        $relatedCategory = Category::from('category as c')
                            ->join('deal_n_category as dc', 'dc.category_id', '=', 'c.id')
                            ->join('category as c1', 'c1.parent_id', '=', 'c.parent_id')
                            ->where('c1.id', $categoryId->category_id)
                            ->where('c.status', Category::STATUS_ENABLE)
                            ->groupBy('dc.category_id')
                            ->get(['c.id', 'c.title', 'c.slug']);
        if (!empty($relatedCategory)) {
            $retVal = $relatedCategory->toArray();
        }
        return $retVal;
    }
}
