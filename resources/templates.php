<?php

/*
Wordpress theme templates
*/

add_filter('theme_page_templates', function($templates){
	$templates['search.php'] = 'Search';
	$templates['posts.php'] = 'Posts';
	return $templates;
});
