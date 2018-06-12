<?php

/* Really crappy hack of __ translation helper */

if (!function_exists('__')) {

    function __($key, $par = NULL, $locale = NULL) {

		if (isset($par) && is_array($par)) { // Probably not Wordpress

			 // Foundation
			return trans($key, $par);

		} else if (function_exists('translate')){

			// Wordpress
			$par = (string) (!empty($par)) ? 'default' : $par;
			return translate($key,$par);

		} else {

			// Voyager
			$par = (array) (!empty($par)) ? [] : $par;
			return trans($key, $par);

		}
    }

}

/* resources/wordpress/core/wp-includes/l10n.php
if (!function_exists('__')){function __($text, $domain = 'default' ) {
    return translate($text,$domain);}
}
*/

/* vendor/tcg/voyager/src/Helpers/helpersi18n.php
if (!function_exists('___')) {
    function ___($key, array $par = [])
    {
        return trans($key, $par);
    }
}
*/

/* vendor/laravel/framework/src/Illuminate/Foundation/helpers.php
if (! function_exists('___')) {
    function ___($key, $replace = [], $locale = null)
    {
        return app('translator')->getFromJson($key, $replace, $locale);
    }
}
*/
