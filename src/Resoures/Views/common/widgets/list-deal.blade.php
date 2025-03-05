@if (isset($listDeal) && count($listDeal) > 0)
    @php $internalLinkAttr = (isset($is_internal_link) && $is_internal_link) ? 'data-internal' : ''; @endphp
    @forelse ($listDeal as $index => $item)
        @include('deals-page::common.item', ['item' => $item, 'internalLinkAttr' => $internalLinkAttr])
    @empty
        <div style="padding: 5px 0 15px; font-size: 17px; margin-bottom: 25px; color: #444;">{{ __("There isn't any deal in here") }}</div>
    @endif
<script>
    document.addEventListener("click", function(e) {
        var elClass = e.target.getAttribute("class");
        if (elClass.indexOf("js-go-deals") !== -1) {
            var itemId = e.target.getAttribute('data-id');
            var originUrl = window.location.origin;
            var fullUrl = `${originUrl}/go-deal/${itemId}`;
            window.open(`${originUrl}/go-deal/${itemId}`);
        }
    });
</script>
@endif
