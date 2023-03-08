@if (isset($relatedCategories))
<div class="item-box lp-footer {{ !empty($isStoreMobile) ? 'widget related-coupons' : '' }}">
    <div class="lp-footer-title">Related Categories</div>
    <div class="lp-footer-keyword">
        @if(!empty($relatedCategories))
        <ul class="lp-footer-keyword-list" id="recently-search0">
            @foreach ($relatedCategories as $key => $item)
                @if ($key % 3 == 0)
                    <li class="footer-keyword-item {{ $key > 17 ? " more-less-item hidden" : "" }}">
                        ›
                        <a class="footer-keyword-link" href="<?= route('frontend::category::listByCategory',['slug' => $item['slug']])?>" title="<?= $item['title'] . ' coupons' ?>"><?= $item['title'] . ' coupons' ?></a>
                    </li>
                @endif
            @endforeach
        </ul>
        @endif
        @if(!empty($relatedCategories))
            <ul class="lp-footer-keyword-list" id="recently-search0">
                @foreach ($relatedCategories as $key => $item)
                    @if ($key % 3 == 1)
                        <li class="footer-keyword-item {{ $key > 17 ? " more-less-item hidden" : "" }}">
                            ›
                            <a class="footer-keyword-link" href="<?= route('frontend::category::listByCategory',['slug' => $item['slug']])?>" title="<?= $item['title'] . ' coupons' ?>"><?= $item['title'] . ' coupons' ?></a>
                        </li>
                    @endif
                @endforeach
            </ul>
        @endif
        @if(!empty($relatedCategories))
            <ul class="lp-footer-keyword-list" id="recently-search0">
                @foreach ($relatedCategories as $key => $item)
                    @if ($key % 3 == 2)
                        <li class="footer-keyword-item {{ $key > 17 ? " more-less-item hidden" : "" }}">
                            ›
                            <a class="footer-keyword-link" href="<?= route('frontend::category::listByCategory',['slug' => $item['slug']])?>" title="<?= $item['title'] . ' coupons' ?>"><?= $item['title'] . ' coupons' ?></a>
                        </li>
                    @endif
                @endforeach
            </ul>
        @endif
    </div>
</div>
@endif