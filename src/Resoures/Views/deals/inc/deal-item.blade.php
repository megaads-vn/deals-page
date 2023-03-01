<li class="lf shaw deals">
    <div class="deals-inner">
        <div class=" deal-image-wrapper js-deal" data-clipboard-text="{{ $item->id }}" data-id="{{ $item->id }}">
            <img src="{{ $item->image }}" class="deal-image" alt="{{ $item->title }}">
        </div>
        <div class="main-tit">
            @if (isset($item->store) && !empty($item->store))
                <div class="text-ell">
                    From <a href="{{ route($storeRoute, ['slug' => $item->store->slug]) }}">{{ $item->store->name }}</a>
                </div>
            @endif
            <h3 class="deal-title js-deal" data-clipboard-text="{{ $item->id }}" data-id="{{ $item->id }}">
                {{ $item->title }}
            </h3>
            <span class="deal-expired">Expire: {{ !empty($item->expired_at) ? $item->expired_at : 'On going' }}</span>
        </div>
        <div class="deal-price">
            <span class="big">${{ $item->sale_price }}</span>
            <del class="small-big">${{ $item->price }}</del>
        </div>
        <button class="deal-shopnow js-deal" data-clipboard-text="{{ $item->id }}" data-id="{{ $item->id }}">
            Shop Now
        </button>
        <div class="offstyle">
            <span class="shop">{{ $item->discount }}%</span>
            <small>Off</small>
        </div>
    </div>
</li>