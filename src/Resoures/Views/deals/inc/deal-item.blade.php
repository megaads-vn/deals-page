<li class="lf shaw deals">
    <div class="deals_inner">
        <div class="small">
            <a href="javascript:void(0);" rel="nofollow" target="_blank" class="js-deal" data-clipboard-text="{{ $item->id }}" data-id="{{ $item->id }}">
                <div class="d-log">
                    <img src="{{ $item->image }}" class="deal-image" height="120" alt="{{ $item->title }}">
                </div>
            </a>
            <div class="main_tit">
                @if (isset($item->store))
                    <p class="text_ell">
                        From <a href="<?= route("frontend::store::listByStore", ['slug' => $item->store->slug]) ?>"><?= $item->store->name ?></a>
                    </p>
                @endif
                <a href="javascript:void(0);" class="main_title js-deal" data-clipboard-text="<?= $item->id ?>" data-id="<?= $item->id ?>"><?= $item->title ?></a>
                <span class="deal-expired">Expire: <?= App\Utils\Utils::timeOnGoing($item->expire_time) ?></span>
            </div>
            <p>
                <span class="big"><?= $item->currency_code ?><?= $item->price ?></span>
                <?php if ($item->discount > 0) { ?>
                <span class="small_big"><?= $item->currency_code ?><?= $item->sale_price ?></span>
                <?php } ?>
            </p>
        </div>
        <div class="amazon_footer">
            <div class="deals_bot">
                <span class="shopnow js-deal" data-clipboard-text="<?= $item->id ?>" data-id="<?= $item->id ?>">
                    <span class="shop">Shop Now</span>
                </span>
            </div>
        </div>
        <?php if ($item->discount > 0) { ?>
        <div class="offstyle">
            <span class="shop">{{ $item->discount }}% off</span>
        </div>
        <?php } ?>
    </div>
</li>