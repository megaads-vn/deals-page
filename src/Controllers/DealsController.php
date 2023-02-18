<?php

namespace Megaads\DealsPage\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Deal;
use App\Models\KeywordResult;
use App\Models\Store;
use App\Models\StoreKeyword;
use App\Models\StoreContact;
use App\Utils\Utils;
use \Illuminate\Support\Facades\Request;
use PHPExcel;
use PHPExcel_Cell;
use PHPExcel_IOFactory;

class DealsController extends Controller {

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    private $totalFilterResult;
    private $numberOfResult;

    public function __construct() {
        parent::__construct();
        $this->totalFilterResult = 0;
        $this->numberOfResult = 50;
    }

    public function index($slug) {
        $keypage = StoreKeyword::where('slug', $slug)->first(['id', 'keyword']);
        if (empty($keypage)) {
            abort(404);
        }
        $retVal = [];
        $deals = Deal::with(['store', 'category'])
            ->where('keypage_id', $keypage->id)
            ->get(['id', 'title', 'slug', 'description', 'regular_price', 'store_id', 'sale_price',
            'image_url', 'category_id', 'currency', 'create_time', 'expired_time', 'url', 'sale_off']);

        $retVal['deals'] = $deals;
        $retVal['page'] = $keypage;
        $retVal['meta'] = ['title' => $keypage->keyword];
        $retVal['title'] = $keypage->keyword;
        return \View::make('deals-page::deals.index', $retVal);
    }

    public function oldRedirection($slug) {
        return redirect()->to("/$slug-deals");
    }

    public function import(Request $request) {
        $retVal = [
            'status' => 'fail'
        ];
        $importData = 0;
        $updateData = 0;
        $errors = 0;
        $errorMsg = '';
        $file = public_path('deals.xlsx');
        $sheetData = $this->getExcelToArray($file);
        if (!empty($sheetData)) {

            foreach ($sheetData as $pageName => $items) {
                $pageSlug = Utils::getSlug(trim($pageName));
                $findPage = StoreKeyword::where('slug', $pageSlug)->first(['id']);
                $pageId = -1;
                if (empty($findPage)) {
                    $pageData = [
                        'keyword' => $pageName,
                        'slug' => $pageSlug
                    ];
                    $pageId = StoreKeyword::insertGetId($pageData);
                } else {
                    $pageId = $findPage->id;
                }
                if ($pageId > 0) {
                    foreach ($items as $item) {
                        $imageUrl = $this->saveDealsImage($item);
                        $storeId = $this->getStoreId($item->urlStore);
                        $cateId = $this->getCateId($item->urlCate);
                        $saleOff = 0;
                        if ($item->salePrice != 'non' && $item->salePrice > 0 && $item->salePrice < $item->regularPrice) {
                            $saleOff = floor((($item->regularPrice - $item->salePrice) / $item->regularPrice) * 100);
                        }

                        $saveData = [
                            'title' => $item->dealsTitle,
                            'slug' => Utils::getSlug($item->dealsTitle),
                            'description' => $item->dealsDescription,
                            'currency' => '$',
                            'regular_price' => (double) $item->regularPrice,
                            'sale_price' => ($item->salePrice != 'non') ? (double) $item->salePrice : 0,
                            'sale_off' => $saleOff,
                            'image_url' => $imageUrl,
                            'expired_time' => $item->expires,
                            'url' => $item->nwLinkShopNow,
                            'store_id' => $storeId,
                            'category_id' => $cateId,
                            'create_time' => new \DateTime(),
                            'update_time' => new \DateTime(),
                            'keypage_id' => $pageId
                        ];
                        try {
                            $checkExists = Deal::where('slug', $saveData['slug'])->first(['id']);
                            if (!empty($checkExists)) {
                                unset($saveData['create_time']);
                                Deal::where('id', $checkExists->id)->update($saveData);
                                $updateData++;
                            } else {
                                Deal::insert($saveData);
                                $importData++;
                            }
                        } catch (\Exception $ex) {
                            $errors++;
                            $errorMsg = $ex->getMessage();
                            \Log::error('DEAL_ERROR: ' . $ex->getMessage());
                        }
                    }
                }
            }
        }
        $retVal['data'] =[
            'imported' => $importData,
            'updated' => $updateData,
            'error' => $errors,
            'message' => $errorMsg
        ];

        return response()->json($retVal);
    }

