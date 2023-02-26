<?php
    $dealLimit = isset($limitDealItem) ? $limitDealItem : 9;
    $dealCategoryId = isset($dealOfCate) ? $dealOfCate : -1;
    $dealStoreId = isset($dealOfStore) ? $dealOfStore : -1;
     $topDealItems = topDeals($dealLimit);
    if (!empty($topDealItems)):
    $topDealBoxTitle = isset($topDealBoxTitle) ? $topDealBoxTitle : "Today's Best Deals";
    $storeRoute = Config::get('deals-page.store_route', 'frontend::store::listByStore');
?>
<div class="row_box is-homepage home-deal">
    <div class="target home-deal-tar">
        <h2><?= $topDealBoxTitle ?></h2>
        <a href="#">View more</a>
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
                <span class="shopnow js-deal" data-clipboard-text="8" data-id="8">
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