@if(isset($category) && !empty($category))
    @php $dealItems = getDealCategory($category->id);@endphp
    @if (count($dealItems) > 0)
        <div class="list-deal-wrapper">
            <div class="widget-title">
                Current <?= $category->title ?>  Deals (<?= date("M d, Y"); ?>)
            </div>
            @foreach ($dealItems as $item)
                @include('deals-page::common.item', ['item' => $item, 'showStore' => false])
            @endforeach
            <div class="view-more-alldeal" style="margin-bottom: 20px">
                <a href="{{ route('frontend::category::deals', ['slug' => $category->slug]) }}" class="view-more-alldeal-link">
                    View all {{ $category->title }} Products
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-right" viewBox="0 0 16 16"> <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/> </svg>
                </a>
            </div>
        </div>
        <link rel="stylesheet" href="{{ asset('/vendor/deals-page/css/deal.css?v=' . time()) }}">
    @endif
@endif