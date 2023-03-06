<?php

namespace Megaads\DealsPage\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    const STATUS_ACTIVE = 'active';
    const STATUS_UNRELIABLE = 'unreliable';

    protected $table = 'coupon';
}