@php $showStore = isset($showStore) ? $showStore : true; @endphp
<div class="deal-item-wrapper">
    <a class="deal-item-image" rel="nofollow" href="javascript:void(0);" data-id="{{ $item->id }}" rel="nofollow">
        @if ($item->discount > 0)
        <span class="deal-tag">
            <span>{{ $item->discount }}%</span>
            <small>OFF</small>
        </span>
        @endif
        @if (isset($store->coverImage))
        <img class="zoom lazy deal-thumb js-go-deals" src="{{ App\Utils\Utils::reSizeImage("/images/stores/" . $store->coverImage, 100, 0) }}" data-src="{{ $item->image }}" alt="{{ $item->title }}" />
        @else
        <img class="zoom lazy deal-thumb js-go-deals" src="{{ $item->image }}" data-src="{{ $item->image }}" alt="{{ $item->title }}" />
        @endif
    </a>
    <div class="deal-item-info">
        <a target="_blank" rel="nofollow" href="javascript:void(0);" data-id="{{ $item->id }}" class="box-top">
            <h3 class="deal-item-header js-go-deals">{{ $item->title }}</h3>
        </a>
        <div class="deal-item-description">
            {!! $item->content !!}
        </div>
        <div class="deal-item-footer">
            <div class="deal-item-price-box">
                @if ($item->discount <= 0)
                    <span class="sale-price">{{ $item->currency_code . "" . number_format($item->price, 2, '.', ',') }}</span>
                @elseif ($item->discount > 0)
                    <span class="sale-price">{{ $item->currency_code . "" . number_format($item->sale_price, 2, '.', ',') }}</span>
                    <span class="hight-price">{{ $item->currency_code . "" . number_format($item->price, 2, '.', ',') }}</span>
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
                <a class="deal-item-button js-go-deals" rel="nofollow" href="javascript:void(0);" data-id="{{ $item->id }}">Shop Now</a>
            </div>
        </div>
    </div>
    <!---->
</div>
