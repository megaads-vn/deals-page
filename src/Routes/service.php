<?php

Route::group([
    'prefix' => 'service',
    'namespace' => '\Megaads\DealsPage\Controllers\Services'
], function () {
    Route::get('/deal', 'DealService@find')->name('deal::find');
    Route::any('/deal/bulk-create', 'DealService@bulkCreate')->name('deal::bulk::create');

    Route::get('/catalog', 'CatalogService@find')->name('catalog::find');
    Route::any('/catalog/bulk-create', 'CatalogService@bulkCreate')->name('catalog::bulk::create');
});