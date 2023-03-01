@extends('frontend.layout.master')
<style media="screen">
    #deal-pop {
        position: fixed;
        top: 0;
        left: 0;
        z-index: 999923;
        display: none;
        width: 100%;
        height: 100%;
    }
    .deal-backgkround {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1;
        display: block;
        background-color: rgba(11 11 11 / 33%);
    }

    .deal-content {
        width: 92%;
        max-width: 760px;
        background-color: #fff;
        border-radius: 4px;
        box-shadow: 0 6px 35px rgba(11 11 11 / 33%);
        position: absolute;
        top: 100px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 3;
    }
    .pop-discount {
        position: absolute;
        top: 10px;
        left: 10px;
        background-color: #fe0000;
        z-index: 1;
        color: #fff;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 3px 6px;
        border-radius: 4px;
        line-height: 1.3;
    }
    .deal-popup-image {
        position: relative;
        width: 94%;
        max-width: 400px;
        margin: 10px auto;
    }
    .deal-popup-image::before {
        content: "";
        padding-top: 100%;
        display: block;
    }

    .deal-detail {
        padding: 10px 16px;
    }

    .deal-popup-image img {
        width: 100%;
        height: 100%;
        display: block;
        position: absolute;
        top: 0;
        left: 0;
        object-fit: contain;
        object-position: center;
    }

    .popup-head {
        text-align: center;
        padding: 10px 20px;
        position: relative;
    }

    .deal_get {
        border: none;
        outline: none;
        color: #fff;
        background-color: #337ab7;
        border-color: #2e6da4;
        user-select: none;
        max-width: 360px;
        min-width: 160px;
        cursor: pointer;
        text-align: center;
        padding: 12px 15px;
        border-radius: 3px;
        width: 100%;
        display: block;
        transition: background-color 300ms ease-in-out;
    }
    .deal_get:hover {
        background-color: #204d74;
        border-color: #122b40;
        color: #fff;
    }
    .close-popup {
        position: absolute;
        border: none;
        background: none;
        outline: none;
        position: absolute;
        top: 10px;
        right: 10px;
    }
    .deal-content-wrapper {
        display: flex;
        justify-content: flex-start;
        align-items: flex-start;
        margin: 32px auto;
        position: relative;
    }
    .deal-item-images {
        width: 300px;
        min-width: 300px;
        margin-right: 20px;
    }
    .original_price {
        color: #777
    }
    @media (max-width: 760px) {
        .deal-content-wrapper {
            display: block;
        }
        #sell_contaier .lp-breadcrumb {
            width: 100%;
        }

        .deal-item-images {
            width: 100%;
            margin: 0
        }
    }
</style>
@section('content')
    <div class="container">
        <div id="sell_contaier">
            <div class="top_header">
                <ul class="lp-breadcrumb">
                    <li>
                        <a class="breadcrumb-content" href="/">Home</a>
                    </li>
                    @if (isset($dataDeal->store) && !empty($dataDeal->store))
                        <li><a href="/deals/<?= $dataDeal->store->slug ?>">All Deals</a></li>
                    @endif
                    <li><?= $dataDeal->title ?></li>
                </ul>
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
                        <span class="pop-discount">
                            <?= ($dataDeal->discount > 0) ? $dataDeal->discount . '% <small>Discount</small>' : '' ?>
                        </span>
                        <div class="price-area">
                            <div class="product-price">
                                <span class="current_price"><?= $dataDeal->currency_code . $dataDeal->sale_price ?></span>
                                @if ( $dataDeal->discount >  0 )
                                    <del class="original_price"><?= $dataDeal->currency_code . $dataDeal->price ?></del>
                                @endif
                            </div>
                            <div class="deal_get_share" style="margin-top: 20px">
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
    </div>
    <?php if (isset($showPopup) && $showPopup) { ?>
    <div id="deal-pop" style="display: none">
        <div class="deal-content">
            <div class="popup-head">
                <h2 class="product-title" style="font-size: 19px; margin: 0">
                    <?= $dataDeal->title ?>
                </h2>
                <span class="expire">
                    Expire date: <span class="clock"> {{ timeOnGoing($dataDeal->expire_time) }}</span>
                </span>
                <span class="shipping" style="padding-left: 10px;">From by <a href="/store/<?= $dataDeal->store_slug ?>"><?= $dataDeal->store_title ?></a></span>
                <button type="button" class="close-popup" name="button">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-x" viewBox="0 0 16 16">
                        <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
                    </svg>
                </button>
            </div>
            <div class="product-detail-content">
                <span class="close-deal">
                    <span></span>
                    <span></span>
                </span>
                <div class="product-img" style="position: relative">
                    @if ($dataDeal->discount > 0)
                        <span class="pop-discount">{{ $dataDeal->discount }}% <small>Discount</small> </span>
                    @endif
                    <div class="deal-popup-image">
                        <img src="<?= $dataDeal->image ?>" alt="<?= $dataDeal->title ?>">
                    </div>
                </div>
                <div class="deal-detail">
                    <div style="display: flex; align-items: center; margin-bottom: 10px;">
                        <div class="product-price" style="margin-right: 20px;">
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
                    </div>
                    <?php if (!empty($dataDeal->content)) { ?>
                        <div class="more-content">
                            <?= $dataDeal->content ?>
                        </div>
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
