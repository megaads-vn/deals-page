<?php

namespace Megaads\DealsPage\Repositories;

use Megaads\DealsPage\Models\Deal;
use Megaads\DealsPage\Models\Store;
use Megaads\DealsPage\Models\StoreReview;

class StoreRepository extends BaseRepository
{
    public function __construct() {
    }
    
    public function getListStore() {
        $storiesId = [];
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

        return Store::select(['id', 'title', 'slug', 'image'])->whereIn('id', $storiesId)->get();
    }
    
    public function getListStoreOfReview() {
        $storiesId = [];
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

        return Store::select(['id', 'title', 'slug', 'image'])->whereIn('id', $storiesId)->get();
    }
}