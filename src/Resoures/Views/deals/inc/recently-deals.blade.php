<div class="alldeals-component recently-deals-wrapper">
    <h2 class="alldeals-component-title">
        Recently Deals
    </h2>
    <div class="recently-deals-list">
        @foreach ($deals as $item)
            <div class="recently-deals-item">
                <a class="bx-square deal-product-item-image" href="{{ route('deal::detail', ['slug' => $item->slug]) }}" title="{{ $item->title }}">
                    <img src="{{ $item->image }}" alt="{{ $item->title }}">
                </a>
                <div class="item-info">
                    <h3 class="deal-product-item">
                        <a class="deal-product-item-link" href="{{ route('deal::detail', ['slug' => $item->slug]) }}" title="{{ $item->title }}" >{{ $item->title }}</a>
                    </h3>
                    <div class="price">
                        @if ($item->sale_price > 0)
                            <div class="current">
                                {{ sprintf('%s%s', '$', $item->sale_price) }}
                            </div>
                            <div class="old">
                                {{ sprintf('%s%s', '$', $item->price) }}
                            </div>
                        @else
                            <div class="current">
                                {{ sprintf('%s%s', '$', $item->price) }}
                            </div>
                        @endif
                    </div>
                    <div>
                        @if ($item->store_name)
                            <div class="deal-expires">
                               Store: <a target="_blank" href="{{ route("frontend::store::listByStore", ['slug' => $item->store_slug]) }}" title="{{ $item->store_name }}">{{ $item->store_name }}</a>
                            </div>
                        @endif
                        @if ($item->category_name)
                            <div class="deal-expires">
                                Category: <a target="_blank" href="{{ route('frontend::category::listByCategory',['slug' => $item->category_slug]) }}" title="{{ $item->category_name }}">{{ $item->category_name }}</a>
                            </div>
                        @endif
    
                        <div class="deal-expires">
                            {{ sprintf('Expires: %s', App\Utils\Utils::timeOnGoing($item->expire_time)) }}
                        </div>
    
                    </div>
                    <a class="get-btn btn-pc js-go-deals" data-id="{{ $item->id }}" href="javascript:void(0)">
                        Shop Now
                    </a>
                </div>
            </div>
        @endforeach
    </div>
</div>
