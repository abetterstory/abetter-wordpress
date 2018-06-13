<?php
/*
Wordpress theme templates
*/

/*
add_filter('theme_page_templates', function($templates){
	$templates['example.php'] = 'Example';
	return $templates;
});
*/

// ---

if (is_file(ROOTPATH.'/resources/wordpress/templates.php')) {
	require_once(ROOTPATH.'/resources/wordpress/templates.php');
}
