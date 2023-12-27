<?php if(count($todayDeals) > 0){?>
<div class="aside-box aside-box-limited">
    <div class="aside-title widget-title"><?= $title ?></div>
    <ul class="aside-list gird js-relevant-list">
        <?php foreach ($todayDeals as $item) {?>
        <li class="truncate-item">
            › <a class="truncate-link" rel="nofollow" href="{{ route('deal::detail', ['slug' => $item->slug]) }}" title="<?= $item->title ?>"><?= $item->title ?></a>
        </li>
        <?php }?>
    </ul>

</div>
<?php } ?>
