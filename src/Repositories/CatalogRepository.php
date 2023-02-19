<?php

namespace Megaads\DealsPage\Repositories;

use Megaads\DealsPage\Models\Catalog;

class CatalogRepository extends BaseRepository
{
    protected $pageId = 0;
    protected $pageSize = 50;

    public function create($item)
    {

    }

    /**
     * @param $items
     * @return bool
     */
    public function bulkCreate($items = []) {
        $reVal = false;
        try {
            Catalog::insert($items);
            $reVal = true;
        } catch (\Exception $ex) {
            dealPageSysLog('error', 'BULK_CREATE_CATALOG: ', $ex);
        }
        return $reVal;
    }

    /**
     * @param $filters
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|void|null
     */
    public function read($filters)
    {
        $retVal = NULL;
        try {
             $query = $this->buildQuery($filters);
             if (array_key_exists('page_size', $filters)) {
                 $this->pageSize = $filters['page_size'];
             }
             if (array_key_exists('page_id', $filters)) {
                 $this->pageId = $filters['page_id'];
             }

             $query->limit($this->pageSize);
             $query->offset(($this->pageSize * $this->pageId));
             $retVal = $query->get();

        } catch (\Exception $exception) {
            dealPageSysLog('error', 'READ_CATALOG: ', $exception);
        }
        return $retVal;
    }

    /**
     * @param $id
     * @param $params
     * @return bool
     */
    public function update($id, $params)
    {
        $retVal = true;
        try {
            Catalog::where('id', $id)->update($params);
        } catch (\Exception $exception) {
            $retVal = false;
            dealPageSysLog('error', 'UPDATE_CATALOG: ', $exception);
        }
        return $retVal;
    }

    /**
     * @param $id
     * @return bool
     */
    public function delete($id)
    {
        $retVal = true;
        try {
            Catalog::where('id', $id)->delete();
        } catch (\Exception $exception) {
            $retVal = false;
            dealPageSysLog('error', 'DELETE_CATALOG: ', $exception);
        }
        return $retVal;
    }

    /**
     * @param $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function buildQuery($filters) {
        $query = Catalog::query();

        if (array_key_exists('crawl_page', $filters)) {
            $query->where('crawl_page', $filters['crawl_page']);
        }
        if (array_key_exists('crawl_page', $filters) && strpos($filters['crawl_page'], '!') >= 0) {
            $query->where('crawl_page', '!=', $filters['crawl_page']);
        }

        return $query;
    }
}