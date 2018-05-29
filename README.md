# labs-abetter-wordpress

ABetter Wordpress integration for Laravel 5+

## Install laravel + abetter + requirements
> composer create-project laravel/laravel
> chmod -r 777 storage
> chmod -r 777 bootstrap/cache
> composer require abetter/wordpress
> composer require intervention/image
> composer require itsgoingd/clockwork --dev

## Add script command to root composer.json + update
"post-update-cmd": [
	"ABetter\\Wordpress\\ComposerScripts::renameHelperFunctions"
]
> composer update

## Install vanilla Wordpress in resources/wordpress

## Copy theme + plugins to resources/wordpress/wp-content/
> cp -r vendor/abetter/wordpress/theme resources/wordpress/wp-content/themes/abetter
> cp -r vendor/abetter/wordpress/plugins resources/wordpress/wp-content/plugins

## Create symlink to public/wp
> cd public
> ln -s ../resources/wordpress wp

## Create symlink to uploads
> cd resources/wordpress/wp-content
> ln -s ../../../storage/wordpress/uploads ./uploads

## Setup Wordpress wp-config.php

## Install Wordpress and configure

## Install Wordpress plugins

## Activate ABetter theme

## Add system pages
Start : start (Front page)
News : news (Posts page)
Privacy Policy : privacy-policy
Search : search
404 Not Found : not-found
403 Forbidden : forbidden

## Setup routes/web.php:
Route::get('/', '\ABetterWordpressController@handle');
Route::get('{l?}/{y?}/{m?}/{d?}/{s?}/{x?}/{z?}/{q?}', '\ABetterWordpressController@handle');
Route::get('wp-admin', function() {
    return redirect('/wp/wp-admin/');
});
