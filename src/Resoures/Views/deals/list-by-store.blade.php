@extends('frontend.layout.master')
@section('meta')
    @include('frontend.layout.meta', [ 'data' => json_decode(json_encode($store), true) ])
    <link rel="stylesheet" href="{{ asset('/vendor/deals-page/css/store-deal.css?v=' . time()) }}">
    <link rel="stylesheet" href="{{ asset('/vendor/deals-page/css/price-range.css?v=' . time()) }}">
@endsection
@section('content')
    @php
        use Megaads\Adsense\Utils\Adsense;
        $storeDescription = ($store->description);
        $rating = !empty($store->crawl_rating) ? $store->crawl_rating : $store->voteUp;
        $ratingCount = !empty($store->crawl_rating_count) ? $store->crawl_rating_count : $store->voteDown;
    @endphp
    <div class="store-desc-wrap is-mobile">
        <div class="store-img">
            <a href="{{ route('frontend::store::goStore', [ 'slug' => $store->slug ]) }}" target="_blank">
                <img class="lazy" src="{{ $placehoderImage }}" data-src="{{ App\Utils\Utils::reSizeImage("/images/stores/" . $store->coverImage, 100, 0) }}"  alt="<?= $store->title ?> Coupons & Promo codes">
            </a>
        </div>
        <div class="store-desc">
            <div class="store-name-box">
                <?= $_COOKIE['metaTitle'] ?>
            </div>
            <div class="store-desc-detail is-desktop">
                <div class="more-less-text" data-background-color="#fff" data-lines="1" data-more-text="More &raquo;" style="max-height: 21px; overflow: hidden;">{!! $storeDescription !!}</div>
            </div>
        </div>
        <div class="openfilter is-mobile">
            <img src="/images/slider-icon.svg" alt="" width="30" height="30">
        </div>
    </div>
    <div class="star-rating is-mobile" style="margin-left: 150px;">
        <div class="rate">
            <input type="radio" id="mb-star5" <?= round($store->voteUp) == 5?'checked':'' ?> name="mb-rate" value="5" />
            <label data-id="<?= $store->id; ?>" class="js-vote" for="mb-star5" title="5 stars">5 stars</label>
            <input type="radio" id="mb-star4" <?= round($store->voteUp) == 4?'checked':'' ?> name="mb-rate" value="4" />
            <label data-id="<?= $store->id; ?>" class="js-vote" for="mb-star4" title="4 stars">4 stars</label>
            <input type="radio" id="mb-star3" name="mb-rate" value="3" />
            <label data-id="<?= $store->id; ?>" class="js-vote" for="mb-star3" title="3 stars">3 stars</label>
            <input type="radio" id="mb-star2" name="mb-rate" value="2" />
            <label data-id="<?= $store->id; ?>" class="js-vote" for="mb-star2" title="2 stars">2 stars</label>
            <input type="radio" id="mb-star1" name="mb-rate" value="1" />
            <label data-id="<?= $store->id; ?>" class="js-vote" for="mb-star1" title="1 star">1 star</label>
        </div>
        <span class="count-rating">
        {{ $rating }} from <span>{{ $ratingCount }}</span> users
    </span>
        <div class="js-vote-message"></div>
    </div>
    <main id="main" data-role="list-by-store">
        <div class="page-full-width">
            <div class="subheader is-desktop">
                <div class="container">
                    <div class="viewstore-col col-sm-2 hidden-xs">
                        <a rel="nofollow" href="{{ route('frontend::store::goStore', [ 'slug' => $store->slug ]) }}" target="_blank" class="store-logo vertical">
                            <img src="{{ $placehoderImage }}" data-src="{{ App\Utils\Utils::reSizeImage("/images/stores/" . $store->coverImage, 100, 0) }}" alt="{{ $store->title }} Coupons & Promo codes" class="img-responsive lazy">
                        </a>
                    </div>
                    <div class="viewstore-col col-sm-10 about-store">
                        <h1>
                            <span class="store-subtitle"><?= $_COOKIE['metaTitle'] ?></span>
                        </h1>
                        <div style="clear: both"></div>
                        <div class="is-desktop" style="margin-bottom:15px">
                            <div class="more-less-text" data-background-color="#fff" data-lines="1" data-more-text="More &raquo;" style="max-height: 21px; overflow: hidden;">{!! $storeDescription !!}</div>
                        </div>
                        <div class="star-rating">
                            <div class="rate">
                                <input type="radio" id="star5" <?= round($store->voteUp) == 5?'checked':'' ?> name="rate" value="5" />
                                <label data-id="<?= $store->id; ?>" class="js-vote" for="star5" title="5 stars">5 stars</label>
                                <input type="radio" id="star4" <?= round($store->voteUp) == 4?'checked':'' ?> name="rate" value="4" />
                                <label data-id="<?= $store->id; ?>" class="js-vote" for="star4" title="4 stars">4 stars</label>
                                <input type="radio" id="star3" name="rate" value="3" />
                                <label data-id="<?= $store->id; ?>" class="js-vote" for="star3" title="3 stars">3 stars</label>
                                <input type="radio" id="star2" name="rate" value="2" />
                                <label data-id="<?= $store->id; ?>" class="js-vote" for="star2" title="2 stars">2 stars</label>
                                <input type="radio" id="star1" name="rate" value="1" />
                                <label data-id="<?= $store->id; ?>" class="js-vote" for="star1" title="1 star">1 star</label>
                            </div>
                            <span class="count-rating">
                            {!! sprintf('%s from <span>%s</span> users', $store->voteUp, $store->voteDown) !!}
                        </span>
                            <div class="js-vote-message"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="container main-container">
                <div class="row">
                    <div class="col-lg-9 col-md-9 col-sm-12 search-results">
                        <div class="list-deal-wrapper">
                            <div class="widget-title">
                                {{ sprintf('Top %s Deals (%s)', $store->title, date("M d, Y")) }}
                            </div>
                            @include('deals-page::keypage.inc.filter')
                            @if (!empty($listDeals))
                                <div id="deal-items-wrapper">
                                    @include('deals-page::common.widgets.list-deal', ['listDeal' => $listDeals, 'store' => $store, 'date' => '(' .date('d. M Y'). ')'])
                                </div>
                            @endif
                            @if (isset($hasNextPage) && $hasNextPage)
                            <div class="view-more-alldeal">
                                <a href="" id="js-view-more-deals" class="view-more-alldeal-link" onclick="loadmoreDeals(event);">
                                    {{ sprintf('Load More %s Products', $store->title) }}
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-right" viewBox="0 0 16 16"> <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/> </svg>
                                    <input type="hidden" id="deal-current-id" value="{{ $currentPage }}" />
                                </a>
                            </div>
                            @endif
                        </div>
                        @include('frontend.common.widgets.related-store', ['storeId' => $store->id, 'storeRelated' => $store->relatedTerms])
                        @if (isset($listCoupon) && !empty($listCoupon))
                            @include('frontend.common.widgets.list-coupon', ['coupons' => $listCoupon['result']['data'], 'store' => $store->title, 'date' => '(' .date('M d, Y'). ')', 'recordsCount' => $listCoupon['result']['recordsCount']])
                            <div class="view-more-alldeal">
                                <a href="{{ route('frontend::store::listByStore', [ 'slug' => $store->slug ]) }}" class="view-more-alldeal-link">
                                    {{ sprintf('See all %s Coupons', $store->title) }}
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-right" viewBox="0 0 16 16"> <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/> </svg>
                                </a>
                            </div>
                        @endif
                        @include('frontend.common.widgets.random-box', ['widgetTitle' => 'Related Categories',  'storeItem' => json_decode(json_encode($store), true)])
                        <div class="clear"></div>
                    </div>
                    <div class="col-lg-3 col-md-3 col-sm-12 left-menu">
                        @include('frontend.common.widgets.popular-categories')
                        @include('frontend.common.widgets.contact-info', [ 'store' => json_decode(json_encode($store), true) ])
                        <?= Adsense::display(['divClass' => 'section-top', 'adsenseStyle' => 'width: 285px; height: 216px;']) ?>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
    </main>
    {!! Breadcrumbs::render('store_page', $breadcrumbs) !!}
