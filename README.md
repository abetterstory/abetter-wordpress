# LABS-ABetter-Wordpress v1.1.6

ABetter Wordpress integration for Laravel 5+

## Install laravel + abetter + requirements
- composer create-project laravel/laravel
- mkdir resources/wordpress
- mkdir storage/wordpress/uploads
- chmod -r 777 storage
- chmod -r 777 bootstrap/cache
- chmod -r 777 resources/wordpress
- chmod -r 777 storage/wordpress/uploads
- composer require abetter/wordpress

## Install optional dev tools
- composer require itsgoingd/clockwork --dev

## Add script command to root composer.json + update
- "post-update-cmd": ["ABetter\\Wordpress\\ComposerScripts::renameHelperFunctions"]
- composer update

## Setup Wordpress config in .env
- WP_DEBUG=
- WP_HOME=
- WP_DB_NAME=
- WP_DB_USER=
- WP_DB_PASSWORD=
- WP_DB_HOST=
- WP_DB_CHARSET=
- WP_DB_COLLATE=
- WP_DB_PREFIX=
- WP_AUTH_KEY=
- WP_SECURE_AUTH_KEY=
- WP_LOGGED_IN_KEY=
- WP_NONCE_KEY=
- WP_AUTH_SALT=
- WP_SECURE_AUTH_SALT=
- WP_LOGGED_IN_SALT=
- WP_NONCE_SALT=

## Create symlink to public/wp
- cd public
- ln -s ../vendor/abetter/wordpress/core wp

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
- 404 Not Found : 404-not-found
- 403 Forbidden : 403-forbidden

## Add laravel routes/web.php:
- Route::get('wp-admin', function () { return redirect('/wp/wp-admin/'); });
- Route::get('wp-admin/{any}', function () { return redirect('/wp/wp-admin/'); });
- Route::get('/', '\ABetterWordpressController@handle');
- Route::get('{l?}/{y?}/{m?}/{d?}/{s?}/{x?}/{z?}/{q?}', '\ABetterWordpressController@handle');
