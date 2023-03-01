<?php
    $pageType = "";
    $dealLimit = isset($limitDealItem) ? $limitDealItem : 12;
    $routeArray = app('request')->route()->getAction();
    $controllerAction = class_basename($routeArray['controller']);
    $allRoute = 'deal::all';
    $allRoutParams = [];
    list($controller, $action) = explode('@', $controllerAction);
    $currentParams = Route::current()->parameters();
    $dealFilters = [];
    if ($controller === "StoreController") {
        $pageType = "store";
        if (isset($currentParams['slug'])) {
            $findStore = \Megaads\DealsPage\Models\Store::query()->where('slug', $currentParams['slug'])->first(['id', 'slug']);
            if (!empty($findStore)) {
                $dealFilters['store_id'] = $findStore->id;
            }
            $allRoute = 'deal::list::by::store';
            $allRoutParams = ['slug' => $findStore->slug];
        }
    } else if ($controller === "CategoryController") {
        $pageType = "category";
        if (isset($currentParams['slug'])) {
            $findCategory = \Megaads\DealsPage\Models\Category::query()->where('slug', $currentParams['slug'])->first(['id']);
            if (!empty($findCategory)) {
                $dealFilters['category_id'] = $findCategory->id;
            }
        }
    }
    if (isset($listDeals)) {
        $topDealItems = $listDeals;
    } else {
        $topDealItems = topDeals($dealLimit, $dealFilters);
    }
    if (!empty($topDealItems) && count($topDealItems) > 0):
    $topDealBoxTitle = isset($topDealBoxTitle) ? $topDealBoxTitle : "Today's Best Deals";
    $storeRoute = Config::get('deals-page.store_route', 'frontend::store::listByStore');
?>
<div class="homepage-coupons-wapper">
    <div class="target home-deal-tar">
        <h2 class="home-heading first-box-heading"><?= $topDealBoxTitle ?></h2>
    </div>
    <ul class="tabs-list">
        @foreach ($topDealItems as $item)
            @include('deals-page::deals.inc.deal-item', ['item' => $item])
        @endforeach
    </ul>
    <a class="viewmore-deal" href="{{ route($allRoute, $allRoutParams) }}" title="View more">View more</a>
</div>
<?php endif; ?>
@section('js')
    @parent
    <script defer src="{{ asset('/vendor/deals-page/js/deals-page.js?v=' . time()) }}"></script>
@endsection
@section('style')
    @parent
    <link rel="stylesheet" href="{{ asset('/vendor/deals-page/css/all-deals.css?v=' . time()) }}" />
@endsection