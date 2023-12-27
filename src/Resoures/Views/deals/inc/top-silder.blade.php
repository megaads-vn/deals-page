<div class="alldeals-component recently-deals-wrapper">
    @if (isset($cateSlideBoxTitle))
        <h3 class="alldeals-component-title">
            {{ $cateSlideBoxTitle }}
        </h3>
    @endif
    <div class="alldeals-top-silder">
        @foreach ($categories as $item)
            <div class="top-brands-deals-item">
                <a target="_blank" href="{{ route('frontend::category::deals',['slug' => $item->slug]) }}" class="top-brands-image round-100 bx-square">
                    <img class="category-image" src="{{ App\Utils\Utils::reSizeImage($item->image, 168, 0) }}" data-src="{{ App\Utils\Utils::reSizeImage($item->image, 168, 0) }}" alt="{{ $item->title }}">
                </a>
                <a href="{{ route('frontend::category::deals',['slug' => $item->slug]) }}" class="top-brands-link">
                    {{ $item->title }}
                </a>
            </div>
        @endforeach
    </div>
</div>
