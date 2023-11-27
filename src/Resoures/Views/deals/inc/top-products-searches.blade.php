@if (count($dealsTopSearch) > 0)
    <div class="alldeals-component top-products-searches-wrapper">
        <h2 class="alldeals-component-title">
            Top Products Searches
        </h2>
        <input type="checkbox" id="moreProductsSearches">
        <div class="top-products-searches-list">
            @foreach ($dealsTopSearch as $item)
            
                <a href="javascript:void(0)" data-id="{{ $item->id }}"  rel="nofollow" class="top-products-searches-item js-go-deals">
                    <img src="/images/trending.svg" width="16" height="16" alt="Copyright @CouponForLess">
                    <span>{{ $item->title }}</span>
                </a>
            @endforeach
        </div>
        @if (count($dealsTopSearch) > 20)
            <label for="moreProductsSearches">
                <span class="more">Show more</span>
                <span class="less">Less</span>
            </label>
        @endif
    </div>
@endif