@endsection

@section('js')
    @parent
    <script defer src="/vendor/deals-page/js/price-range.js?v=<?= Config::get('app.version'); ?>"></script>
    <script>
        var storeImageUrl = '{{ App\Utils\Utils::reSizeImage("/images/stores/" . $store->coverImage, 100, 0) }}';
        var isLoading = false;
        var currentPage = {{ $currentPage }};
        var viewMoreDeal = document.getElementById('js-view-more-deals');
        var filterType = '{{ $dealFilterActivated }}';

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

            const listDealImage = document.getElementsByClassName('deal-thumb');
            for (const image of listDealImage) {
                image.addEventListener('error', function() {
                    image.src = storeImageUrl;
                });
            }

        });

        var favoriteDeals = "{{ isset($favoriteDeals) ? json_encode($favoriteDeals) : '' }}";
        var favoriteDealTitle = "{{ $store->title }}";
        if (favoriteDeals && favoriteDeals.length) {
            var template = `
                <h2>Favourite ${favoriteDealTitle} Coupons & Deals Today</h2>
                <table class="table-favorite">
                    <thead>
                        <tr>
                            <th>Coupon Detail</th>
                            <th>Discount Type</th>
                            <th>Expires Time</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            for(let item of favoriteDeals) {
                template += `
                    <tr>
                        <td>${item.title}</td>
                        <td>${item.discount_type ? item.discount_type.toUpperCase() : ''}</td>
                        <td>${item.onGoing}</td>
                    </tr>
                `;
            }
            template += `
                    </tbody>
                </table>
            `;
            var content = document.getElementsByClassName("mb-content");
            if (content && content.length > 0) {
                content = content[0];
                var wrapper = document.getElementById('toc-show-wrapper');
                var element = document.createElement("div");
                element.innerHTML = template;
                content.insertBefore(element, wrapper);
            }
        }

        function loadmoreDeals(e) {
            e = e || window.event;
            e.preventDefault();
            if (isLoading) return;
            isLoading = true;
            // var currentPageId = document.getElementById('deal-current-id').value;
            var dataParams = {
                store_id: {{ $store->id }},
                page_size: 5,
                current_page: parseInt(currentPage) + 1,
                dealType: filterType
            };
            if (filterType === 'price') {
                var minPrice = document.getElementById('input-min').value;
                var maxPrice = document.getElementById('input-max').value;
                dataParams.minPrice = minPrice;
                dataParams.maxPrice = maxPrice;
            }

            var queryString = Object.keys(dataParams).map((key) => {
                return encodeURIComponent(key) + '=' + encodeURIComponent(dataParams[key])
            }).join('&');

            var xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function() {
                if (this.readyState === 4 && this.status === 200) {
                    var response = JSON.parse(this.responseText);
                    if (response.status === 'successful') {
                        if (!response.has_next) {
                            viewMoreDeal.style.display = "none";
                        }
                        currentPage++;
                        document.getElementById("deal-items-wrapper").insertAdjacentHTML("afterend", response.data);
                    }

                    setTimeout(() => {
                        isLoading = false;
                    }, 300);

                }
            };
            xmlhttp.open("GET", "/stores/deals/load-more?" + queryString, true);
            xmlhttp.setRequestHeader("Authorization", "Basic YXBpOjEyM0AxMjNh")
            xmlhttp.send();
        }
    </script>
@endsection
