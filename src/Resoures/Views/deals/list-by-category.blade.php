@extends('frontend.layout.master')
@section('meta')
    @include('frontend.layout.meta', [ 'data' => json_decode(json_encode($category), true) ])
    <link rel="stylesheet" href="{{ asset('/vendor/deals-page/css/store-deal.css?v=' . time()) }}">
    <link rel="stylesheet" href="{{ asset('/vendor/deals-page/css/price-range.css?v=' . time()) }}">
@endsection
@section('content')
    @php
        use Megaads\Adsense\Utils\Adsense;
        $categoryDescription = ($category->description);
        $pageTitle = sprintf('Top %s Sale %s %s', $category->title, date('M'), date('Y'))
    @endphp
    <main id="main" data-role="list-by-category">
        <div class="page-full-width list-by-category">
            <div class="subheader">
                <div class="container">
                    <div class="viewstore-col col-sm-2 hidden-xs">
                        <a href="<?= route('frontend::category::listByCategory', [ 'slug' => $category->slug ]); ?>" target="_blank" class="store-logo vertical">
                            <img src="/images/blank.gif" data-src="{{ App\Utils\Utils::reSizeImage($category->image, 135, 0) }}" alt="{{ $pageTitle }}" class="img-responsive lazy">
                        </a>
                    </div>
                    <div class="viewstore-col col-sm-9 col-md-7 about-store">
                        <h1 class="store-subtitle">{{ $pageTitle }}</h1>
                    </div>
                </div>
            </div>
            <div class="container main-container">
                <div class="row">
                    <div class="col-lg-9 col-md-9 col-sm-12 search-results">
                        <div class="list-deal-wrapper">
                            @include('deals-page::keypage.inc.filter', ['codeText' => 'Best Price'])
                            @if (!empty($listDeals))
                                <div id="deal-items-wrapper">
                                    @include('deals-page::common.widgets.list-deal', ['listDeal' => $listDeals, 'store' => $category, 'date' => '(' .date('d. M Y'). ')'])
                                </div>
                            @endif
                            @if (isset($hasNextPage) && $hasNextPage)
                            <div class="view-more-alldeal">
                                <a href="javascript:void(0);" id="js-view-more-deals" class="view-more-alldeal-link" data-target="category" data-target-id="{{ $category->id }}">
                                    {{ sprintf('Load More %s Products', $category->title) }}
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-right" viewBox="0 0 16 16"> <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/> </svg>
                                    <input type="hidden" id="deal-current-id" value="{{ $currentPage }}" />
                                    <input type="hidden" id="deal-filter-type" value="{{ $dealFilterActivated }}" />
                                </a>
                            </div>
                            @endif
                        </div>
                        @include('deals-page::common.widgets.related-store', [
                                    'beforeTitle' => '<h2 class="is-desktop box-coupon-title">',
                                    'afterTitle' => '</h2>',
                                    'widgetTitle' => 'Top Store on Sale',
                                    'showCoupons' => false,
                                    'items' => isset($topSaleStore) ? $topSaleStore : NULL,
                                    'customRouteType' => 'storeDeal'
                                    ])
                        @if (isset($activeCoupons) && !empty($activeCoupons))
                            @include('frontend.common.widgets.list-coupon', [
                                        'widgetTitle' => sprintf('Recommend %s Coupons & All Working Code', $category->title),
                                        'coupons' => $activeCoupons,
                                        'store' => $category->title,
                                        'date' => '(' .date('M d, Y'). ')',
                                        'recordsCount' => count($activeCoupons)
                                      ])
                            <div class="view-more-alldeal">
                                <a href="{{ route('frontend::store::listByStore', [ 'slug' => $category->slug ]) }}" class="view-more-alldeal-link">
                                    {{ sprintf('See all %s Coupons', $category->title) }}
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-right" viewBox="0 0 16 16"> <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/> </svg>
                                </a>
                            </div>
                        @endif
                        @include('deals-page::common.widgets.related-store', [
                                          'beforeTitle' => '<h2 class="is-desktop box-coupon-title">',
                                          'afterTitle' => '</h2>',
                                          'categoryId' => $category->id,
                                          'widgetTitle' => 'Featured Stores'])
                        @include('frontend.common.widgets.random-box', ['widgetTitle' => 'Related Categories',  'storeItem' => json_decode(json_encode($category), true)])
                        <div class="clear"></div>
                    </div>
                    <div class="col-lg-3 col-md-3 col-sm-12 left-menu">
                        @include('deals-page::common.widgets.popular-categories', [
                                              'dataItems' => $similarSaleCate,
                                              'route' => 'frontend::category::deals',
                                              'widgetTitle' => sprintf('Similar %s Sale', $category->title)
                                              ])
                        @include('frontend.common.widgets.contact-info', [
                                    'store' => json_decode(json_encode($category), true),
                                    'showRating' => true,
                                    'hideReview' =>  true,
                                    'hideDeals' =>  true,
                                    'customStyle' => 'text-align:center'
                                    ])
                        <?= Adsense::display(['divClass' => 'section-top', 'adsenseStyle' => 'width: 285px; height: 216px;']) ?>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
    </main>
    {!! Breadcrumbs::render('category_page', $breadcrumbs) !!}
@endsection

@section('js')
    @parent
    <script type="text/javascript">
        var isLoading = false;
    </script>
    <script defer src="/vendor/deals-page/js/price-range.js?v=<?= Config::get('app.version'); ?>"></script>
    <script defer src="/vendor/deals-page/js/deal-common.js?v={{ config('app.version') }}"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function(event) {
            $('.favorite-related-stores').slick({
                slidesToShow: 6,
                slidesToScroll: 6,
                dots: false,
                infinite: false,
                speed: 500,
                arrows: true,
                responsive: [
                    {
                        breakpoint: 1280,
                        settings: {
                            slidesToShow: 5,
                            slidesToScroll: 5,

                        }
                    },
                    {
                        breakpoint: 992,
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
                            slidesToShow: 2,
                            slidesToScroll: 2,
                            dots: true,
                            arrows: false,
                        }
                    }
                ]
            });
        });

        document.addEventListener("click", function(e) {
            var elClass = e.target.getAttribute("class");
            if (elClass.indexOf("js-go-deals") !== -1) {
                var itemId = e.target.getAttribute('data-id');
                var originUrl = window.location.origin;
                var fullUrl = `${originUrl}/go-deal/${itemId}`;
                window.open(`${originUrl}/go-deal/${itemId}`);
            }
        });
    </script>
@endsection
