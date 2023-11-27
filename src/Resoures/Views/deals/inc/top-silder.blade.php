<div class="alldeals-top-silder">
    @foreach ($categories as $item)
        <div class="top-brands-deals-item">
            <a target="_blank" href="http://" class="top-brands-image round-100 bx-square">
                <img class="category-image" src="{{ App\Utils\Utils::reSizeImage($item->image, 168, 0) }}" data-src="{{ App\Utils\Utils::reSizeImage($item->image, 168, 0) }}" alt="{{ $item->title }}">
            </a>
            <a href="" class="top-brands-link">
                {{ $item->title }}
            </a>
        </div>
    @endforeach
</div>