    public function goUrl($slug)
    {
        $deals = Deal::where('slug', $slug)->first(['url']);
        if (!empty($deals) && !empty($deals->url)) {
            return redirect($deals->url);
        } else {
            abort(404);
        }
    }

    private function saveDealsImage($item) {
        $imageUrl = $item->imageUrl;
        $imageUrl = explode('?', $imageUrl)[0];
        $dealsPath = "images/deals";
        $absolutePath = public_path($dealsPath);
        if (!file_exists($absolutePath)) {
            mkdir($absolutePath, 0775);
        }

        $extractImage = explode("/", $imageUrl);
        $imageName = end($extractImage);
        $fullImageSavedPath = $absolutePath . "/" . $imageName;
        if (!file_exists($fullImageSavedPath)) {
            $ch = curl_init($imageUrl);
            $fp = fopen($fullImageSavedPath, 'wb');
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_exec($ch);
            curl_close($ch);
            fclose($fp);
        }
        return "/" . $dealsPath . "/" . $imageName;
    }

    private function getExcelToArray($input)
    {
        $header = null;
        $data = array();

        $objPHPExcel = PHPExcel_IOFactory::load($input);

        $sheet = $objPHPExcel->getSheet(1);
        $total_rows = $sheet->getHighestRow();
		$highestColumn      = $sheet->getHighestColumn();	
		$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
        
        for ($row = 0; $row <= $total_rows; ++ $row) {
            for ($col = 0; $col < $highestColumnIndex; ++ $col) {
                $cell = $sheet->getCellByColumnAndRow($col, $row);
                $val = $cell->getValue();
                $records[$row][] = $val;
            }
            if ($row == 1) {
                $header = $records[$row];
                unset($records[$row]);
            }
        }
        
        $records = array_values($records);
        if (count($records) > 0) {
            $groupItems = [];
            $pageName = "";
            $total = count($records);
            foreach ($records as $idx => $items) {
                $dataItem = [];
                if (!empty($items[0]) && $idx == 1) {
                    $pageName = $items[0];
                } else if (!empty($items[0]) && count($groupItems) > 0) {
                    $data[$pageName] = (object) $groupItems;
                    $groupItems = [];
                    $pageName = $items[0];
                }
                if (!empty($items[2])) {
                    foreach ($items as $index => $item) {
                        if (isset($header[$index])) {
                            $headerAttribute = strtolower($header[$index]);
                            $headerAttribute = preg_replace('/\s+/', '', ucwords($headerAttribute, ' '));
                            $headerAttribute = lcfirst($headerAttribute);
                            $dataItem[$headerAttribute] = $item;
                        }
                    }
                    $groupItems[] = (object) $dataItem;
                }
                if ($total == ($idx + 1)) {
                    $data[$pageName] = (object) $groupItems;
                }
            }
        };
        return $data;
    }

    private function getStoreId($urlStore) {
        $retVal = 0;
        $urlStore = explode('?', $urlStore);
        $urlStore = $urlStore[0];
        $urlStore = explode('/', $urlStore);
        $urlStore = end($urlStore);
        $store = Store::where('slug', $urlStore)->first(['id']);
        if (!empty($store)) {
            $retVal = $store->id;
        }
        return $retVal;
    }

    private function getCateId($urlCategory) {
        $retVal = 0;
        $urlCategory = explode('?', $urlCategory);
        $urlCategory = $urlCategory[0];
        $urlCategory = explode('/', $urlCategory);
        $urlCategory = end($urlCategory);
        $category = Category::where('slug', $urlCategory)->first(['id']);
        if (!empty($category)) {
            $retVal = $category->id;
        }
        return $retVal;
    }

}
