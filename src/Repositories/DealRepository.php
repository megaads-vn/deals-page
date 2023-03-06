<?php

namespace Megaads\DealsPage\Repositories;

use Megaads\DealsPage\Models\Deal;
use Megaads\DealsPage\Models\DealCategory;

class DealRepository extends BaseRepository
{

    protected $pageId = 0;
    protected $pageSize = 50;

    public function create($params)
    {
        $retVal = NULL;
        try {
            $deal = new Deal();
            $deal->fill($params);
            if ($deal->save()) {
                $retVal = $deal->id;
            }
        } catch (\Exception $exception) {
            dealPageSysLog('error', 'REPO_CREATE_DEAL: ', $exception);
        }
        return $retVal;
    }

    /**
     * @param $filters
     * @return array|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|void|null
     */
    public function read($filters)
    {
        $retVal = [];
        try {
            $query = $this->buildQuery($filters);

            if (array_key_exists('pageSize', $filters)) {
                $this->pageSize = $filters['pageSize'];
            }
            if (array_key_exists('pageId', $filters)) {
                $this->pageId = $filters['pageId'];
            }

            if (array_key_exists('metrics', $filters) && $filters['metrics'] == 'first') {
                $retVal["data"] = $query->first();
            } else if (array_key_exists('metrics', $filters) && $filters['metrics'] == 'count') {
                $retVal["data"] = $query->count();
            } else {
                $query->limit($this->pageSize);
                $query->offset(($this->pageSize * $this->pageId));
                $retVal = [
                    "pageId" => $this->pageId,
                    "pageSize" => $this->pageSize,
                    "data" => $query->get(),
                ];
            }
        } catch (\Exception $ex) {
            dealPageSysLog('error', 'FIND_DEAL: ', $ex);
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
            Deal::where('id', $id)->update($params);
        } catch (\Exception $exception) {
            $retVal = false;
            dealPageSysLog('error', 'UPDATE_DEAL: ', $exception);
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
             Deal::where('id', $id)->delete();
        } catch (\Exception $exception) {
            dealPageSysLog('error', 'DELETE_DEAL: ', $exception);
        }
        return $retVal;
    }

    /**
     * @param $arrayData
     * @return bool
     */
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

    public function updateDealCategory($dealId, $params) {
        $retVal = true;
        try {
            $existsCate = DealCategory::where('deal_id', $dealId)->pluck('category_id')->toArray();
            if (!empty($existsCate)) {
                $removed = [];
                foreach ($existsCate as $item) {
                    if (!in_array($item, $params)) {
                        $removed[] = $item;
                    } else {
                        $findIndex = array_search($item, $params);
                        if (isset($params[$findIndex]))
                            unset($params[$findIndex]);
                    }
                }
                if (count($removed) > 0) {
                    DealCategory::whereIn('category_id', $removed)->delete();
                }
                if (count($params) > 0) {
                    $insertParams = [];
                    foreach ($params as $item) {
                        $insertParams[] = [
                            'deal_id' => $dealId,
                            'category_id' => $item
                        ];
                    }
                    if (!empty($insertParams)) {
                        DealCategory::insert($insertParams);
                    }
                }
            } else {
                $insertParams = [];
                foreach ($params as $item) {
                    $insertParams[] = [
                        'deal_id' => $dealId,
                        'category_id' => $item
                    ];
                }
                if (!empty($insertParams)) {
                    DealCategory::insert($insertParams);
                }
            }
        } catch (\Exception $exception) {
            $retVal = false;
            dealPageSysLog('error', 'REPO_UPDATE_DEAL_CATE: ', $exception);
        }
        return $retVal;
    }

