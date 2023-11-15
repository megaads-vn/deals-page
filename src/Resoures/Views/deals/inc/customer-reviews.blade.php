<div class="alldeals-component customer-reviews-wrapper">
    <h2 class="alldeals-component-title">
        Customer Reviews
    </h2>
    <div class="customer-reviews-list">
        @foreach ($storiesReview as $item)
            <div class="top-brands-deals-item">
                <a href="{{ route("frontend::store::listByStore", ['slug' => $item->slug]) }}" class="top-brands-image round-100 bx-square">
                    <img src='{{ App\Utils\Utils::reSizeImage("/images/stores/{$item->image}", 168, 0) }}' alt="">
                </a>
                <a href="{{ route("frontend::store::listByStore", ['slug' => $item->slug]) }}" class="top-brands-link">
                    {{ $item->title }}
                </a>
            </div>
        @endforeach
    </div>
</div>