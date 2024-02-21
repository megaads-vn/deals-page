<?php

namespace Megaads\DealsPage\Models;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    protected $table = 'store';

    public function deals() {
        return $this->hasMany(Deal::class);
    }
}
