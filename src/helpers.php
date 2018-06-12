<?php

if (!function_exists('__')) {
    function __($key, array $par = []) {
		clock([$key,$par]);
        return trans($key, $par);
    }
}
