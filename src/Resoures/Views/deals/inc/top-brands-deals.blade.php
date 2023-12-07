<div class="alldeals-component top-brands-deals-wrapper">
    <h2 class="alldeals-component-title">
        Top Brands Deals
    </h2>
    <div class="top-brands-deals-list">
        @foreach ($stories as $item)
            <div class="top-brands-deals-item">
                <a href="{{ route("frontend::store::listByStore::page", ['slug' => $item->slug, 'page' => 'deals']) }}" class="top-brands-image round-100 bx-square">
                    <img src='{{ App\Utils\Utils::reSizeImage("/images/stores/{$item->image}", 168, 0) }}' alt="">
                </a>
                <a href="{{ route("frontend::store::listByStore::page", ['slug' => $item->slug]) }}" class="top-brands-link">
                    {{ $item->title }}
                </a>
            </div>
        @endforeach
    </div>
</div>