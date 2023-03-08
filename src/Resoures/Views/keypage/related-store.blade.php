<?php
$placehoderImage = "/images/blank.gif";
?>
@if(isset($stores) && count($stores) > 0)
    <div class="item-box widget">
        <h2 class="box-coupon-title lp-footer-title">Related Stores</h2>
        <div class="favorite-related-stores" id="relatedStore">
            @foreach($stores as $store)
                <div class="related-item">
                    <a class="related-link" href="<?= route('frontend::store::listByStore', [ 'slug' => $store['slug'] ]); ?>" target="_blank" title="<?= $store['title']; ?>">
                        <div class="related-image">
                            <img src="{{ $placehoderImage }}" width="122" height="71" data-src="{{ reSizeImage("/images/stores/" . $store['coverImage'], 220, 0) }}" class="img-responsive lazy" alt="{{ $store['title'] }} Coupons & Promo codes">
                        </div>
                        <div class="related-title">
                            <?= $store['title']; ?> Coupons
                        </div>
                    </a>
                </div>
            @endforeach
        </div>

    </div>

@endif