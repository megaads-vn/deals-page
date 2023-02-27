@extends('frontend.layout.master')
@section('content')
    <div id="sell_contaier">
        <div class="top_header">
            <nav class="breadcrumb hd_row">
                <ul>
                    <li><a href="/">Home</a></li>
                    @if (isset($dataDeal->store) && !empty($dataDeal->store))
                        <li><a href="/deals/<?= $dataDeal->store->slug ?>">All Deals</a></li>
                    @endif
                    <li><?= $dataDeal->title ?></li>
                </ul>
            </nav>
        </div>
        <div class="deal-top">
            <div class="deal-content-wrapper">
                <div class="deal-item-images">
                    <img src="<?= $dataDeal->image ?>" />
                </div>
                <div class="deal-item-content current-center">
                    <p class="expire">
                        Expire date <span class="clock"> {{ timeOnGoing($dataDeal->expire_time) }}</span>
                    </p>
                    <h2 class="product-title">
                        <?= $dataDeal->title ?>
                    </h2>
                    <p class="shipping">Fulfilled by <?= $dataDeal->store_title ?></p>
                    <p class="product-discount"><?= ($dataDeal->discount > 0) ? $dataDeal->discount . '% discount' : '' ?></p>
                    <div class="price-area">
                        <div class="product-price">
                            @if ( $dataDeal->discount >  0 )
                                <span class="original_price"><?= $dataDeal->currency . $dataDeal->price ?></span>
                            @endif
                            <span class="current_price"><?= $dataDeal->currency . $dataDeal->sale_price ?></span>
                        </div>
                        <div class="deal_get_share">
                            <a href="<?= route("deal::action::go", ['id' => $dataDeal->id]) ?>" data-clipboard-text="<?= $dataDeal->id ?>" data-id="<?= $dataDeal->id ?>" class="deal_get js-deal" target="_blank">Shop Now</a>
                        </div>
                    </div>

                    <h4 class="product-desc">Description</h4>
                    <p class="desc-detail">
                        <?= $dataDeal->content ?>
                    </p>
                </div>
            </div>
        </div>
        @if (isset($otherCoupon) && count($otherCoupon) > 0)
            <div class="main-dealbg">
                <div class="deal_content" id="main_inner">
                    <h2>You May Also Like</h2>
                    <ul class="tabs_list clear">
                        <?php foreach ($otherCoupon as $otherItem) { ?>
                        @include('deals-page::deals.inc.deal-item', ['item' => $otherItem])
                        <?php } ?>
                    </ul>
                </div>
            </div>
        @endif
    </div>
    <?php if (isset($showPopup) && $showPopup) { ?>
    <div id="deal-pop" style="display: block">
        <div class="deal-content">
            <div class="popup-head">
                <h2 class="product-title" style="font-size: 19px; margin: 0">
                    <?= $dataDeal->title ?>
                </h2>
                <span class="expire">
                    Expire date: <span class="clock"> {{ timeOnGoing($dataDeal->expire_time) }}</span>
                </span>
                <span class="shipping" style="padding-left: 10px;">From by <a href="/store/<?= $dataDeal->store_slug ?>"><?= $dataDeal->store_title ?></a></span>
            </div>
            <div class="product-detail-content">
                <span class="close-deal">
                    <span></span>
                    <span></span>
                </span>
                <div class="product-img" style="position: relative">
                    @if ($dataDeal->discount > 0)
                        <span class="pop-discount">{{ $dataDeal->discount }}% discount</span>
                    @endif
                    <img src="<?= $dataDeal->image ?>" alt="<?= $dataDeal->title ?>">
                </div>
                <div class="deal-detail">
                    <div class="product-price">
                        <?php if ($dataDeal->discount > 0) { ?>
                        <span class="original_price"><?= $dataDeal->currency_code ?><?= $dataDeal->price ?></span>
                        <?php } ?>

                        <span class="current_price"><?= $dataDeal->currency_code ?><?= $dataDeal->sale_price ?></span>
                    </div>
                    <div class="deal_get_share">
                        <a target="_blank" href="<?= route("deal::action::go", ['id' => $dataDeal->id]) ?>" class="deal_get">
                            Shop Now
                        </a>
                    </div>
                    <?php if (!empty($dataDeal->content)) { ?>
                    <h4 class="product-desc">Description</h4>
                    <p class="more-content">
                        <?= $dataDeal->content ?>
                    </p>
                    <?php } ?>
                </div>
            </div>
            @if (isset($otherCoupon) && count($otherCoupon) > 0) {
            <div class="deal-next-li popup-list">
                <h2 class="next-title">More: <?= $dataDeal->store->title ?> coupons</h2>
                <ul class="listed-none">
                    <?php foreach ($otherCoupon as $itemCoupon) { ?>
                    <li>
                        <a href="<?= route('deal::detail', ['itemId' => $itemCoupon->id]); ?>"> <?= $itemCoupon->title; ?></a>
                    </li>
                    <?php } ?>
                </ul>
            </div>
            @endif
        </div>
        <div class="deal-backgkround"></div>
    </div>
    <?php } ?>
@endsection