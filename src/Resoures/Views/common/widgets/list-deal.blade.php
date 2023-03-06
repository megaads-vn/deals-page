<?php
use Megaads\Adsense\Utils\Adsense;
?>
@if (isset($listDeal))
    @include('deals-page::keypage.inc.filter')
    @forelse ($listDeal as $index => $item)
        @include('deals-page::common.item', ['item' => $item])
    @empty
        <div style="padding: 5px 0 15px; font-size: 17px; margin-bottom: 25px; color: #444;">There isn't any deal in here</div>
    @endif
@endif
