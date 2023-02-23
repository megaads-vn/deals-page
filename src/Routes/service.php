<?php

Route::group([
    'prefix' => 'service',
    'namespace' => '\Megaads\DealsPage\Controllers\Services',
    'middleware' => ['deals_auth', 'deals_cors']
], function () {
    Route::get('/deal', 'DealService@find')->name('deal::find');
    Route::any('/deal/find', 'DealService@find')->name('deal::find');
    Route::any('/deal/update', 'DealService@update')->name('deal::update');
    Route::any('/deal/create', 'DealService@create')->name('deal::create');
    Route::any('/deal/delete', 'DealService@delete')->name('deal::delete');
    Route::any('/deal/migrate-data', 'DealService@dealMigration')->name('deal::delete');
    Route::any('/deal/bulk-create', 'DealService@bulkCreate')->name('deal::bulk::create');
    Route::any('/deal/schedule-bulk-create', 'DealService@bulkCreateWithSchedule')->name('deal::bulk::create::queue');

    Route::get('/catalog', 'CatalogService@find')->name('catalog::find');
    Route::any('/catalog/bulk-create', 'CatalogService@bulkCreate')->name('catalog::bulk::create');
});