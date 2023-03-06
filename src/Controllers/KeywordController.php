<?php

namespace Megaads\DealsPage\Controllers;

use App\Models\Config as AppConfig;
use App\Utils\Utils;
use Carbon\Carbon;
use Firebase\JWT\Key;
use Megaads\DealsPage\Models\Category;
use Megaads\DealsPage\Models\Config;
use Megaads\DealsPage\Models\Coupon;
use Megaads\DealsPage\Models\DealRelation;
use Megaads\DealsPage\Models\Store;
use Megaads\DealsPage\Models\StoreCategory;
use Megaads\DealsPage\Models\StoreContact;
use View;
use Megaads\DealsPage\Models\Deal;
use App\Http\Controllers\Controller;
use Megaads\DealsPage\Models\Keypage;
use Megaads\DealsPage\Repositories\DealRepository;

class KeywordController extends Controller {

    const POPULAR_STORE_CACHE = 'deal_popular_store';
    const POPULAR_CATEGORY_CACHE = 'deal_popular_category';
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    private $totalFilterResult;
    private $dealRepository;

    public function __construct() {
        parent::__construct();
        $this->totalFilterResult = 0;
        $this->numberOfResult = 45;
        $this->dealRepository = new DealRepository();
    }

    public function index($slug, $itemId = 0) {
        $page = \Request::get('page', 0);

        $retVal = [];
        $total = 0;
        $keyword = Keypage::query()->where('slug', $slug)->first();
        if (empty($keyword)) {
            return abort(404);
        }
        $storeId = -1;
        $relationIds = DealRelation::query()->where('target_id', $keyword->id)->pluck('object_id');
        $store = NULL;
        if (!empty($keyword->store_id)) {
            $storeId = $keyword->store_id;
            $store = Store::query()->where('id', $storeId)->first(
                [
                    'id',
                    'title',
                    'slug',
                    'image as coverImage',
                    'origin_link as originUrl',
                    'affiliate_link as affiliateUrl',
                    'vote_up as voteUp',
                    'vote_down as voteDown',
                    'content'
                ]);
        }

        if($page - 1 <= 0) {
            $pagination = 0;
            $paginationString = "";
        } else {
            return response()->redirectToRoute("frontend::keyword", ['slug' => $slug], 301);
        }

        $canonicalLink = route('frontend::keyword', $slug);

        View::share('canonicalLink', $canonicalLink);

        if (isset($store) && !empty($store)) {
            $contact = StoreContact::where('store_id', '=', $store->id)->first();
            if (!empty($contact)) {
                $retVal['storeContact'] = $contact;
            }
            $retVal['storeItem'] = $store;
        }
        $dealFiler = [
            'status' => Deal::STATUS_ACTIVE,
            'orderBy' => 'typeDesc',
            'pageId' => $pagination,
            'pageSize' => $this->numberOfResult
        ];
        $filterActivated = 'all';
        if (\Request::isMethod('POST')) {
            $allParams = \Request::all();
            if (isset($allParams['dealType'])) {
                if ($allParams['dealType'] === 'code') {
                    $dealFiler['codeNotNull'] = true;
                    $filterActivated = 'code';
                }
                if ($allParams['dealType'] === 'offer') {
                    $dealFiler['orderBy'] = 'price_DESC';
                    $filterActivated = 'offer';
                }
                if ($allParams['dealType'] === 'newest') {
                    $dealFiler['orderBy'] = 'id_DESC';
                    $filterActivated = 'newest';
                }

                if ($allParams['dealType'] === 'price') {
                    $dealFiler['priceFrom'] = $allParams['minPrice'];
                    $dealFiler['priceTo'] = $allParams['maxPrice'];
                    $filterActivated = 'price';
                    View::share('priceRange', [$allParams['minPrice'], $allParams['maxPrice']]);
                }
            }
        }
        View::share('dealFilterActivated', $filterActivated);
        if (!empty($keyword['deal_filter'])) {
            $dealFiler['advSearch'] = trim($keyword['deal_filter']);
        } else if (!empty($keyword['store_id'])) {
            $dealFiler['storeId'] = $keyword['store_id'];
        } else if (!empty($relationIds)) {
            $dealFiler['ids'] = $relationIds->toArray();
        } else {
            $dealFiler['like_title'] = str_replace("%","\%",$keyword['keyword']);
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
            if (isset($getDeals['data'])) {
                $totalDeal = $getDeals['data'];
            }
        }
        $retVal['keyword'] = $keyword;
        $defaultMetaTitle = getDefaultMeta('keyword', 'metaTitle');
        $defaultMetaTitle = str_replace("{text}", $keyword['keyword'], $defaultMetaTitle);
        $retVal['title'] = replaceMonthYear($defaultMetaTitle);
        $defaultMeta['title'] = $retVal['title'];
        $retVal['slug'] = $slug;
        $retVal['listDeal'] = $dealResult;
        $retVal['meta'] = ['title' => $keyword['keyword']];
        $retVal['total'] = $totalDeal;
        $this->getRecommendedCoupon($retVal, $keyword);
        $this->getRelatedStores($retVal, $keyword['store_id']);
        $this->getRelatedCategories($retVal,$keyword['store_id']);
        $this->getSimilarSearch($retVal, $keyword);
        $this->contentTemplate($retVal, $keyword);
        $this->getTodayDeals($retVal, $keyword);

        return response()->make(view('deals-page::keypage.index', $retVal));
    }

