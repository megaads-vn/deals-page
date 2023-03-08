@if (isset($keywords))
    <div class="item-box lp-footer {{ !empty($isStoreMobile) ? 'widget related-coupons' : '' }}">
        <div class="lp-footer-title">Similar Deal Page Search:</div>
        <div class="lp-footer-keyword">
            @if(!empty($keywords))
                <ul class="lp-footer-keyword-list" id="recently-search0">
                    @foreach ($keywords as $key => $item)
                        @if ($key % 3 == 0)
                            <li class="footer-keyword-item {{ $key > 17 ? " more-less-item hidden" : "" }}">
                                ›
                                <a class="footer-keyword-link" href="<?= route('frontend::keyword',['slug' => $item->slug])?>" title="<?= $item->keyword ?>"><?= $item->keyword ?></a>
                            </li>
                        @endif
                    @endforeach
                </ul>
            @endif
            @if(!empty($keywords))
                <ul class="lp-footer-keyword-list" id="recently-search0">
                    @foreach ($keywords as $key => $item)
                        @if ($key % 3 == 1)
                            <li class="footer-keyword-item {{ $key > 17 ? " more-less-item hidden" : "" }}">
                                ›
                                <a class="footer-keyword-link" href="<?= route('frontend::keyword',['slug' => $item->slug])?>" title="<?= $item->keyword ?>"><?= $item->keyword ?></a>
                            </li>
                        @endif
                    @endforeach
                </ul>
            @endif
            @if(!empty($keywords))
                <ul class="lp-footer-keyword-list" id="recently-search0">
                    @foreach ($keywords as $key => $item)
                        @if ($key % 3 == 2)
                            <li class="footer-keyword-item {{ $key > 17 ? " more-less-item hidden" : "" }}">
                                ›
                                <a class="footer-keyword-link" href="<?= route('frontend::keyword',['slug' => $item->slug])?>" title="<?= $item->keyword ?>"><?= $item->keyword ?></a>
                            </li>
                        @endif
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
@endif