@php
    use App\Utils\Utils;
@endphp
@extends('frontend.layout.master')
@section('meta')
    <title>All {{ $totalDeals }} Products on Sale</title>
    <link rel="stylesheet" href="/frontend/css/alldeal.css?v=<?= Config::get('app.version'); ?>">
    <link rel="stylesheet" href="{{ asset('/frontend/css/circle-slider.css?v=' . time()) }}">
@endsection

@section('content')
    <main id="main" class="app-alldeals">
        <div class="page-full-width">
            <div class="subheader is-desktop">
                <div class="container">
                    <div class="viewstore-col col-sm-10 about-store">
                        <h1>
                            <span class="store-subtitle">All {{ $totalDeals }} Products on Sale</span>
                        </h1>
                    </div>
                </div>
            </div>
        </div>
        <div class="container app-alldeals-wrapper">
            @include('deals-page::deals.inc.top-silder', ['categories' => $categories])
            @include('deals-page::deals.inc.recently-deals', ['deals' => $deals])
            @include('deals-page::deals.inc.top-brands-deals', ['stories' => $stories])
            @include('deals-page::deals.inc.top-products-searches', ['dealsTopSearch' => $dealsTopSearch])
            @include('frontend.home.customer-reviews', [
                                      'storiesReview' => $storiesReview,
                                      'topReviewBoxTitle' => 'Customer Reviews',
                                      'showStar' => true
                                      ])
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

        document.addEventListener("DOMContentLoaded", function (event) {
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

        document.addEventListener("click", function (e) {
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

        window.addEventListener('load', function () {
            var categoryImages = document.querySelectorAll('.category-image');
            categoryImages.forEach(function (image) {
                if ((typeof image.naturalWidth !== "undefined" && image.naturalWidth === 0) || image.readyState === 'uninitialized') {
                    image.setAttribute('src', '/images/noimage.png?v=' + <?= Config::get('app.version'); ?>);
                }
            });
        });
    </script>
@endsection
