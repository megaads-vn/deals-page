<?php

namespace Megaads\DealsPage\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    const STATUS_ENABLE = 'enable';

    protected $table = 'category';

    public function dealRelationship() {
        return $this->hasMany(DealCategory::class);
    }

    public function coupons () {
        return $this->belongsToMany('App\Models\Coupon', 'coupon_n_category', 'category_id', 'coupon_id');
    }
}
