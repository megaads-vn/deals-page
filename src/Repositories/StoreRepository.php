<?php

namespace Megaads\DealsPage\Repositories;

use Megaads\DealsPage\Models\Config;
use Megaads\DealsPage\Models\Deal;
use Megaads\DealsPage\Models\Store;
use Megaads\DealsPage\Models\StoreReview;

class StoreRepository extends BaseRepository
{
    public function __construct() {
    }
    
    public function getListStore() {
        $storiesId = [];
        $topBrandConfigure = Config::where('key', 'alldeal.topbrand')->first(['value']);
        $topBrandBoxTitle = "Top Brands Deals";
        if (!empty($topBrandConfigure)) {
            $storiesId = (json_decode($topBrandConfigure->value))->items;
            $topBrandBoxTitle = (json_decode($topBrandConfigure->value))->boxTitle;
        }

        if (empty($storiesId)) {
            $i = 0;
            do {
                $data = Deal::select(['id', 'store_id'])->orderBy('id', 'desc')->offset($i)->take(10)->get();
                foreach ($data as $item) {
                    $storeId = $item->store_id;
                    if (!in_array($storeId, $storiesId) && $storeId != 0) {
                        $storiesId[] = $storeId;
                    }
                }

                $i += 10;
            } while (count($storiesId) <= 20);
        }
        view()->share('topBrandBoxTitle', $topBrandBoxTitle);
        return Store::select(['id', 'title', 'slug', 'image', 'vote_down','vote_up'])->whereIn('id', $storiesId)->get();
    }
    
    public function getListStoreOfReview() {
        $storiesId = [];
        $topReviewConfigure = Config::where('key', 'alldeal.reviews')->first(['value']);
        $topReviewBoxTitle = "Customer Reviews";
        if (!empty($topReviewConfigure)) {
            $storiesId = (json_decode($topReviewConfigure->value))->items;
            $topReviewBoxTitle = (json_decode($topReviewConfigure->value))->boxTitle;
        }

        if (empty($storiesId)) {
            $i = 0;
            do {
                $data = StoreReview::select(['id', 'store_id'])->orderBy('id', 'desc')->offset($i)->take(10)->get();
                foreach ($data as $item) {
                    $storeId = $item->store_id;
                    if (!in_array($storeId, $storiesId) && $storeId != 0) {
                        $storiesId[] = $storeId;
                    }
                }

                $i += 10;
            } while (count($storiesId) <= 10);
        }
        view()->share('topReviewBoxTitle', $topReviewBoxTitle);
        return Store::select(['id', 'title', 'slug', 'image','vote_down','vote_up'])->whereIn('id', $storiesId)->get();
    }
}
