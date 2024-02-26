@php $showStore = isset($showStore) ? $showStore : true; @endphp
<div class="deal-item-wrapper">
    <a class="deal-item-image" rel="nofollow" href="{{ route('deal::detail', ['slug' => $item->slug]) }}" title="{{ $item->title }}" rel="nofollow">
        @if ($item->discount > 0)
        <span class="deal-tag">
            <span>{{ $item->discount }}%</span>
            <small>OFF</small>
        </span>
        @endif
        @if (isset($store->coverImage))
        <img class="zoom lazy deal-thumb" src="{{ App\Utils\Utils::reSizeImage("/images/stores/" . $store->coverImage, 100, 0) }}" data-src="{{ $item->image }}" alt="{{ $item->title }}" />
        @else
        <img class="zoom lazy deal-thumb" src="{{ $item->image }}" data-src="{{ $item->image }}" alt="{{ $item->title }}" />
        @endif
    </a>
    <div class="deal-item-info">
        <a target="_blank" rel="nofollow" href="{{ route('deal::detail', ['slug' => $item->slug]) }}" title="{{ $item->title }}" class="box-top">
            <h3 class="deal-item-header">{{ $item->title }}</h3>
        </a>
        <div class="deal-item-description">
            {{ strip_tags($item->content) }}
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
                    Store:&nbsp;
                    <a target="_blank" href="{{ route('frontend::store::listByStore', ['slug' => $item->store->slug]) }}" title="{{ $item->store->name }}" class="name">
                        <span>{{ $item->store->name }}</span>
                    </a>
                @endif
                @if (!empty($item->category))
                    Category:&nbsp;
                    <a target="_blank" href="{{ route('frontend::category::listByCategory', ['slug' => $item->category->slug]) }}" title="{{ $item->category->title }}" class="name">
                        <span>{{ $item->category->title }}</span>
                    </a>
                @endif
                <div>
                    Expired: {{ App\Utils\Utils::timeOnGoing($item->expired_time) }}
                </div>
            </div>
            <div class="deal-item-action">
                <span class="deal-item-button js-go-deals" rel="nofollow" data-id="{{ $item->id }}">Shop Now</span>
            </div>
        </div>
    </div>
    <!---->
</div>