    /**
     * @param $retVal
     * @return void
     */
    protected function getRecommendedCoupon(&$retVal, $keyword) {
        $query = Coupon::query();
        $query->where('coupon.status', Coupon::STATUS_ACTIVE);
        $query->join('store', 'store.id', '=', 'coupon.store_id');
        if (isset($keyword['filter']) && !empty($keyword['filter'])) {
            $strQuery = explode('|', $keyword['filter']);
            preg_match('/(\w+)(\+|\-)(.*)/i', $strQuery[0], $matches);
            foreach ($strQuery as $item) {
                preg_match('/(\w+)(\+|\-)(.*)/i', $item, $matches);
                if ($matches) {
                    $field = $matches[1];
                    $operation = $matches[2];
                    if ($operation == '+') {
                        $operation = 'LIKE';
                    } else if ($operation == '-') {
                        $operation = 'NOT LIKE';
                    }
                    $value = '%' . $matches[3] . '%';
                    $query->where($field, $operation, $value);
                }
            }
        }
        if (empty($keyword['store_id'])) {
            $query->limit(15);
        } else {
            $query->where('store_id', $keyword['store_id']);
        }
        $getCoupon = $query->get(['coupon.*', 'store.image as storeImage']);
        if (count($getCoupon) > 0) {
            $retVal['recommendedCoupons'] = $getCoupon;
        }
    }

    /**
     * @param $retVal
     * @param $storeId
     * @return void
     */
    protected function getRelatedStores(&$retVal, $storeId = NULL)
    {
        if (!empty($storeId)) {
            $findCate = StoreCategory::query()->where('store_id', $storeId)->first(['category_id']);
            if (!empty($findCate)) {
                $categoryId = $findCate->category_id;
                $storeIds = StoreCategory::query()->where('category_id', $categoryId)->pluck('store_id');
                $retVal['relatedStore'] = Store::query()->whereIn('id', $storeIds)->get(['id', 'title', 'slug', 'image as coverImage']);
            }
        } else {
            $retVal['relatedStore'] = $this->getPopularStore();
        }
    }

    /**
     * @param $retVal
     * @param $storeId
     * @return void
     */
    protected function getRelatedCategories(&$retVal, $storeId = NULL)
    {
        if (!empty($storeId)) {
            $findCate = StoreCategory::query()
                                ->with(['category' => function($q) {
                                    $q->select(['id', 'title', 'slug', 'parent_id']);
                                }])
                                ->where('store_id', $storeId)
                                ->first();
            if (!empty($findCate) && isset($findCate->category)) {
                $category = $findCate->category;
                $findRelatedCate = Category::query()
                    ->where('parent_id', $category->parent_id)
                    ->select(['id', 'title', 'slug'])
                    ->get();
                $retVal['relatedCategory'] = $findRelatedCate;
            }
        } else {
            $retVal['relatedCategory'] = $this->getPopularCategory();
        }
    }

    /**
     * @param $retVal
     * @param $keyword
     * @return void
     */
    protected function getSimilarSearch(&$retVal, $keyword)
    {
        $similarSearch = [];
        if (isset($keyword['similar_box']) && !empty($keyword['similar_box'])) {
            $similarSearchObj = json_decode($keyword['similar_box'], true);
            $similarSearchData = $similarSearchObj['data'];
            foreach ($similarSearchData as $slug => $value) {
                $buildItem = [
                    'keyword' => $value,
                    'slug' =>  $slug
                ];
                $buildItem = (object) $buildItem;
                array_push($similarSearch, $buildItem);
            }
            $retVal['similarSearch'] = $similarSearch;
        }
        if (count($similarSearch) <= 0) {
            $retVal['similarSearch'] = $this->popularSearch($keyword['store_id']);
        }
    }

