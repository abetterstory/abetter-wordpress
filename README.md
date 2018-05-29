# labs-abetter-wordpress

ABetter Wordpress integration for Laravel 5+

## Install laravel + abetter + requirements
- > composer create-project laravel/laravel
- > chmod -r 777 storage
- > chmod -r 777 bootstrap/cache
- > composer require abetter/wordpress
- > composer require intervention/image
- > composer require itsgoingd/clockwork --dev

## Add script command to root composer.json + update
- "post-update-cmd": [
	"ABetter\\Wordpress\\ComposerScripts::renameHelperFunctions"
]
- > composer update

## Install vanilla Wordpress in resources/wordpress/core

## Copy wp-config.php to resources/wordpress/core
- > cd resources/wordpress/core
- > cp ../../../vendor/abetter/wordpress/wp-config.php wp-config.php

## Create symlink to public/wp
- > cd public
- > ln -s ../resources/wordpress/core wp

## Create symlink to plugins & themes (first remove default)
- > cd resources/wordpress/core/wp-content
- > ln -s ../../../../vendor/abetter/wordpress/plugins plugins
- > ln -s ../../../../vendor/abetter/wordpress/themes themes

## Create symlink to uploads
- > cd resources/wordpress/core/wp-content
- > ln -s ../../../../storage/wordpress/uploads uploads

## Setup Wordpress core/wp-config.php + config.php
- require_once(dirname(__FILE__).'/../config.php');

## Install Wordpress and configure

## Activate Wordpress plugins + theme

## Add theme templates in templates.php

## Add system pages
- Start : start (Front page)
- News : news (Posts page)
- Privacy Policy : privacy-policy
- Search : search
- 404 Not Found : not-found
- 403 Forbidden : forbidden

## Setup routes/web.php:
- Route::get('/', '\ABetterWordpressController@handle');
- Route::get('{l?}/{y?}/{m?}/{d?}/{s?}/{x?}/{z?}/{q?}', '\ABetterWordpressController@handle');
- Route::get('wp-admin', function() {
    return redirect('/wp/wp-admin/');
});
