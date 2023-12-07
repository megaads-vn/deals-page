<?php

namespace Megaads\DealsPage\Repositories;

use Megaads\DealsPage\Models\Catalog;
use Megaads\DealsPage\Models\Category;
use Megaads\DealsPage\Models\Config;
use Megaads\DealsPage\Models\Deal;

class CategoryRepository extends BaseRepository
{
    public function __construct() {
    }

    public function getListCategory() {
        $categoriesId = [];
        $categoryConfigure = Config::where('key', 'alldeal.category')->first(['value']);
        if (!empty($categoryConfigure)) {
            $categoriesId = json_decode($categoryConfigure->value)->items;
        }
        if (empty($categoriesId)) {
            $i = 0;
            do {
                $data = Deal::select(['id', 'category_id'])->orderBy('id', 'desc')->offset($i)->take(5)->get();
                foreach ($data as $item) {
                    $listCategoryId = $item->category_id;
                    if ($listCategoryId) {
                        $listCategoryId = explode(',',$listCategoryId);

                        foreach ($listCategoryId as $id) {
                            if (!in_array($id, $categoriesId)) {
                                $categoriesId[] = $id;
                            }
                        }
                    }
                }

                $i += 5;
            } while (count($categoriesId) <= 20);
        }

        return $data = Category::select(['id', 'title', 'slug', 'image'])->whereIn('id', $categoriesId)->get();
    }
}
