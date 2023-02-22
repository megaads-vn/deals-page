<?php

namespace Megaads\DealsPage\Controllers;

use App\Http\Controllers\Controller;
use Megaads\DealsPage\Models\Deal;
use Megaads\DealsPage\Models\Store;
use Megaads\DealsPage\Models\DealRelation;
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
        $deals = Deal::where('slug', $slug)->first(['affiliate_link']);
        if (!empty($deals) && !empty($deals->affiliate_link)) {
            return redirect($deals->affiliate_link);
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

}
