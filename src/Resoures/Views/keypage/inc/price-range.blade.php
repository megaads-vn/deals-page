@php
$hideClass = 'price-range-hide';
if (isset($dealFilterActivated) && $dealFilterActivated == 'price') {
    $hideClass = '';
}
@endphp
<div id="price-range-input" class="wrapper {{ $hideClass }}">
    <div class="input-wrap">
        <div class="price-input">
            <div class="field">
                <span>Min</span>
                <input type="number" class="input-min" value="{{ isset($priceRange) ? $priceRange[0] : 0 }}">&nbsp;$
            </div>
            <div class="separator">-</div>
            <div class="field">
                <span>Max</span>
                <input type="number" class="input-max" value="{{ isset($priceRange) ? $priceRange[1] : 2500 }}">&nbsp;$
            </div>
        </div>
        <div class="field deal-apply-filter-btn">
            <span id="deal-filter-btn" class="deal-item-button">Apply</span>
        </div>
    </div>

<!--     <div class="slider">
        <div class="progress"></div>
    </div>
    <div class="range-input">
        <input type="range" class="range-min" min="0" max="10000" value="{{ isset($priceRange) ? $priceRange[0] : 0 }}" step="100">
        <input type="range" class="range-max" min="0" max="10000" value="{{ isset($priceRange) ? $priceRange[1] : 2500 }}" step="100">
    </div> -->
</div>
