<?php

Route::group([
    'namespace' => '\Megaads\DealsPage\Controllers'
], function() {
    Route::get('/deals', 'DealsController@allDeals')->name('deal::all');
    Route::get('/deals/{slug}-deals', 'DealsController@index')->name('deal::detail')->where(['slug' => '[0-9a-zA-Z\-]+']);
    Route::get('/deals/{slug}', 'DealsController@listByStore')->name('deal::list::by::store');
    Route::get('/deals/{slug}/c/{itemId}','DealsController@listByStore')->where(['itemId' => '[0-9]+'])->name('deal::list::by::store::item');

    Route::get('/deal/{itemId}','DealController@dealDetail')->name('deal::detail');
    Route::get('/deal/c/{itemId}', 'DealsController@dealDetail')->name('deal::detail::item')->where(['itemId' => '[0-9]+']);
    Route::get('/go-deal/{slug}', 'DealsController@goUrl')->name('deal::action::go')->where(['slug' => '[0-9a-zA-Z\-]+']);
    Route::get('/deals/import', 'DealsController@import');
});