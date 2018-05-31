# labs-abetter-wordpress

ABetter Wordpress integration for Laravel 5+

## Install laravel + abetter + requirements
- > composer create-project laravel/laravel
- > chmod -r 777 storage
- > chmod -r 777 bootstrap/cache
- > composer require abetter/wordpress

## Install optional dev tools
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

## Setup Wordpress config in .env

## Create symlink to public/wp
- > cd public
- > ln -s ../resources/wordpress/core wp

## Create symlinks to wp-content
- > cd resources/wordpress/core/wp-content
- > ln -s ../../../../vendor/abetter/wordpress/plugins plugins
- > ln -s ../../../../vendor/abetter/wordpress/themes themes
- > ln -s ../../../../storage/wordpress/uploads uploads

## Install Wordpress and configure

## Activate Wordpress plugins + theme

## Add theme functions in resources/wordpress/functions.php
## Add theme templates in resources/wordpress/templates.php
## Add editor styles in resources/wordpress/editor.css

## Add system pages
- Start : start (Front page)
- News : news (Posts page)
- Privacy Policy : privacy-policy
- Search : search
- 404 Not Found : not-found
- 403 Forbidden : forbidden

## Add laravel routes/web.php:
- Route::get('/', '\ABetterWordpressController@handle');
- Route::get('{l?}/{y?}/{m?}/{d?}/{s?}/{x?}/{z?}/{q?}', '\ABetterWordpressController@handle');
- Route::get('wp-admin', function() {
    return redirect('/wp/wp-admin/');
});
