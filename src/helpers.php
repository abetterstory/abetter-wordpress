<?php

if (!function_exists('__')) {
    function __($key, array $par = []) {
		clock([$key,$par]);
        return trans($key, $par);
    }
}

/* resources/wordpress/core/wp-includes/l10n.php

function __( $text, $domain = 'default' ) {
    return translate( $text, $domain );
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

    function ___________($key, $replace = [], $locale = null)
    {
        return app('translator')->getFromJson($key, $replace, $locale);
    }
}

*/
