@php $showStore = isset($showStore) ? $showStore : true; @endphp
<div class="deal-item-wrapper">
    <a class="deal-item-image" rel="nofollow" href="{{ route('deal::action::go', ['slug' => $item->slug]) }}" rel="nofollow">
        @if ($item->discount > 0)
        <span class="deal-tag">
            <span>{{ $item->discount }}%</span>
            <small>OFF</small>
        </span>
        @endif
        <img class="zoom" src="{{ $item->image }}" alt="{{ $item->title }}">
    </a>
    <div class="deal-item-info">
        <a target="_blank" rel="nofollow" href="{{ route('deal::action::go', ['slug' => $item->slug]) }}" class="box-top">
            <h3 class="deal-item-header">{{ $item->title }}</h3>
        </a>
        <div class="deal-item-description">
            {!! $item->content !!}
        </div>
        <div class="deal-item-footer">
            <div class="deal-item-price-box">
                @if ($item->discount <= 0)
                    <span class="sale-price">{{ $item->currency_code . "" . $item->price }}</span>
                @elseif ($item->discount > 0)
                    <span class="sale-price">{{ $item->currency_code . "" . $item->sale_price }}</span>
                    <span class="hight-price">{{ $item->currency_code . "" . $item->price }}</span>
                @endif
            </div>
            <div class="deal-item-footer-box">
                @if (!empty($item->store) && $showStore)
                    <a target="_blank" href="{{ route('frontend::store::listByStore', ['slug' => $item->store->slug]) }}" class="name">
                        Store: <span>{{ $item->store->name }}</span>
                    </a>
                @endif
                @if (!empty($item->category))
                    <a target="_blank" href="{{ route('frontend::category::listByCategory', ['slug' => $item->category->slug]) }}" class="name">
                        Category: <span>{{ $item->category->title }}</span>
                    </a>
                @endif
                <div>
                    Expired: {{ App\Utils\Utils::timeOnGoing($item->expired_time) }}
                </div>
            </div>
            <div class="deal-item-action">
                <a class="deal-item-button" rel="nofollow" href="{{ route('deal::action::go', ['slug' => $item->slug]) }}">Shop Now</a>
            </div>
        </div>
    </div>
    <!---->
</div>