    protected function buildQuery($filters)
    {
        $query = Deal::query();
        $query->with(['categories', 'store' => function($s) {
            $s->select(['id', 'title as name', 'slug']);
        }]);
        $columns = ['*'];
        if (array_key_exists('columns', $filters)) {
            $columns = $filters['columns'];
            if (is_string($columns)) {
                $columns = explode(',', $columns);
            }
            $query->select($columns);
        }

        if (array_key_exists('id', $filters)) {
            $query->where('id', $filters['id']);
        }

        if (array_key_exists('title', $filters)) {
            $query->where('title', $filters['title']);
        }

        if (array_key_exists('like_title', $filters)) {
            $query->where('title', 'like', "'%" . $filters["title"] . "%'");
        }

        if (array_key_exists('storeId', $filters)) {
            $query->where('store_id', $filters['storeId']);
        }

        if (array_key_exists('categoryId', $filters)) {
            $query->join('deal_n_category', 'deal_n_category.deal_id', '=', 'deals.id');
            $query->where('deal_n_category.category_id', $filters['categoryId']);
        }

        if (array_key_exists('codeNotNull', $filters)) {
            $query->whereNotNull('code');
        }

        if (array_key_exists('priceFrom', $filters) && array_key_exists('priceTo', $filters)) {
            $query->whereBetween('price', [$filters['priceFrom'], $filters['priceTo']]);
        }


        if (array_key_exists('statuses', $filters)) {
            $statuses = explode(",", $filters['statuses']);
            $query->whereIn('status', $statuses);
        }
        if (array_key_exists('status', $filters)) {
            $query->where('status', $filters['status']);
        }
        if (array_key_exists('createTimeFrom', $filters)) {
            $createFrom = preg_replace('/\//i', '-', $filters['createTimeFrom']);
            $createFrom = new \DateTime($createFrom . ' 00:00:00');
            $query->where('create_time', '>=', $createFrom);
        }
        if (array_key_exists('createTimeTo', $filters)) {
            $createTo = preg_replace('/\//i', '-', $filters['createTimeTo']);
            $createTo = new \DateTime($createTo . ' 00:00:00');
            $query->where('create_time', '<', $createTo);
        }
        if (array_key_exists('createTimeTo', $filters) && array_key_exists('createTimeFrom', $filters)) {
            $createTo = preg_replace('/\//i', '-', $filters['createTimeTo']);
            $createTo = new \DateTime($createTo . ' 00:00:00');
            $createFrom = preg_replace('/\//i', '-', $filters['createTimeFrom']);
            $createFrom = new \DateTime($createFrom . ' 00:00:00');
            $query->whereBetween('create_time', [$createFrom, $createTo]);
        }

        if (array_key_exists('discountMoreThan', $filters)) {
            $query->where('discount', '>', $filters['discountMoreThan']);
        }
        if (array_key_exists('discountLessThan', $filters)) {
            $query->where('discount', '<', $filters['discountLessThan']);
        }

        if (array_key_exists('discountMoreThan', $filters) && array_key_exists('discountLessThan', $filters)) {
            $query->whereBetween('discount', [$filters['discountMoreThan'], $filters['discountLessThan']]);
        }

        if (array_key_exists('advSearch', $filters)) {
            $strQuery = explode('|', $filters['advSearch']);
            preg_match('/(\w+)(\+|\-)(.*)/i', $strQuery[0], $matches);
            foreach ($strQuery as $item) {
                preg_match('/(\w+)(\+|\-)(.*)/i', $item, $matches);
                if ($matches) {
                    $field = $matches[1];
                    $operation = $matches[2];
                    if ($operation == '+') {
                        $operation = 'LIKE';
                    } else if ($operation == '-') {
                        $operation = 'NOT LIKE';
                    }
                    $value = '%' . $matches[3] . '%';
                    $query->where($field, $operation, $value);
                }
            }
        }

        if (array_key_exists('order_by', $filters)) {
            $orderByAttributes = explode('_', $filters['order_by']);
            $sortOrder = $orderByAttributes[1];
            $field = $orderByAttributes[0];
            $query->orderBy($field, $sortOrder);
        } else {
            $query->orderBy('deals.id', 'DESC');
        }

        return $query;
    }

}