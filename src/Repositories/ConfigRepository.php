<?php

namespace Megaads\DealsPage\Repositories;

use Megaads\DealsPage\Models\Config;

class ConfigRepository extends BaseRepository
{
    public function __construct() {
    }
    
    public function findByField($field, $value)
    {
        return Config::where($field, $value)->first();
    }
}