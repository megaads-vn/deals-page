@php
    $currentUrl = Request::url();
@endphp
<form id="deal-filter-form" action="{{ $currentUrl }}" method="POST">
    <div class="deal-button-filter-wrapper">
        <label class="deal-filter-label" for="deal-all">
            <input class="deal-input" id="deal-all" type="radio"
                   {{ isset($dealFilterActivated) && $dealFilterActivated == 'all' ? 'checked' : '' }}
                   name="deal-filter">
            <span class="deal-filter-item" type="button">
                                            All
                                        </span>
        </label>
        <label class="deal-filter-label" for="deal-code">
            <input class="deal-input" id="deal-code" type="radio"
                   {{ isset($dealFilterActivated) && $dealFilterActivated == 'code' ? 'checked' : '' }}
                   name="deal-filter">
            <span class="deal-filter-item" type="button">
                                            Best Price
                                        </span>
        </label>
        <label class="deal-filter-label" for="deal-offer">
            <input class="deal-input" id="deal-offer" type="radio"
                   {{ isset($dealFilterActivated) && $dealFilterActivated == 'offer' ? 'checked' : '' }}
                   name="deal-filter">
            <span class="deal-filter-item" type="button">
                                            Top Offer
                                        </span>
        </label>
        <label class="deal-filter-label" for="deal-newest">
            <input class="deal-input" id="deal-newest" type="radio"
                   {{ isset($dealFilterActivated) && $dealFilterActivated == 'newest' ? 'checked' : '' }}
                   name="deal-filter">
            <span class="deal-filter-item" type="button">Newest</span>
        </label>

        <label class="deal-filter-label" for="deal-price">
            <input class="deal-input" id="deal-price" type="radio"
                   {{ isset($dealFilterActivated) && $dealFilterActivated == 'price' ? 'checked' : '' }}
                   name="deal-filter">
            <span class="deal-filter-item" type="button">Set Price</span>
        </label>
    </div>
    @include('deals-page::keypage.inc.price-range')
</form>
