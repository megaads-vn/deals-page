<?php
if(!isset($type))
    $type = 'coupon';

if(!isset($route))
    $route = 'frontend::category::listByCategory';
if (!isset($dataItems)) {
    $categories = \App\Utils\Utils::getDataInternalRequests('category/find', [
        'type' => $type,
        'status' => 'enable',
        'pageSize' => 10,
        'orderBy' => 'couponCountDesc'
    ]);
} else {
    $categories = $dataItems;
}

?>
@if(isset($categories) && count($categories) > 0)
    <div class="widget">
        <h2 class="widget-title font-alt">{{ isset($widgetTitle) ? $widgetTitle : 'Popular Categories' }}</h2>
        <div class="widget-body">
            <ul class="blog-category-list list-unstyled">
                @foreach($categories as $category)
                    <li>
                        <a href="<?= route($route, ['slug' => $category->slug]); ?>">
                            <?= $category->title; ?> {{ isset($category->total) ? '(' . number_format($category->total, 0, '.', ',') . ' Products)' : '' }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

