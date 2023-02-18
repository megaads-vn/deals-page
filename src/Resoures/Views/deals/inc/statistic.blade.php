<div class="aside-box">
    <h2 class="aside-title widget-title">
        {{ $keyword['keyword'] }} {{ date('M Y') }}
    </h2>
    <div class="mb-content">
        @if(count($statistic['top_coupons']))
        <ul>
            @foreach($statistic['top_coupons'] as $item)
                <li>{{ $item['title'] }}</li>
            @endforeach
        </ul>
        @endif
    </div>
</div>