    /**
     * @return mixed|null
     */
    private function getPopularStore() {
        $dealPopularStore = NULL;
        if (\Cache::has(self::POPULAR_STORE_CACHE)) {
            $dealPopularStore = \Cache::get(self::POPULAR_STORE_CACHE);
        } else {
            $getConfig = Config::whereRaw("`key` = 'site.popularStores'")->first(['value']);
            if (!empty($getConfig)) {
                $objectValue = json_decode($getConfig);
                $objectValue = json_decode($objectValue->value);
                $stores = Store::query()->whereIn('id', $objectValue->ids)->get(['id', 'title', 'slug', 'image']);

                if (count($stores) > 0) {
                    $expiredAt = Carbon::now()->addHour(1);
                    \Cache::put(self::POPULAR_STORE_CACHE, $stores, $expiredAt);
                    $dealPopularStore = $stores;
                } else {
                    $stores = Store::query()
                                    ->where('status', 'enable')
                                    ->orderBy('coupon_count', 'DESC')
                                    ->limit(20)
                                    ->get(['id', 'title', 'slug', 'image']);
                    if (count($stores) > 0) {
                        $expiredAt = Carbon::now()->addHour(1);
                        \Cache::put(self::POPULAR_STORE_CACHE, $stores, $expiredAt);
                        $dealPopularStore = $stores;
                    }
                }
            }
        }
        return $dealPopularStore;
    }


    /**
     * @return mixed|null
     */
    protected function getPopularCategory()
    {
        $dealPopularCategory = NULL;
        if (\Cache::has(self::POPULAR_CATEGORY_CACHE)) {
            $dealPopularCategory = \Cache::get(self::POPULAR_CATEGORY_CACHE);
        } else {
            $getConfig = Config::whereRaw("`key` = 'site.popularCategories'")->first(['value']);
            if (!empty($getConfig)) {
                $objectValue = json_decode($getConfig);
                $objectValue = json_decode($objectValue->value);
                $categories = Category::query()->whereIn('id', $objectValue->ids)->get(['id', 'title', 'slug', 'image']);
                if (count($categories) > 0) {
                    $expiredAt = Carbon::now()->addHour(1);
                    \Cache::put(self::POPULAR_CATEGORY_CACHE, $categories, $expiredAt);
                    $dealPopularCategory = $categories;
                } else {
                    $categories = Store::query()
                        ->where('status', 'enable')
                        ->orderBy('coupon_count', 'DESC')
                        ->limit(20)
                        ->get(['id', 'title', 'slug', 'image']);
                    if (count($categories) > 0) {
                        $expiredAt = Carbon::now()->addHour(1);
                        \Cache::put(self::POPULAR_CATEGORY_CACHE, $categories, $expiredAt);
                        $dealPopularCategory = $categories;
                    }
                }
            }
        }
        return $dealPopularCategory;
    }


    /**
     * @param $storeId
     * @param $limit
     * @param $isRandom
     * @param $ignoreIds
     * @return mixed
     */
    private function popularSearch($storeId, $limit = 10, $isRandom = false, $ignoreIds = [])
    {
        $query = Keypage::query();

        $keywords = [ "reddit", "minimum", "family", "entire", "order", "what", "where", "when", "how", "can", "off" ];
        foreach ($keywords as $keyword) {
            $query->where('keyword', 'NOT LIKE', "%$keyword%");
        }

        if (!empty($storeId)) {
            $query->where('store_id', '=', $storeId);
        }
        if ($isRandom) {
            $query->orderBy(\DB::raw('RAND()'));
        }
        $query->where('visibility', '=', Keypage::VISIBLE);
        if (!empty($ignoreIds)){
            $query->whereNotIn('id', $ignoreIds);
        }
        return $query->limit($limit)->get();
    }

    /**
     * @param $keyword
     * @param $countEachType
     * @param $total
     * @return void
     */
    private function contentTemplate(&$retVal, $keyword, $store = NULL, $countEachType = '', $total = '') {
        $contentTemplateTrans = $this->getContentTemplateTrans($keyword['keyword'], $countEachType, $total);
        $contentTemplateTrans['[title]'] = isset($store) ? $store['title'] : '';
        $retVal['contentTemplate'] = $this->getContentTemplate(
            $contentTemplateTrans,
            "keypage.contentTemplate"
        );
        if (!empty(trim(strip_tags($keyword['content'])))){
            $keyword['content'] =  strtr($keyword['content'],$contentTemplateTrans);
            $keyword['content'] =  replaceMonthYear($keyword['content']);
        }else{
            $retVal['contentTemplateFAQ'] = $this->getContentTemplate(
                $contentTemplateTrans,
                "keypage.contentTemplateFAQ"
            );
            $retVal['contentTemplateFAQ'] = $this->parseContentTemplateFAQ($retVal['contentTemplateFAQ']);
            $retVal['contentTemplateFAQ'] = replaceMonthYear($retVal['contentTemplateFAQ']);
        }
    }

