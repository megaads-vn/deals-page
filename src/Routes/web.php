<?php

Route::group([
    'namespace' => '\Megaads\DealsPage\Controllers'
], function() {
    //    Route::get('/deals', 'DealsController@index');
    Route::get('/deals/{slug}', 'DealsController@index')->name('deal::old::detail')->where(['slug' => '[0-9a-zA-Z\-]+']);
//    Route::get('/{slug}-deals', 'DealsController@index')->name('deal::detail')->where(['slug' => '[0-9a-zA-Z\-]+\-deals']);
    Route::get('/go/{slug}-deals', 'DealsController@goUrl')->name('deal::action::go')->where(['slug' => '[0-9a-zA-Z\-]+']);
    Route::get('/deals/import', 'DealsController@import');
});