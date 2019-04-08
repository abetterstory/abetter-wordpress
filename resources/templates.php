<?php

/*
Wordpress theme templates
*/

add_filter('theme_page_templates', function($templates){
	$templates['error.php'] = 'Error';
	$templates['front.php'] = 'Front';
	$templates['posts.php'] = 'Posts';
	$templates['search.php'] = 'Search';
	return $templates;
});
