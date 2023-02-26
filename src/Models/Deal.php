<?php

namespace Megaads\DealsPage\Models;

use Illuminate\Database\Eloquent\Model;

class Deal extends Model
{
    const CREATED_AT =  'create_time';
    const UPDATED_AT =  'update_time';

    protected $fillable = [
        'title', 'content', 'slug', 'search_slug', 'status', 'type', 'price', 'sale_price', 'sorder', 'store_id', 'creator_name', 'creator_id',
        'modifier_id', 'affiliate_link', 'origin_link', 'image', 'currency', 'discount', 'category_id', 'expire_time', 'sorder_in_category',
        'views', 'vote_up', 'vote_down', 'meta_title', 'meta_description', 'meta_keywords', 'crawl_id', 'mpn', 'sku',
        'in_stock'
    ];

    protected $appends = [
        'originUrl', 'affilidateUrl', 'expireTime', 'createTime', 'modifierName', 'modifierId', 'currency_code'
    ];

    protected $hidden = [
        'origin_link', 'affiliate_link', 'expire_time', 'create_time', 'modifier_id', 'modifier_name'
    ];

    public function categories() {
        return $this->belongsToMany(Category::class, 'deal_n_category')->select(['category.id', 'title', 'slug']);
    }

    public function store() {
        return $this->belongsTo(Store::class);
    }

    public function getOriginUrlAttribute() {
        return $this->attributes['origin_link'];
    }
    public function getAffilidateUrlAttribute() {
        return $this->attributes['affiliate_link'];
    }
    public function getExpireTimeAttribute() {
        return $this->attributes['expire_time'];
    }
    public function getCreateTimeAttribute() {
        return $this->attributes['create_time'];
    }
    public function getModifierIdAttribute() {
        return $this->attributes['modifier_id'];
    }
    public function getModifierNameAttribute() {
        return $this->attributes['modifier_name'];
    }
    public function getCurrencyCodeAttribute() {
        return '$';
    }

}
