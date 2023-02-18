<div class="aside-box">
    <div class="aside-header">
        @if (isset($keyword['image_url']))
        <div class="round-box">
            <img src="{{ $keyword['image_url'] }}" />
        </div>
        @endif
        <h2 class="aside-title widget-title">{{ $keyword['keyword'] }}</h2>
    </div>
    <div class="mb-content">
        <?= $contentTemplate ?>
    </div>
</div>