    /**
     * @param $keyword
     * @param $countEachType
     * @param $total
     * @return array
     */
    private function getContentTemplateTrans($keyword, $countEachType, $total) {
        return [
            '[month]' => date('F'),
            '[year]' => date('Y'),
            '[keypage]' => $keyword,
            '[deal]' => $total,
            '[code]' => '', //$countEachType[\App\Models\Coupon::TYPE_COUPON],
            '[printable]' => ''//$countEachType['printable'],
        ];
    }

    /**
     * @param array $trans
     * @param $key
     * @param $withTitle
     * @return array|string
     */
    protected function getContentTemplate(Array $trans, $key = "category.contentTemplate", $withTitle = false){
        $contentTemplate = Config::query()->where('key', $key)->first(['value']);
        $title = '';
        if (!empty($contentTemplate)){
            $contentTemplate = json_decode($contentTemplate->value);
            if (isset($contentTemplate->status) && $contentTemplate->status == 'enable') {
                $title = isset($contentTemplate->title)?$contentTemplate->title:"";
                $contentTemplate = strtr($contentTemplate->data,$trans);
            } else {
                $contentTemplate = '';
            }
        }
        $contentTemplate = replaceMonthYear($contentTemplate);
        if ($withTitle){
            return [
                'title' => $title,
                'contentTemplate' => $contentTemplate
            ];
        }
        return $contentTemplate;
    }

    /**
     * @param $content
     * @return string
     */
    private function parseContentTemplateFAQ($content) {
        $content = preg_replace("/<strong>([^\<\/]*?)<\/strong><br \/>/m", '<div class="faq-title"><strong>$1</strong></div>', $content);
        $content = preg_replace("/<p([^>]+)><strong>([^\<\/]*?)<\/strong><\/p>/m", '<div class="faq-title"><strong>$2</strong></div><br />', $content);
        $content = preg_replace(
            '/<div class="faq-title"><strong>([^\<\/]*?)<\/strong><\/div>/m',
            '<div class="faq-title"><strong>$1</strong></div><div class="faq-content">',
            $content
        );
        $svg = '<svg focusable="false" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6 1.41-1.41z"></path></svg>';
        $retVal = '';
        $arr = explode('<div class="faq-title"><strong>', $content);
        foreach ($arr as $key => $value) {
            if ($key > 0) {
                $value = '<div class="faq-title">' . $svg . '<strong>' . $value;
            }
            $retVal .= $value . ($key > 0 ? '</div>' : '');
        }

        return $retVal;
    }

    private function getTodayDeals(&$retVal, $keyword)
    {
        $dealFilters = [
            'pageSize' => 15,
            'orderBy' => 'id_DESC',
            'columns' => ['id', 'title', 'slug']
        ];
        if (isset($keyword['store_id'])) {
            $dealFilters['storeId'] = $keyword['store_id'];
        }
        $result = $this->dealRepository->read($dealFilters);
        if (count($result['data']) > 0) {
            $retVal['todayDeals'] = $result['data'];
        }
    }
    /**
     * =================================================================================================================
     *
     *
     *
     * =================================================================================================================
     */



    /** OLD FUNCITON */
    public function asyncFindKeywords(\Illuminate\Http\Request $request) {
        $retVal = [
            'status' => 'fail',
            'render_result' => ''
        ];

        $idKeyword = $request->input('id');
        $offset = $request->input('offset');
        $limitCouponCrawl = 100 - $offset;
        $slug = $request->input('slug');

        $storeItem = null;

        if($slug) {
            $keywords = $this->getDataInternalRequests('/service/store-keyword', ['slug' => $slug,'visibility' => StoreKeyword::VISIBLE]);

            if (empty($keywords)) {
                return $retVal;
            }
            $keyword = $keywords[0];
            if (!empty($keyword['store_id'])) {
                $stores = [$keyword['store']];
                if(count($stores))
                    $storeItem = $stores[0];
            }
        }

        $result = KeywordResult::where('keyword_id','=', $idKeyword)
            ->whereNotNull('description')
            ->where('status','=',KeywordResult::STATUS_ENABLE)
            ->groupBy('domain')
            ->orderBy('updated_at', 'DESC')
            ->skip($offset)
            ->take($limitCouponCrawl)
            ->get();

        // $retVal['result'] = $result;
        $retVal['status'] = 'successful';
        foreach ($result as $item) {
            $retVal['render_result'] .= view('frontend.common.item-keyword', [
                'item' => $item, 'storeItem' => $storeItem
            ])->render();
        }

        return $retVal['render_result'];

    }

