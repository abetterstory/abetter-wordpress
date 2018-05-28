# labs-abetter-wordpress
ABetter Wordpress integration for Laravel 5+

## Install laravel + abetter + requirements
> composer create-project laravel/laravel
> chmod -r 777 storage
> chmod -r 777 bootstrap/cache
> composer require abetter/wordpress
> composer require intervention/image
> composer require itsgoingd/clockwork --dev

## Install vanilla Wordpress in resources/wordpress

## Copy default theme to resources/wordpress/wp-content/themes
> cp -r vendor/abetter/wordpress/theme resources/wordpress/wp-content/themes/abetter

## Create symlink to public/wp
> cd public
> ln -s ../resources/wordpress wp

## Create symlink to uploads
> cd resources/wordpress/wp-content
> ln -s ../../../storage/wordpress/uploads ./uploads

## Setup Wordpress wp-config.php

## Install Wordpress and configure

## Activate ABetter theme

## Setup routes/web.php:
Route::get('/', '\ABetterWordpressController@handle');
Route::get('{l?}/{y?}/{m?}/{d?}/{s?}/{x?}/{z?}/{q?}', '\ABetterWordpressController@handle');
Route::get('wp-admin', function() {
    return redirect('/wp/wp-admin/');
});
