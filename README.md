# A Better Wordpress

[![Packagist Version](https://img.shields.io/packagist/v/abetter/wordpress.svg)](https://packagist.org/packages/abetter/wordpress)
[![Latest Stable Version](https://poser.pugx.org/abetter/wordpress/v/stable.svg)](https://packagist.org/packages/abetter/wordpress)
[![Total Downloads](https://poser.pugx.org/abetter/wordpress/downloads.svg)](https://packagist.org/packages/abetter/wordpress)
[![License](https://poser.pugx.org/abetter/wordpress/license.svg)](https://packagist.org/packages/abetter/wordpress)

ABetter Wordpress is a turnkey solution for using Wordpress on top of Laravel to build exceptionally fast web applications – while still using the worlds most popular CMS to manage content and translations.

Our methodology to fast web applications is all about Separation of Concerns (SoC) and Scalable Static Caching. We let Wordpress handle the content back-end and Laravel the standalone front-end. Additional API and web services for dynamic content are also routed through Laravel.

With the ABetter Toolkit we give Laravel/Blade some new powerful directives helping us separate as much as possible in standalone and resusable components – much inspired by ReactJS/VueJS.

---

## Requirements

* PHP 7.2+
* MySQL 5.7+
* Composer 1.6+
* Laravel 5.8+
* Deployer 6+
* Node 10.0+
* NPM 6.4+

---

## Installation

Via Composer:

```bash
composer create-project laravel/laravel .
composer require abetter/wordpress
```

#### Laravel modifications

Add post install/update script to composer.json:

```bash
"scripts": {
	"post-install-cmd": [
		"ABetter\\Wordpress\\ComposerScripts::postInstall"
	],
	"post-update-cmd": [
		"ABetter\\Wordpress\\ComposerScripts::postUpdate"
	]
}
```

Note: The script will modify any core files using the global __() method for string translations and add a cross-framework workaround. Sadly wordpress core do not check for function_exists before defining global __(), which breaks Laravel + Wordpress compatibility without modification.

Add middleware to app/Http/Kernel.php:

```bash
protected $middleware = [
	\ABetter\Toolkit\SandboxMiddleware::class,
	\ABetter\Wordpress\Middleware::class,
];
```

Note: The middleware helps Blade clear the view cache when developing many nested components.

---

## Preparations

* Setup a local host domain using .loc, (e.g. www.abetter.loc)
* Point the host to /public
* Create a local mysql database

## Setup Laravel

Edit .env settings

```bash
APP_NAME=<name>
APP_VERSION=<version>
APP_ENV=<sandbox|local|dev|stage|production>
APP_DEBUG=<true|false>
APP_URL=<url>
APP_PROXY=<url>
DB_DATABASE=<database>
DB_USERNAME=<username>
DB_PASSWORD=<password>
WP_THEME=<optional-views-subfolder>
WP_AUTOLOGIN=<autologin-user>
WP_REQUIRELOGIN=<require-login-to-view>
```

Note: Use APP_ENV=sandbox when developing with browsersync.

Add routes to /routes/web.php

```bash
// ABetter Toolkit services
Route::any('image/{style?}/{path}', '\ABetterToolkitController@handle')->where('path','.*');
Route::any('proxy/{path}', '\ABetterToolkitController@handle')->where('path','.*');
Route::any('browsersync/{event?}/{path}', '\ABetterToolkitController@handle')->where('path','.*');
Route::any('service/{path?}.{type}', '\ABetterToolkitController@handle')->where(['path'=>'.*','type'=>'json']);

// ABetter Wordpress main route
Route::any('{path}', '\ABetterWordpressController@handle')->where('path','.*');
```

Note: Remove any other routes for root or wp paths (i.e default Welcome).

Copy the Deployer file to root and run setup once:

```bash
cp vendor/abetter/toolkit/deploy/deploy.php deploy.php
dep setuponce local
```

Run audit fix if needed:

```bash
npm audit fix
```

Note: Only run the setuponce on fresh projects, since it will replace files in /resources and /public.

Test to build the app:
```bash
dep build local
```

## Setup Wordpress

Go to host in browser (e.g. http://www.abetter.loc) and follow install instructions.

Go to /Appearance/Themes, and activate ABetter theme.

Go to /Plugins, and activate:

* Advanced Custom Fields.
* Disable Gutenberg (full support is coming soon...).
* ... Any other of the supported plugins you need.

Add default pages:

```bash
<name> : <slug> : <template> : <order>
Start : start : default : -1
News : news : default : 200
Privacy Policy : privacy-policy : default : 200
Search : search : search : 400
403 Forbidden : 403-forbidden : error : 403
404 Not found : 404-not-found : error : 404
```

Go to /Settings/Reading:

* Select Start as homepage
* Select News as post page

Finaly, go to host in browser:

**Congratulations to your new site!**

---

## Usage

#### Development

Start webpack and browsersync:

```bash
npm run watch
```

#### Deployment

...

---

# Contributors

[Johan Sjöland](https://www.abetterstory.com/]) <johan@sjoland.com>  
Senior Product Developer: ABetter Story Sweden AB.

## License

MIT license. Please see the [license file](LICENSE) for more information.