    public function asyncFind(\Illuminate\Http\Request $request){
        $this->totalFilterResult = 0;
        $slug = $request->input('slug','none');
        $filter = $request->input('filter');
        $types = $request->input('types');
        $other = $request->input('other');
        $today = $request->input('today', false);
        if (!empty($types)){
            $types = explode(",",$types);
        }else{
            $types = [strtolower(Coupon::TYPE_COUPONCODE),strtolower(Coupon::TYPE_COUPON)];
        }
        $retVal = [
            "result" => [],
            "message" => ""
        ];
        $keywords = $this->getDataInternalRequests('/service/store-keyword', ['slug' => $slug,'visibility' => StoreKeyword::VISIBLE]);
        if (empty($keywords)) {
            $retVal["message"] = "Keyword not found";
        }else{
            $keyword = $keywords[0];
            if (!empty($keyword['store_id'])) {
                $stores = $this->getDataInternalRequests('/service/store/find', ['id' => $keyword['store_id'], 'status' => Store::STATUS_ENABLE]);
            }
            if (isset($stores) && !empty($stores)) {
                $store = $stores[0];
            } else {
                $params = [
                    'status' => Coupon::STATUS_UNRELIABLE,
                    'types' => $types,
                    'orderBy' => 'typeDesc',
                    'pageId' => 0,
                    'pageSize' => 5
                ];
                if (!empty($keyword['filter'])) {
                    $params['titles'] = trim($keyword['filter']);
                } else {
                    $params['title'] = str_replace("%","\%", $keyword['keyword']);
                }
                $unreliable = $this->getInternalRequests('/service/coupon/find', $params);
            }
            $couponFilter = [
                'status' => Coupon::STATUS_ACTIVE,
                'types' => $types,
                'orderBy' => 'typeDesc',
                'pageId' => 0,
                'pageSize' => $this->numberOfResult
            ];
            if (isset($store)){
                $couponFilter['storeId'] = $store['id'];
            } else {
                $couponFilter['pageSize'] = 55;
                if (!empty($keyword['filter'])) {
                    $couponFilter['titles'] = trim($keyword['filter']);
                } else {
                    $couponFilter['title'] = str_replace("%","\%",$keyword['keyword']);
                }

            }
            if (!empty($filter)){
                $couponFilter['regexTitle'] = $filter;
            }
            if ($other) {
                $couponFilter['otherRegexTitle'] = $other;
            }
            if ($today) {
                $couponFilter['todayCoupon'] = true;
            }
            $couponResult = $this->getInternalRequests('/service/coupon/find', $couponFilter);
            $activeCoupons = $this->renderCouponItem($couponResult['result']['data'],'frontend.common.item',null, true);
            if (isset($unreliable)){
                $retVal['result']['unreliableCoupons'] = $this->renderCouponItem($unreliable['result']['data']);
            }
            $retVal['result']['activeCoupons'] = $activeCoupons;
            $retVal['result']['total'] = $this->totalFilterResult;
            return response()->json($retVal);

        }
    }

    private function renderCouponItem($coupons,$view = 'frontend.common.item',$store = null, $verifyText = false, $keyword = null){
        $retVal = '';
        foreach ($coupons as $item){
            $this->totalFilterResult ++;
            $retVal .= view($view,['item' => $item,'storeItem' => $store, 'verifyText' => true, 'keyword' => $keyword])->render();
        }
        return $retVal;
    }

    public function sitemap() {
        $title = 'Search Everything by Keywords on CouponForLess';
        $keypageAlphabet = ["0-9"=>[]];
        $alphabet = range('A', 'Z');
        array_unshift($alphabet, "0-9");
        $keypages = StoreKeyword::where('visibility', StoreKeyword::VISIBLE)->select(['id', 'keyword', 'slug'])->get()->toArray();

        foreach ($keypages as $item) {
            $item = (object) $item;
            $title = $item->keyword;
            $char = strtoupper(substr(trim($title), 0,1));
            if (in_array($char, $alphabet)) {
                if (isset($keypageAlphabet[$char])) {
                    $keypageAlphabet[$char][] = $item;
                }else{
                    $keypageAlphabet[$char] = [$item];
                }
            }else{
                $keypageAlphabet["0-9"][] = $item;
            }
        }
        return view('frontend.keyword.page')->with(compact('title', 'alphabet', 'keypageAlphabet'));
    }

}
