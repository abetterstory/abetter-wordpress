<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('wp-admin', function() { return redirect('/wp/wp-admin/'); });
Route::get('wp-admin/{path}', function() { return redirect('/wp/wp-admin/'); })->where('path','.*');
Route::get('/', '\ABetterWordpressController@handle');
Route::get('{path}', '\ABetterWordpressController@handle')->where('path','.*');
