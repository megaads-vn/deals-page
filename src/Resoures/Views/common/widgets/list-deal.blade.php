<?php
use Megaads\Adsense\Utils\Adsense;
?>
@if (isset($listDeal))
    @forelse ($listDeal as $index => $item)
        @include('deals-page::common.item', ['item' => $item])
    @empty
        <div style="padding: 5px 0 15px; font-size: 17px; margin-bottom: 25px; color: #444;">There isn't any deal in here</div>
    @endif
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
