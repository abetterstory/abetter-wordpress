<?php

/*
Wordpress theme functions and definitions
*/

// ACF/Meta Search
add_filter('search_meta_keys', function($keys) {
	//$keys = ['<acf_field_key>'];
	$keys = ['cover_headline','cover_lead','cover_caption'];
	return $keys;
});
