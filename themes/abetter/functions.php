<?php
/*
Wordpress theme functions and definitions
*/

// Add developer role
add_action('after_setup_theme',function(){
	$admin_role = get_role('administrator');
	if ($developer_role = get_role('developer')) {
		foreach ($admin_role->capabilities AS $cap => $perm) {
			$developer_role->add_cap($cap);
		}
	} else {
		add_role('developer', 'Developer', $admin_role->capabilities);
	}
});

// Limit admin menu on role
add_action('admin_init', function(){
	if (current_user_can('developer')) return;
	remove_submenu_page('plugins.php', 'plugin-editor.php');
	remove_submenu_page('plugins.php', 'plugin-install.php');
    remove_submenu_page('themes.php', 'theme-editor.php');
	remove_menu_page('edit-comments.php');
	if (current_user_can('administrator')) return;
	remove_menu_page('edit.php?post_type=acf-field-group');
	remove_menu_page('admin.php?page=abetter-deployment');
	remove_menu_page('admin.php?page=acf-options');
	remove_menu_page('options-general.php');
	remove_menu_page('plugins.php');
	remove_menu_page('tools.php');
	remove_menu_page('themes.php');
});

// ---

// Disable image sizes
add_filter('intermediate_image_sizes', function($sizes) { return array(); });
add_filter('intermediate_image_sizes_advanced', function($sizes) { return array(); });

// Generate only default preview image size on non-image files
add_filter('fallback_intermediate_image_sizes', function($sizes) { return array('default'); });

// Filter uploads to lowercase filenames
add_filter('wp_handle_upload_prefilter', function($file){
    $file['name'] = strtolower($file['name']);
    return $file;
});

// ---

// Remove content filters
remove_filter('the_content', 'wpautop');
remove_filter('the_excerpt', 'wpautop');
add_filter('tiny_mce_before_init', function($init) {
	$init['wpautop'] = FALSE;
	return $init;
});

// Filter local urls to be relative in post_content
add_filter('content_save_pre', function($content){
	$content = preg_replace_callback('/\"(https?\:\/\/)([^\/]+)\/([^\"]+)\"/', function($match) {
		return ($match[2] == $_SERVER['HTTP_HOST']) ? '"/'.$match[3].'"' : $match[0];
	}, $content);
	return $content;
});

// TinyMCE Editor JS
add_filter('mce_external_plugins', function($mce_plugins) {
	$mce_plugins[] = get_template_directory_uri().'/editor.js';
	return $mce_plugins;
}, 999);

// TinyMCE Editor CSS
add_filter('mce_css', function($mce_css){
	if (!empty($mce_css)) $mce_css .= ',';
    $mce_css .= get_template_directory_uri().'/editor.css';
    return $mce_css;
});

// TinyMCE Editor styles
add_filter('mce_buttons_2',function($buttons){
	array_unshift($buttons,'styleselect');
	return $buttons;
});
add_filter('tiny_mce_before_init',function($init){
	$styles = array(
		array(
			'title' => 'Lead',
			'block' => 'p',
			'classes' => 'lead',
			'wrapper' => FALSE
		),
		array(
			'title' => 'Nowrap',
			'inline' => 'span',
			'classes' => 'nowrap',
			'wrapper' => TRUE
		),
	);
	$init['style_formats'] = json_encode($styles);
	$init['extended_valid_elements'] = '*[*]';
	return $init;
});

// ---

// Options page
if (function_exists('acf_add_options_page')) {
	acf_add_options_page();
}

// Custom content
add_action('init', function(){
	// Page
	register_taxonomy_for_object_type('post_tag', 'page');
	register_taxonomy_for_object_type('category', 'page');
	add_post_type_support('page', 'excerpt');
	// Dictionary
	register_post_type('dictionary',array(
        'labels' => array(
            'name' => __('Dictionary'),
            'singular_name' => __('Dictionary')
        ),
		'menu_icon' => 'dashicons-tag',
		'rewrite' => FALSE,
        'public' => FALSE,
		'show_ui' => TRUE
    ));
	// Menus
	register_nav_menus(array(
		'main' => 'Main',
		'extra' => 'Extra',
		'social' => 'Social',
		'quick' => 'Quick',
		'language' => 'Language',
		'footer' => 'Footer'
	));
});

// Remove TinyMCE for Dictionary
add_filter('user_can_richedit', function($default) {
    if (get_post_type() == 'dictionary') return FALSE;
    return $default;
});

// Force fields on all pages (posts)
add_action('edit_form_after_title', function(){
	add_post_type_support('page', 'editor');
});

// ---

add_filter('post_type_link', function($url,$post=0){
	$url = preg_replace('/\/wp\//',"/",$url);
	return $url;
});

add_filter('post_link', function($url){
	$url = preg_replace('/\/wp\//',"/",$url);
	return $url;
});

add_filter('page_link', function($url){
	$url = preg_replace('/\/wp\//',"/",$url);
	return $url;
});

add_filter('preview_post_link', function($url){
	$url = preg_replace('/\?(page_id|p)=([0-9]+)\&/',"$1/$2?",$url);
	return $url;
});

add_filter('get_attached_file', function($url){
	//$url = preg_replace('/^.*(\/uploads\/.+)$/', "$1", $url);
	return $url;
});

// ---

if (is_file(ROOTPATH.'/resources/wordpress/functions.php')) {
	require_once(ROOTPATH.'/resources/wordpress/functions.php');
}

if (is_file(ROOTPATH.'/resources/wordpress/helpers.php')) {
	require_once(ROOTPATH.'/resources/wordpress/helpers.php');
}

// ---

require_once('templates.php');
