<?php

namespace Megaads\DealsPage\Models;

use Illuminate\Database\Eloquent\Model;

class Deal extends Model
{
    const CREATED_AT =  'create_time';
    const UPDATED_AT =  'update_time';

    const STATUS_ACTIVE = 'active';
    const STATUS_DISABLE = 'disable';

    protected $fillable = [
        'title', 'content', 'slug', 'search_slug', 'status', 'type', 'price', 'sale_price', 'sorder', 'store_id', 'creator_name', 'creator_id',
        'modifier_id', 'affiliate_link', 'origin_link', 'image', 'currency', 'discount', 'category_id', 'expire_time', 'sorder_in_category',
        'views', 'vote_up', 'vote_down', 'meta_title', 'meta_description', 'meta_keywords', 'crawl_id', 'mpn', 'sku',
        'in_stock'
    ];

    protected $appends = [
        'originUrl',
        'expireTime',
        'createTime',
        'modifierId',
        'modifierName',
        'affilidateUrl',
        'currency_code'
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
        return isset($this->attributes['origin_link']) ? $this->attributes['origin_link'] : NULL;
    }

    public function getAffilidateUrlAttribute() {
        return isset($this->attributes['affiliate_link']) ? $this->attributes['affiliate_link'] : NULL;
    }
    public function getExpireTimeAttribute() {
        return isset($this->attributes['expire_time']) ? $this->attributes['expire_time'] : NULL;
    }
    public function getCreateTimeAttribute() {
        return isset($this->attributes['create_time']) ? $this->attributes['create_time'] : NULL;
    }
    public function getModifierIdAttribute() {
        return isset($this->attributes['modifier_id']) ? $this->attributes['modifier_id'] : NULL;
    }
    public function getModifierNameAttribute() {
        return isset($this->attributes['modifier_name']) ? $this->attributes['modifier_name'] : NULL;
    }
    public function getCurrencyCodeAttribute() {
        return '$';
    }

}
