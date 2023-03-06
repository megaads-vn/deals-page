<?php

namespace Megaads\DealsPage\Models;

use Illuminate\Database\Eloquent\Model;

class StoreCategory extends Model
{
    protected $table = 'store_n_category';

    public function category() {
        return $this->belongsTo(Category::class);
    }
}