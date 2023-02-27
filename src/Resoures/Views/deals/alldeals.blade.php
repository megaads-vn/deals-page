@extends('frontend.layout.master')
@section('js')
    @parent
<script defer src="{{ asset('/vendor/deals-page/js/deals-page.js?v=' . time()) }}"></script>
@endsection
@section('content')
    <main id="main_coupon" class="deal_main is-store-detail is-deals-detail">
        <div class="deal_con">
            <div class="deal_textCon">
                <h1 class="text_center">All Deals</h1>
                <p class="deal_mainp"></p>
            </div>
        </div>
        <div class="main_dealbg">
            <div class="deal_content" id="main_inner">
                <div class="deallist_tab">
                    <div class="list listbig">
                        <div id="brand-list" class="dropdown">
                            <p class="tab_button" data-slug="<?= !empty($brands->slug) ? $brands->slug:''?>"><?= !empty($brands->title)?$brands->title:$brands ?> <i class="fa-down"></i></p>
                            <ul id="list-deals" class="deals_ul">
                                <?php foreach ($stores as $store) { ?>
                                <li>
                                    <a class="data-tid" data-tid="<?= $store->id ?>" data-slug="<?= $store->slug ?>"><?= $store->title ?></a>
                                </li>
                                <?php } ?>
                            </ul>
                        </div>
                    </div>
                    <form action="/deals/" method="get" id="cates_submit" class="list search-deals-form">
                        <div class="listserach">
                            <input type="text" class="serach_input" autocomplete="off" name="search" placeholder="Refine By Keyword" value="<?= isset($_GET['search'])?urldecode($_GET['search']):''?>">
                        </div>
                    </form>
                    <div class="list listsmall list-latest active">
                        <p class="tab_button">Find</p>
                    </div>
                </div>
                @if (count($deals)<1)
                <div class="deals_null">
                    Sorry, nothing was searched.
                </div>
                @else
                <ul class="tabs_list clear deal-list">
                    @foreach ($deals as $item)
                        @include('deals-page::deals.inc.deal-item', ['item' => $item])
                    @endforeach
                </ul>

                <?php
                if ($pagination['page_count'] > 1) {
                    echo pagination(3, $pagination['total_count'], 30, $_GET);
                }
                ?>
                @endif
            </div>
        </div>
        <?php if(isset($dataDeal) && !empty($dataDeal)){?>
        <div id="deal-pop" style="display: block">
            <div class="deal-content">
                <div class="product-detail-content">
                <span class="close-deal">
                    <span></span>
                    <span></span>
                </span>
                    <div class="product-img">
                        <img src="/images/deals/<?= $dataDeal->image_url?>" alt="<?= $dataDeal->title?>">
                    </div>
                    <div class="deal-detail">
                        <p class="expire">
                            Expire date: <span class="clock"> <?= App\Utils\Utils::timeOnGoing($dataDeal->expire_time) ?></span>
                        </p>
                        <h2 class="product-title">
                            <a href="<?= route('frontend::deal::detail', ['itemItem' => $dataDeal->id]) ?>"><?= $dataDeal->title?></a>
                        </h2>
                        <p class="shipping">From by <a href="/store/<?= $item->store_slug ?>"><?= $dataDeal->store_title ?></a></p>
                        <?php
                        $temp = $dataDeal->old_price - $dataDeal->price;
                        ?>
                        <?php if($temp>0){?>
                        <p class="product-discount"><?= App\Utils\Utils::calPercent($dataDeal->price, $dataDeal->old_price) ?>% discount</p>
                        <?php }?>
                        <div class="product-price">
                            <?php if ($temp > 0) { ?>
                            <span class="original_price"><?= $dataDeal->currency ?><?= $dataDeal->old_price?></span>
                            <?php } ?>

                            <span class="current_price"><?= $dataDeal->currency ?><?= $dataDeal->price ?></span>
                        </div>
                        <div class="deal_get_share">
                            <a target="_blank" href="<?= route("frontend::deal::go",['id'=>$dataDeal->id])?>" class="deal_get">
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
                <?php
                if (count($otherCoupon) > 0) {
                ?>
                <div class="deal-next-li">
                    <h2 class="next-title">More: <?= $dataDeal->store_title ?> coupons</h2>
                    <ul class="listed-none">
                        <?php foreach ($otherCoupon as $itemCoupon) { ?>
                        <li>
                            <a href="<?= route('frontend::deal::detail', ['itemId' => $itemCoupon->id]); ?>"> <?= $itemCoupon->title; ?></a>
                        </li>
                        <?php } ?>
                    </ul>
                </div>
                <?php } ?>
            </div>
            <div class="deal-backgkround"></div>
        </div>
        <?php } ?>
    </main>
@endsection
