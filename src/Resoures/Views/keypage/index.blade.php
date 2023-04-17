@php
    use App\Utils\Utils;
@endphp
@extends('frontend.layout.master', ['title' => $title])
@section('meta')
    @include('frontend.layout.meta', ['data' => $meta])
    <style>
        .aside-table{width:100%}
        .aside-table th,td{text-align:left;width:50%}
        .aside-box .mb-content{margin-bottom:20px}
        @media (max-width:760px){
            #deal-filter-form{position: sticky; top: 90px; background-color: #fff; z-index: 11; width: 100vw; transform: translateX(-14px); overflow: hidden;}
            .deal-button-filter-wrapper {padding-left: 10px}
            .keydeal-heading { font-size: 21px; margin-top: 12px; line-height: 1.3; }
        }
    </style>
    <link rel="stylesheet" href="/frontend/css/slick.css?v=<?= Config::get('app.version'); ?>">
    <link rel="stylesheet" href="{{ asset('/vendor/deals-page/css/deal.css?v=' . time()) }}">
    <link rel="stylesheet" href="{{ asset('/vendor/deals-page/css/price-range.css?v=' . time()) }}">
@endsection
@section('content')
    <?php
    if (isset($localSchema)) {
        $localSchema = str_replace('#meta_title', $_COOKIE['metaTitle'], $localSchema);
        $localSchema = str_replace('#meta_description', $_COOKIE['metaDescription'], $localSchema);
        echo $localSchema;
    }
    ?>
    <script>var slugKeyword = '<?= $keyword['slug']?>'</script>
    <main id="main" data-role="landingpage">
        <div class="container">
            <div class="lp-head-box">
                <h1 class="lp-heading keydeal-heading"><?= $_COOKIE['metaTitle'] ?></h1>
            </div>
            <!-- landingpage content -->
            <div class="lp-container">
                <div class="lp-results">
                    <ul class="lp-breadcrumb">
                        <li>
                            <a class="breadcrumb-content" href="/">Home</a>
                        </li>
                        <li>
                            <span class="breadcrumb-content"><?= $keyword['keyword']?></span>
                        </li>
                    </ul>
                    @include('deals-page::keypage.inc.filter')
                    <div class="lp-top-keyword">
                        <div class="lp-list-coupon">
                            <!-- coupon item -->
                            <div class="item-box js-active-box list-deal-wrapper">
                                @include('deals-page::common.widgets.list-deal', ['listDeal' => $listDeal, 'store' => $keyword['keyword'], 'date' => '(' .date('d. M Y'). ')'])
                            </div>
                            @if(!empty($recommendedCoupons) && view()->exists('frontend.common.item'))
                                <div class="item-box js-related-box">
                                    <h2 class="box-coupon-title">Recommended {{$keyword['keyword']}} Coupons:</h2>
                                    @foreach ($recommendedCoupons as $index => $item)
                                        @include('frontend.common.item', ['item' => $item])
                                    @endforeach
                                </div>
                            @endif
                            @if (!empty($similarSearch))
                                @include('deals-page::keypage.similar', ['keywords' => $similarSearch])
                            @endif
                            @if (!empty($relatedCategory))
                                @include('deals-page::keypage.related-category', ['relatedCategories' => $relatedCategory])
                            @endif
                            @if (!empty($relatedStore))
                                @include('deals-page::keypage.related-store', ['stores' => $relatedStore])
                            @endif
                        </div>
                    </div>
                    @if(isset($contentTemplateFAQ) && !empty($contentTemplateFAQ))
                        @include('deals-page::keypage.inc.contentTemplateFAQ', ['contentTemplateFAQ' => $contentTemplateFAQ])
                    @else
                        {!!$keyword['content']!!}
                    @endif
                </div>
                <aside class="lp-aside">
                    @if(isset($contentTemplate) && !empty($contentTemplate) && !Utils::isMobile())
                        @include('deals-page::keypage.inc.contentTemplate', [
                        'contentTemplate' => $contentTemplate
                        ])
                    @endif
                    @if (isset($statistic))
                        @include('frontend.keyword.inc.statistic', [
                        'statistic' => $statistic
                        ])
                    @endif
                    @if (isset($todayDeals))
                        @include('deals-page::common.today-deals', ['title' => $keyword['keyword'] . ' Today','todayDeals' => $todayDeals])
                    @endif
                    @if(isset($contentTemplate) && !empty($contentTemplate) && Utils::isMobile())
                        @include('frontend.keyword.inc.contentTemplate', [
                        'contentTemplate' => $contentTemplate
                        ])
                    @endif
                    @if (isset($relevantSearch))
                        @include('frontend.common.widgets.keyword', ['title' => 'Relevant Search', 'keywords' => $relevantSearch,'storeId' => $keyword['store_id'],])
                    @endif
                    @if (!empty($storeItem))
                        @include('deals-page::common.widgets.contact-info', [ 'store' => $storeItem, 'showRating' => true ])
                    @endif
                    <?= \Megaads\Adsense\Utils\Adsense::display(['divClass' => 'section-top', 'adsenseStyle' => 'width: 100%; height: 216px;']) ?>
                </aside>
                <div class="filter-container-background">
                </div>
            </div>
        </div>
    </main>
@endsection
@section('js')
    <script defer src="/frontend/js/slick.js?v=<?= Config::get('app.version'); ?>"></script>
    <script defer src="/vendor/deals-page/js/price-range.js?v=<?= Config::get('app.version'); ?>"></script>
    <script>
        const targets = document.querySelectorAll('div.lazy-home-box');
        function resetSlider (selector) {
            $(selector).slick({
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
        }

        document.addEventListener("DOMContentLoaded", function(event) {
            resetSlider('.favorite-related-stores');
        });
    </script>
@endsection
