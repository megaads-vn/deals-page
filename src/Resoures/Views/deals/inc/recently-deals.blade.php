<div class="alldeals-component recently-deals-wrapper">
    <h2 class="alldeals-component-title">
        Recently Deals
    </h2>
    <div class="recently-deals-list">
        @foreach ($deals as $item)
            <div class="recently-deals-item">
                <a class="bx-square deal-product-item-image" href="http://">
                    <img src="{{ $item->image }}" alt="">
                </a>
                <h3 class="deal-product-item">
                    <a href="http://">{{ $item->title }}</a>
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
                           Store: <a target="_blank" href="{{ route("frontend::store::listByStore", ['slug' => $item->store_slug]) }}">{{ $item->store_name }}</a>
                        </div>
                    @endif
                    @if ($item->category_name)
                        <div class="deal-expires">
                            Category: <a target="_blank" href="{{ route('frontend::category::listByCategory',['slug' => $item->category_slug]) }}">{{ $item->category_name }}</a>
                        </div>
                    @endif

                    <div class="deal-expires">
                        {{ sprintf('Expires: %s', App\Utils\Utils::timeOnGoing($item->expire_time)) }}
                    </div>

                </div>
                <a class="get-btn btn-pc" href="{{ route("frontend::store::listByStore", ['slug' => $item->store_slug]) }}">
                    Shop Now
                </a>
            </div>
        @endforeach
    </div>
</div>