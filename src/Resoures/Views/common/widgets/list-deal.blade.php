<?php
use Megaads\Adsense\Utils\Adsense;
?>
@if (isset($listDeal))
    @include('deals-page::keypage.inc.filter')
    @forelse ($listDeal as $index => $item)
        @include('deals-page::common.item', ['item' => $item])
    @empty
        <div style="padding: 5px 0 15px; font-size: 17px; margin-bottom: 25px; color: #444;">There isn't any deal in here</div>
    @endif
@endif

<?php if (isset($keywordsUnreliable) && !empty($keywordsUnreliable)) {
    echo '<h3 class="box-coupon-title" style="margin-top: 15px">Unreliable '.(isset($store) ? $store : '').'Coupons &amp; Promo codes'.(isset($date)?$date:'').'</h3>';
    foreach ($keywordsUnreliable as $item) {
        echo View::make('frontend.common.unreliable-item', ['item' => $item])->render();
    }
} ?>
