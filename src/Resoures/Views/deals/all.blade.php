@php
use App\Utils\Utils;
@endphp
@extends('frontend.layout.master')
@section('meta')
<link rel="stylesheet" href="/frontend/css/alldeal.css?v=<?= Config::get('app.version'); ?>">
@endsection

@section('content')
<main id="main" class="app-alldeals">
    <div class="page-full-width">
        <div class="subheader is-desktop">
            <div class="container">
                <div class="viewstore-col col-sm-2 hidden-xs">
                    <a rel="nofollow" href="https://couponforless.com/go-store/dell.com" target="_blank" class="store-logo vertical">
                        <img src="https://cfl.agoz.me/unsafe/100x0/left/top/smart/filters:quality(90)/couponforless.com/images/stores/2013_09_04_021606_dell_coupons.jpg" data-src="https://cfl.agoz.me/unsafe/100x0/left/top/smart/filters:quality(90)/couponforless.com/images/stores/2013_09_04_021606_dell_coupons.jpg" alt="Dell Coupons &amp; Promo codes" class="img-responsive lazy loaded" data-was-processed="true">
                    </a>
                </div>
                <div class="viewstore-col col-sm-10 about-store">
                    <h1>
                        <span class="store-subtitle">Dell XPS $150 Coupon 2023: 10 OFF Coupon &amp; Deals</span>
                    </h1>
                    <div style="clear: both"></div>
                    <div class="is-desktop" style="margin-bottom: 15px; position: relative; clear: both;">
                        <div class="more-less-text" data-background-color="#fff" data-lines="1" data-more-text="More »" style="max-height: 20px; overflow: hidden; line-height: 20px; -webkit-line-clamp: 1; height: 20px;">
                            <p>Enter Dell to shop for all great technology devices. Save more with Dell coupon codes, 20% discount codes, promo codes, free shipping codes, and deals.</p>
                        </div>
                        <a href="javascript:void(0)" class="more-less-anchor" style="line-height: 20px; background-color: rgb(255, 255, 255);">...More »</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container app-alldeals-wrapper">
        @include('deals-page::deals.inc.top-silder', ['categories' => $categories])
        @include('deals-page::deals.inc.recently-deals', ['deals' => $deals])
        @include('deals-page::deals.inc.top-brands-deals', ['stories' => $stories])
        @include('deals-page::deals.inc.top-products-searches', ['dealsTopSearch' => $dealsTopSearch])
        @include('deals-page::deals.inc.customer-reviews', ['storiesReview' => $storiesReview])
    </div>
</main>
@endsection
@section('js')
@parent
<script type="text/javascript" name="app-deasl-script">
    const cateSliderResponsive = [{
            breakpoint: 1280,
            settings: {
                slidesToShow: 5,
                slidesToScroll: 5,

            }
        },
        {
            breakpoint: 760,
            settings: {
                slidesToShow: 4,
                slidesToScroll: 4,
                dots: true,
                arrows: false,
            }
        },
        {
            breakpoint: 640,
            settings: {
                slidesToShow: 3,
                slidesToScroll: 3,
                dots: true,
                arrows: false,
            }
        }
    ];

    document.addEventListener("DOMContentLoaded", function(event) {
        resetSlider('.customer-reviews-list, .top-brands-deals-list', {
            showNumber: 10,
            scrollNmber: 10,
            speed: 500,
            isArrow: true,
            autoplaySpeed: 2500,
            isInfinite: false,
            responsive: cateSliderResponsive
        });
        resetSlider('.alldeals-top-silder', {
            showNumber: 8,
            scrollNmber: 8,
            speed: 500,
            isArrow: true,
            autoplaySpeed: 2500,
            isInfinite: false,
            responsive: cateSliderResponsive
        });
    });

    document.addEventListener("click", function(e) {
            var elClass = e.target.getAttribute("class");
            var parentElClass = e.target.parentElement.getAttribute('class');
            var element;
            if (elClass && elClass.indexOf("js-go-deals") !== -1) {
                element = e.target;
            } else if (parentElClass && parentElClass.indexOf("js-go-deals") !== -1) {
                element = e.target.parentElement;
            }

            if (element) {
                var itemId = element.getAttribute('data-id');
                var originUrl = window.location.origin;
                var fullUrl = `${originUrl}/go-deal/${itemId}`;
                window.open(`${originUrl}/go-deal/${itemId}`);
            }
        });
</script>
@endsection