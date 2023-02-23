<?php

namespace Megaads\DealsPage\Controllers;

use App\Http\Controllers\Controller;
use Megaads\DealsPage\Models\Deal;
use Megaads\DealsPage\Models\Store;
use Megaads\DealsPage\Models\DealRelation;
use App\Utils\Utils;
use Illuminate\Support\Facades\Input;
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

    public function __construct() {
        parent::__construct();
        $this->dealPageTable = \Config::get('deals-page.deal_related_page.name', 'store_n_keyword');
        $this->dealPageColumns = \Config::get('deals-page.deal_related_page.name', ['id', 'keyword']);
    }

    public function index($slug) {
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

    public function goUrl($slug)
    {
        $deals = Deal::where('slug', $slug)->first(['affiliate_link', 'store_id', 'id']);
        if (!empty($deals) && !empty($deals->affiliate_link)) {
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

}
