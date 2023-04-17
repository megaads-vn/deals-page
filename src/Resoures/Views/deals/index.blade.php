@php
use App\Utils\Utils;
@endphp
@extends(config('deals-page.layouts.extends.name'), ['title' => $title])
@section('meta')
@include('frontend.layout.meta', ['data' => $meta])
<link rel="stylesheet" href="{{ asset('/vendor/deals-page/css/deal.css?v=' . time())  }}">
@endsection

@section(config('deals-page.layouts.section.content'))
<?php
if (isset($localSchema)) {
    $localSchema = str_replace('#meta_title', $_COOKIE['metaTitle'], $localSchema);
    $localSchema = str_replace('#meta_description', $_COOKIE['metaDescription'], $localSchema);
    echo $localSchema;
}
?>
<main id="main" data-role="landingpage">
    <div class="container">
        <div class="lp-head-box">
            <h1 class="lp-heading">{{ $page->keyword }}</h1>
        </div>
        <!-- landingpage content -->
        <div class="lp-container">
            <div class="lp-results">
                <ul class="lp-breadcrumb">
                    <li>
                        <a class="breadcrumb-content" href="/">Home</a>
                    </li>
                    <li>
                        <span class="breadcrumb-content">{{ $page->keyword }}</span>
                    </li>
                </ul>
                <div class="lp-top-keyword">
                    <div class="lp-list-coupon">
                        <!-- coupon item -->
                        @if(isset($deals) && !empty($deals))
                            <div class="list-deal-wrapper">
                                @foreach ($deals as $item)
                                    @include('deals-page::common.item', ['item' => $item])
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
</main>
@endsection
@section(config('deals-page.layouts.section.javascript'))

@endsection
