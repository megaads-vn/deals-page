@extends('frontend.layout.master')
@section('meta')
    @include('frontend.layout.meta', ['data' => $meta])
    <link rel="stylesheet" href="{{ asset('/vendor/deals-page/css/deal-detail.css?v=' . time())  }}">
    <link rel="stylesheet" href="{{ asset('/vendor/deals-page/css/deal.css?v=' . time())  }}">
@endsection
@section('content')
    <?php use App\Utils\Utils; ?>
    <main id="main">
        @section('breadcrumbs', Breadcrumbs::render('deal_detail', $breadcrumbs))
        <div class="container">
            <div class="row product-detail-wapper">
                <div class="col-sm-12 col-md-12">
                    <div class="product-page">
                        <div class="product-image">
                            <a href="#" class="product-logo-link">
                                <img src="{{ $detailItem->image }}" class="img-responsive" alt="{{ $detailItem->title }}">
                            </a>
                        </div>
                        <div class="product-info-right">
                            <div class="product-time-info">
                                <div class="date">
                                    <div class="calendar"><svg t="1695279559608" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="6194" width="20" height="20" class="icon"><path data-v-6a0ebef0="" d="M270.984 223.669c-27.146 0-49.157-22.011-49.157-49.161V49.157C221.828 22.011 243.838 0 270.984 0c27.15 0 49.161 22.011 49.161 49.157v125.351c0 27.15-22.01 49.161-49.161 49.161zM751.266 223.669c-27.146 0-49.158-22.011-49.158-49.161V49.157C702.107 22.011 724.119 0 751.266 0c27.15 0 49.156 22.011 49.156 49.157v125.351c0 27.15-22.006 49.161-49.156 49.161zM311.836 891.936h-146.92c-15.533 0-28.124-12.592-28.124-28.125V716.892c0-15.534 12.591-28.125 28.124-28.125h146.92c15.533 0 28.124 12.591 28.124 28.125v146.919c0 15.533-12.591 28.125-28.124 28.125zM585.458 631.89h-146.92c-15.533 0-28.125-12.591-28.125-28.125V456.846c0-15.533 12.592-28.125 28.125-28.125h146.92c15.533 0 28.124 12.592 28.124 28.125v146.919c0 15.534-12.591 28.125-28.124 28.125zM585.458 891.936h-146.92c-15.533 0-28.125-12.592-28.125-28.125V716.892c0-15.534 12.592-28.125 28.125-28.125h146.92c15.533 0 28.124 12.591 28.124 28.125v146.919c0 15.533-12.591 28.125-28.124 28.125zM859.083 631.89h-146.92c-15.533 0-28.124-12.591-28.124-28.125V456.846c0-15.533 12.591-28.125 28.124-28.125h146.92c15.533 0 28.125 12.592 28.125 28.125v146.919c0 15.534-12.592 28.125-28.125 28.125zM859.083 891.936h-146.92c-15.533 0-28.124-12.592-28.124-28.125V716.892c0-15.534 12.591-28.125 28.124-28.125h146.92c15.533 0 28.125 12.591 28.125 28.125v146.919c0 15.533-12.592 28.125-28.125 28.125z" fill="#1373EB" p-id="6195"></path><path data-v-6a0ebef0="" d="M910.608 116.914h-59.312v57.594c0 54.678-44.481 99.161-99.156 99.161-54.676 0-99.158-44.483-99.158-99.161v-57.594H371.02v57.594c0 54.678-44.483 99.161-99.161 99.161-54.675 0-99.157-44.483-99.157-99.161v-57.594h-59.311C50.77 116.914 0.005 167.679 0.005 230.3v680.315C0.006 973.235 50.771 1024 113.392 1024h797.216c62.621 0 113.385-50.765 113.385-113.386V230.299c0-62.621-50.764-113.385-113.385-113.385z m53.385 793.7c0 29.437-23.949 53.386-53.385 53.386H113.392c-29.437 0-53.386-23.949-53.386-53.386V361.02h903.987v549.594z" fill="#1373EB" p-id="6196"></path></svg></div>
                                    <div class="date-text">
                                        {{ Utils::timeOnGoing($detailItem->publish_time, 'F d, Y') }}
                                    </div>
                                </div>
                            </div>
                            <h1 class="product-title">
                                <a href="javascript:void(0)" class="js-go-deals" data-id="{{ $detailItem->id }}" data-clipboard-text="{{ $detailItem->title }}">{{ $detailItem->title }}</a>
                            </h1>
                            <div class="product-cate-store-link">
                                @if(isset($dealCategories) && !empty($dealCategories))
                                    <a href="{{ route('frontend::category::listByCategory', ['slug' => $dealCategories->slug]) }}" title="{{ $dealCategories->title  }}}">{{ $dealCategories->title }}</a>
                                    <span class="vertical-line"> | </span>
                                @endif
                                @if(isset($dealStore) && !empty($dealStore))
                                    <a href="{{ route('frontend::store::listByStore', ['slug' => $dealStore->slug]) }}" title="{{ $dealStore->title  }}}">{{ $dealStore->title }}</a>
{{--                                    <span class="vertical-line"> | </span>--}}
                                @endif
                            </div>
                            <div class="manufacturer">
                              <span>Manufacturer:</span> {{ $detailItem->manufacturer }}
                            </div>
                            <div class="upc-or-ean">
                              <span>UPC or EAN:</span> {{ $detailItem->upc_or_ean }}
                            </div>
                            <div class="product-price">
                                <div class="product-price-reverse">
                                    @if ($detailItem->discount > 0)
                                        <div class="price-discount">
                                            <svg t="1693991245327" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="6573" width="20" height="20" class="icon"><path data-v-6a0ebef0="" d="M806.2464 893.952H220.5184c-84.6336 0-153.4464-68.8128-153.4464-153.4464v-142.3872c0-24.832 16.9984-46.1824 41.3696-51.8656 19.6608-4.608 33.3824-21.9136 33.3824-42.0864s-13.7216-37.4784-33.3824-42.0864c-24.3712-5.6832-41.3696-27.0336-41.3696-51.8656V274.5856c0-84.5824 68.8128-153.4464 153.4464-153.4464h585.728c84.5824 0 153.4464 68.8128 153.4464 153.4464v135.2704c0 25.2416-17.3568 46.6432-42.1376 52.0704-19.712 4.3008-33.9968 22.0672-33.9968 42.24s14.2848 37.9392 33.9968 42.24c24.832 5.4272 42.1888 26.8288 42.1888 52.0704v142.08c-0.0512 84.5312-68.864 153.3952-153.4976 153.3952zM138.752 611.84v128.6656c0 45.1072 36.6592 81.7664 81.7664 81.7664h585.728c45.1072 0 81.7664-36.6592 81.7664-81.7664V612.352c-44.9024-16.1792-76.1856-59.5456-76.1856-108.1856s31.2832-92.0064 76.1856-108.1856V274.5856c0-45.1072-36.6592-81.7664-81.7664-81.7664H220.5184c-45.1072 0-81.7664 36.6592-81.7664 81.7664v121.856c44.6464 16.5376 74.752 58.9312 74.752 107.6736 0 48.7424-30.1056 91.1872-74.752 107.7248z m763.5456 4.5568h0.0512-0.0512z" fill="#1273EB" p-id="6574"></path><path data-v-6a0ebef0="" d="M412.7232 477.44c-63.3856 0-114.9952-51.6096-114.9952-114.9952s51.6096-114.9952 114.9952-114.9952 114.9952 51.6096 114.9952 114.9952-51.6096 114.9952-114.9952 114.9952z m0-158.3104c-23.9104 0-43.3152 19.4048-43.3152 43.3152s19.4048 43.3152 43.3152 43.3152 43.3152-19.456 43.3152-43.3152-19.456-43.3152-43.3152-43.3152zM598.1696 757.0944c-63.3856 0-114.9952-51.6096-114.9952-114.9952s51.6096-114.9952 114.9952-114.9952 114.9952 51.6096 114.9952 114.9952c0 63.4368-51.6096 114.9952-114.9952 114.9952z m0-158.3104c-23.9104 0-43.3152 19.456-43.3152 43.3152 0 23.9104 19.456 43.3152 43.3152 43.3152s43.3152-19.4048 43.3152-43.3152c0-23.8592-19.456-43.3152-43.3152-43.3152zM355.4304 686.592c-9.216 0-18.432-3.5328-25.4464-10.5984-13.9264-14.0288-13.8752-36.7616 0.2048-50.688l297.0112-294.7584c14.0288-13.9264 36.7616-13.8752 50.688 0.2048 13.9264 14.0288 13.8752 36.7616-0.2048 50.688l-297.0112 294.7584a35.48672 35.48672 0 0 1-25.2416 10.3936z" fill="#1273EB" p-id="6575"></path></svg>
                                            {{ $detailItem->discount . '%' }}
                                        </div>
                                    @endif
                                        <div class="price">
                                            @if  ($detailItem->sale_price > 0)
                                                <div class="current">{{ $detailItem->currency_code . '' . $detailItem->sale_price }}</div>
                                                <div class="old">{{ $detailItem->currency_code . '' . $detailItem->price }}</div>
                                            @else
                                                <div class="current">{{ $detailItem->currency_code . '' . $detailItem->price }}</div>
                                            @endif
                                        </div>
                                </div>
                            </div>
                            <div class="btn-line"></div>
                            <div class="get-btn btn-pc js-go-deals" data-id="{{ $detailItem->id }}">Shop Now</div>
                            <div class="product-info-desc">
                                <div class="title">Description:</div>
                                <span id="see-more-btn" class="see-more-text js-click-see-more">See More...</span>
                                <div id="text-out-desc" class="text-out" style="height: 42px;">
                                    <div class="text">
                                        {{ strip_tags($detailItem->content) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @if (isset($otherStoreDeal) && count($otherStoreDeal) > 0)
                    @include('deals-page::common.column-item', [
                                'boxTitle' => 'More Deals From Store',
                                'deals' => $otherStoreDeal,
                                'totalDeals' => $storeTotalDeals,
                                'customWrapper' => 'more-store-deals',
                                'heading' => 3,
                                'seeAllUrl' => !empty($dealStore) ? route('frontend::store::listDeal', ['slug' => $dealStore->slug]) : '/alldeals'
                             ])
                    @endif
                    @if (isset($relatedDeal) && count($relatedDeal) > 0)
                        @include('deals-page::common.column-item', [
                                     'boxTitle' => 'Related Deals',
                                     'deals' => $relatedDeal,
                                     'totalDeals' => $totalRelatedDeal,
                                     'heading' => 3
                                 ])
                    @endif
                    @if (isset($relatedCateDeal) && count($relatedCateDeal) > 0)
                        @include('deals-page::deals.inc.top-silder',
                                 [
                                     'categories' => $relatedCateDeal,
                                     'cateSlideBoxTitle' => 'Related Cate Deals',
                                     'seeAllUrl' => !empty($dealCategories) ? route('frontend::category::deals', ['slug' => $dealCategories->slug]) : '/alldeals'
                                 ])
                    @endif
                    @if (isset($relatedStoreReviews) && count($relatedStoreReviews) > 0)
                      @include('frontend.home.customer-reviews', [
                                  'storiesReview' => $relatedStoreReviews,
                                  'topReviewBoxTitle' => 'Related Store Customer Reviews',
                                  'customWrapperStyle' => 'reviews-wrapper',
                                  'showStar' => true
                                  ])
                    @endif
                </div>
            </div>

        </div>
        <style>
            @media (max-width: 767px) {
                .coupon-page-body {
                    width: 100%;
                    float: none;
                }
            }
        </style>
    </main>
@endsection
@section('js')
    @parent
    <script>
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

        const dealsResponsive = [{
          breakpoint: 1280,
          settings: {
            slidesToShow: 5,
            slidesToScroll: 5,

          }
        },
          {
            breakpoint: 760,
            settings: {
              slidesToShow: 2,
              slidesToScroll: 2,
              dots: true,
              arrows: false,
            }
          },
          {
            breakpoint: 640,
            settings: {
              slidesToShow: 2,
              slidesToScroll: 2,
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
                responsive: dealsResponsive
            });
        });

        document.addEventListener("click", function (e) {
            var elClass = e.target.getAttribute("class");
            console.log("elClass=", elClass);
            var parentElClass = e.target.parentElement.getAttribute('class');
            var element;
            if (elClass && elClass.indexOf("js-go-deals") !== -1) {
                element = e.target;
            } else if (parentElClass && parentElClass.indexOf("js-go-deals") !== -1) {
                element = e.target.parentElement;
            } else if (elClass && elClass.indexOf("js-click-see-more") !== -1) {
                const textOutEl = document.getElementById('text-out-desc');
                const seeMoreBtn = document.getElementById('see-more-btn');
                e.target.classList.add('disable-see-more');
                textOutEl.removeAttribute('style');
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
