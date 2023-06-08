<?php

namespace Megaads\DealsPage\Controllers;

use App\Http\Controllers\Controller;
use Megaads\DealsPage\Models\Deal;
use Megaads\DealsPage\Models\Store;
use Megaads\DealsPage\Models\DealRelation;
use App\Utils\Utils;
use Illuminate\Support\Facades\Input;
use Megaads\DealsPage\Repositories\DealRepository;
use PHPExcel_Cell;
use PHPExcel_IOFactory;

class DealsController extends Controller {

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    private $dealPageTable;
    private $dealPageColumns;
    protected $dealRepository;

    public function __construct() {
        parent::__construct();
        $this->dealPageTable = \Config::get('deals-page.deal_related_page.name', 'store_n_keyword');
        $this->dealPageColumns = \Config::get('deals-page.deal_related_page.name', ['id', 'keyword']);
        $this->dealRepository = new DealRepository();
        view()->share('allDealTitle', 'All Deals');
    }

    public function index($slug) {
        return redirect()->route('frontend::keyword', ['slug' => $slug], 301);
        $relationPage = \DB::table($this->dealPageTable)->where('slug', $slug)->first($this->dealPageColumns);
        if (empty($relationPage)) {
            abort(404);
        }
        $dealIds = DealRelation::where('target_id', $relationPage->id)->pluck('object_id');
        if(count($dealIds) <= 0) {
            abort(404);
        }
        $retVal = [];
        $deals = Deal::with(['store', 'categories'])
            ->whereIn('id', $dealIds)
            ->get(['id', 'title', 'slug', 'content', 'meta_title', 'meta_description', 'meta_keywords', 'price', 'store_id', 'sale_price',
                'image', 'currency', 'create_time', 'expire_time', 'affiliate_link', 'origin_link', 'discount']);

        $retVal['deals'] = $deals;
        $retVal['page'] = $relationPage;
        $retVal['meta'] = ['title' => $relationPage->keyword];
        $retVal['title'] = $relationPage->keyword;
        return \View::make('deals-page::deals.index', $retVal);
    }


    public function allDeals() {
        return redirect('/');
        $retVal = [];
        $dealFilter = [
            'columns' => ['id', 'title', 'slug',
                'image', 'content', 'price',
                'sale_price', 'discount', 'store_id',
                'expire_time', 'origin_link', 'affiliate_link',
                'create_time', 'modifier_name', 'modifier_id'],
            'order_by' => 'discount_DESC'
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
        $retVal['title'] = 'All Deals';
        $retVal['meta']['title'] = 'All Deals';
        return view('deals-page::deals.alldeals', $retVal);
    }

    public function dealDetail($itemId, \Request  $request) {
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

//            $anotherCoupon = $this->dealRepository->getData([
//                'page_size' => 10,
//                'status' => Coupon::STATUS_ACTIVE,
//                'store_id' => $dataDeal->store_id,
//                'join_store' => 1,
//                'columns' => ['deal.*', 'store.slug as store_slug', 'store.image as store_image', 'store.title as store_title']
//            ]);
//            $retVal['otherCoupon'] = $anotherCoupon;
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

    public function listByStore($slug, \Request $request) {
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
            'order_by' => 'discount_DESC',
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

    public function goUrl($slug)
    {
        $query = Deal::query();
        if (is_string($slug)) {
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

    protected function addXcust($url, $storeId, $couponId = 0, $type = 'coupon') {
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

    private function getDataFromReferer() {
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

    protected function netGoTracking($params = []) {
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

    protected function getDealStore() {
        $retVal = NULL;
        $dealStoreIds = Deal::query()->where('store_id', '>', 0)->distinct()->pluck('store_id');
        if (count($dealStoreIds) > 0) {
            $storeIds = $dealStoreIds->toArray(); 
            $retVal = Store::whereIn('id', $storeIds)->orderBy('title', 'ASC')->select(['id', 'title', 'slug'])->get();
        }
        return $retVal;
    }

    protected function showDeal($itemId, $slug = '') {
        $retVal = [];
        $dealFilter = [
            'columns' => ['id', 'title', 'slug',
                'image', 'content', 'price',
                'sale_price', 'discount', 'store_id',
                'expire_time', 'origin_link', 'affiliate_link',
                'create_time', 'modifier_name', 'modifier_id'],
            'order_by' => 'discount_DESC'
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
}
