<?php

namespace Megaads\DealsPage\Repositories;

use Megaads\DealsPage\Models\Deal;

class DealRepository extends BaseRepository
{

    protected $pageId = 0;
    protected $pageSize = 50;

    public function create($params)
    {
        // TODO: Implement create() method.
    }

    public function read($filters)
    {
        // TODO: Implement read() method.
    }

    public function update($id, $params)
    {
        // TODO: Implement update() method.
    }

    public function delete($id)
    {
        // TODO: Implement delete() method.
    }

    public function bulkInsert($arrayData)
    {
        $reVal = false;
        try {
            Deal::insert($arrayData);
            $reVal = true;
        } catch (\Exception $ex) {
            dealPageSysLog('error', 'BULK_CREATE_PRODUCTS: ', $ex);
        }
        return $reVal;
    }

    protected function buildFilter($filters)
    {
        $query = Deal::query();

        if (array_key_exists('title', $filters)) {
            $query->where('title', $filters['title']);
        }

        if (array_key_exists('like_title', $filters)) {
            $query->where('title', 'like', "'%" . $filters["title"] . "%'");
        }



        return $query;
    }

}