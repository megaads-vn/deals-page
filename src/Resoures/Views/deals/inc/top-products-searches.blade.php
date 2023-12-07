@if (isset($dealsTopSearch['items']) && count($dealsTopSearch['items']) > 0)
    <div class="alldeals-component top-products-searches-wrapper">
        <h2 class="alldeals-component-title">
            {{ $dealsTopSearch['boxTitle'] }}
        </h2>
        @php $boxItems = $dealsTopSearch['items']; @endphp
        <input type="checkbox" id="moreProductsSearches">
        <div class="top-products-searches-list">
            @foreach ($boxItems as $item)
            
                <a href="{{ $item->link }}"  rel="nofollow" class="top-products-searches-item">
                    <img src="/images/trending.svg" width="16" height="16" alt="{{ $item->name }}">
                    <span>{{ $item->name }}</span>
                </a>
            @endforeach
        </div>
        @if (count($boxItems) > 20)
            <label for="moreProductsSearches">
                <span class="more">Show more</span>
                <span class="less">Less</span>
            </label>
        @endif
    </div>
@endif
