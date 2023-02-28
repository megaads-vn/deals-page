<?php
    $pageType = "";
    $dealLimit = isset($limitDealItem) ? $limitDealItem : 9;
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
    $topDealItems = topDeals($dealLimit, $dealFilters);
    if (!empty($topDealItems) && count($topDealItems) > 0):
    $topDealBoxTitle = isset($topDealBoxTitle) ? $topDealBoxTitle : "Today's Best Deals";
    $storeRoute = Config::get('deals-page.store_route', 'deal::list::by::store');
?>
<div class="row_box is-homepage home-deal">
    <div class="target home-deal-tar">
        <h2><?= $topDealBoxTitle ?></h2>
        <a href="{{ route($allRoute, $allRoutParams) }}">View more</a>
    </div>
    <ul class="tabs_list clear">
        @foreach ($topDealItems as $item)
            <li class="lf shaw deals">
            <div class="deals_inner">
                <div class="small">
                    <a href="javascript:void(0);" rel="nofollow" target="_blank" class="js-deal" data-clipboard-text="{{ $item->id }}" data-id="{{ $item->id }}">
                        <div class="d-log">
                            <img src="{{ $item->image }}" class="deal-image" height="120" alt="{{ $item->title }}">
                        </div>
                    </a>
                    <div class="main_tit">
                        @if (isset($item->store) && !empty($item->store))
                        <p class="text_ell">
                            From <a href="{{ route($storeRoute, ['slug' => $item->store->slug]) }}">{{ $item->store->name }}</a>
                        </p>
                        @endif
                        <a href="javascript:void(0);" class="main_title js-deal" data-clipboard-text="{{ $item->id }}" data-id="{{ $item->id }}">{{ $item->title }}</a>
                        <span class="deal-expired">Expire: {{ !empty($item->expired_at) ? $item->expired_at : 'On going' }}</span>
                    </div>
                    <p>
                        <span class="big">${{ $item->sale_price }}</span>
                        <span class="small_big">${{ $item->price }}</span>
                    </p>
                </div>
                <div class="amazon_footer">
                    <div class="deals_bot">
                <span class="shopnow js-deal" data-clipboard-text="{{ $item->id }}" data-id="{{ $item->id }}">
                    <span class="shop">Shop Now</span>
                </span>
                    </div>
                </div>
                <div class="offstyle">
                    <span class="shop">{{ $item->discount }}% off</span>
                </div>
            </div>
        </li>
        @endforeach
    </ul>
</div>
<?php endif; ?>
@section('js')
    @parent
    <script defer src="{{ asset('/vendor/deals-page/js/deals-page.js?v=' . time()) }}"></script>
@endsection
