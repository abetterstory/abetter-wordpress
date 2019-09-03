<?php
/*
Wordpress theme functions and definitions
*/

// Login form
add_action('login_enqueue_scripts', function(){ ?>
    <style type="text/css">
        #login h1, #login .privacy-policy-page-link, #backtoblog {
			display: none;
		}
    </style>
<?php });

// Admin Menu Bar
add_action('admin_bar_menu', function($wp_admin_bar){
	$wp_admin_bar->add_node(array(
		'id' => 'abetter-clear-cache',
		'title' => '<span class="ab-icon"></span><span class="ab-label">Clear Cache@CDN</span>',
		'href' => '/service/invalidate',
		'meta' => array(
			'target' => '_blank',
			'onclick' => 'event.preventDefault(); if (window.abcc) return; window.abcc = 1; var $li = jQuery(event.target).closest("li"); $li.removeClass("done").addClass("invalidating"); jQuery.get("/service/aws/invalidate.json").done(function(res,txt,req){ window.abcc = 0; $li.removeClass("invalidating").addClass("done"); console.log("/service/aws/invalidate.json",{res:res,txt:txt,req:req}); });',
			'html' => '<style>#wpadminbar #wp-admin-bar-abetter-clear-cache .ab-icon{position:relative;top:1px}#wpadminbar #wp-admin-bar-abetter-clear-cache .ab-label{margin-left:-2px}#wpadminbar #wp-admin-bar-abetter-clear-cache .ab-icon:before{position:relative;content:"\f182";top:0}#wpadminbar #wp-admin-bar-abetter-clear-cache.invalidating .ab-label{color:#fff!important;opacity:.4}#wpadminbar #wp-admin-bar-abetter-clear-cache.invalidating .ab-icon:before{color:#fff!important;content:"\f531"}#wpadminbar #wp-admin-bar-abetter-clear-cache.done .ab-icon:before{color:#fff!important;content:"\f147"}#wpadminbar #wp-admin-bar-abetter-clear-cache.invalidating .ab-icon{animation:rotating 2s linear infinite}@-webkit-keyframes rotating{from{transform:rotate(360deg)}to{transform:rotate(0)}}</style>'
		)
	));
},9999);

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
	remove_menu_page('edit.php?post_type=dev_actor');
	remove_menu_page('edit.php?post_type=dev_requirement');
	remove_menu_page('edit.php?post_type=dev_brief');
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

// Filter @components and images in post_content
add_filter('content_save_pre', function($content) {
	if (preg_match('/@/',$content)) {
		$content = preg_replace('/@(component|slot|block)end/', "@end$1", $content);
		$content = preg_replace('/<p>@(component|slot|classname|dateline|byline|block)([^<]*)<\/p>/', "\n@$1$2\n", $content);
		$content = preg_replace('/<p>@(endcomponent|endslot|endblock)<\/p>/', "\n@$1\n", $content);
		$content = preg_replace('/<p>@(component|slot|block)([^<]*)(<img|a)/', "\n@$1$2\n<p>$3", $content);
		$content = preg_replace('/<p>@(endcomponent|endslot|endblock)\s*@(component|block)([^<]*)<\/p>/', "\n@$1\n@$2\n", $content);
		//$content = preg_replace('/<p[^>]*>@(component|slot|classname|dateline|byline|block)([^<]*)<\/p>/', "\n@$1$2\n", $content);
		//$content = preg_replace('/<p[^>]*>@(endcomponent|endslot|endblock)<\/p>/', "\n@$1\n", $content);
		//$content = preg_replace('/<p[^>]*>@(component|slot|block)([^<]*)(<img|a)/', "\n@$1$2\n<p>$3", $content);
		//$content = preg_replace('/<p[^>]*>@(endcomponent|endslot|endblock)\s*@(component|block)([^<]*)<\/p>/', "\n@$1\n@$2\n", $content);
	}
	$content = preg_replace('/<p>\s*(<img[^<]+)\s*<\/p>/', "\n$1\n", $content);
	//$content = preg_replace('/<p[^>]*>\s*(<img[^<]+)\s*<\/p>/', "\n$1\n", $content);
	$content = preg_replace('/></', ">\n<", $content);
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
			'selector' => 'p',
			'classes' => 'lead',
			'wrapper' => FALSE
		),
		array(
			'title' => 'Dateline',
			'selector' => 'p',
			'classes' => 'dateline',
			'wrapper' => FALSE
		),
		array(
			'title' => 'Small',
			'selector' => 'p',
			'classes' => 'small',
			'wrapper' => FALSE
		),
		array(
			'title' => 'Nowrap',
			'inline' => 'span',
			'classes' => 'nowrap',
			'wrapper' => TRUE
		),
		array(
			'title' => 'Prewrap',
			'inline' => 'span',
			'classes' => 'prewrap',
			'wrapper' => TRUE
		),
		array(
			'title' => 'Baseline',
			'inline' => 'span',
			'classes' => 'nowrap',
			'wrapper' => TRUE
		),
		array(
			'title' => 'Center',
			'block' => 'center',
			'classes' => 'align-center',
			'wrapper' => TRUE
		),
		array(
			'title' => 'Width 100%',
			'selector' => 'span,h1,h2,h3,h4,h5,h6,blockquote,pre,img',
			'classes' => 'w100',
		),
		array(
			'title' => 'Width 75%',
			'selector' => 'span,h1,h2,h3,h4,h5,h6,blockquote,pre,img',
			'classes' => 'w75',
		),
		array(
			'title' => 'Width 50%',
			'selector' => 'span,h1,h2,h3,h4,h5,h6,blockquote,pre,img',
			'classes' => 'w50',
		),
		array(
			'title' => 'Width 25%',
			'selector' => 'span,h1,h2,h3,h4,h5,h6,blockquote,pre,img',
			'classes' => 'w25',
		),
		array(
			'title' => 'Float Left',
			'selector' => 'span,h1,h2,h3,h4,h5,h6,blockquote,pre,img',
			'classes' => 'float-left',
		),
		array(
			'title' => 'Float Right',
			'selector' => 'span,h1,h2,h3,h4,h5,h6,blockquote,pre,img',
			'classes' => 'float-right',
		),
		array(
			'title' => 'Float Break',
			'selector' => 'span,h1,h2,h3,h4,h5,h6,blockquote,pre,img',
			'classes' => 'float-break',
		),
		array(
			'title' => 'Column 75%',
			'selector' => 'p',
			'classes' => 'w75',
		),
		array(
			'title' => 'Column 50%',
			'selector' => 'p',
			'classes' => 'w50',
		),
		array(
			'title' => 'Column 25%',
			'selector' => 'p',
			'classes' => 'w25',
		),
		array(
			'title' => 'Column Left',
			'selector' => 'p',
			'classes' => 'float-left',
		),
		array(
			'title' => 'Column Right',
			'selector' => 'p',
			'classes' => 'float-right',
		),
		array(
			'title' => 'Column Break',
			'selector' => 'p',
			'classes' => 'float-break',
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

// Custom taxonomy
add_action('init', function(){
	// Component
	register_taxonomy('components',['post','page'],[
		'hierarchical' => FALSE,
		'show_ui' => TRUE,
		'show_admin_column' => TRUE,
		'query_var' => TRUE,
		'rewrite' => ['slug' => 'component'],
		'labels' => [
			'name' => _x('Components', 'taxonomy general name'),
			'singular_name' => _x('Component', 'taxonomy singular name'),
			'search_items' => __('Search Components'),
			'all_items' => __('All Components'),
			'parent_item' => __('Parent Component'),
			'parent_item_colon' => __('Parent Component:'),
			'edit_item' => __('Edit Component'),
			'update_item' => __('Update Component'),
			'add_new_item' => __('Add New Component'),
			'new_item_name' => __('New Component Name'),
			'menu_name' => __('Components'),
		],
	]);
});

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
	// Dev
	register_post_type('dev_actor',array(
        'labels' => array(
            'name' => __('D:Actors'),
            'singular_name' => __('D:Actor')
        ),
		'menu_icon' => 'dashicons-admin-tools',
		'rewrite' => FALSE,
        'public' => FALSE,
		'show_ui' => TRUE
    ));
	register_post_type('dev_requirement',array(
        'labels' => array(
            'name' => __('D:Requirements'),
            'singular_name' => __('D:Requirement')
        ),
		'menu_icon' => 'dashicons-admin-tools',
		'rewrite' => FALSE,
        'public' => FALSE,
		'show_ui' => TRUE
    ));
	register_post_type('dev_brief',array(
        'labels' => array(
            'name' => __('D:Briefs'),
            'singular_name' => __('D:Brief')
        ),
		'menu_icon' => 'dashicons-admin-tools',
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

// Force english admin panels
add_action('locale', function($locale){
	if (is_admin()) return 'en_US';
	return $locale;
});

// Remove update notice on prod/stage
if (_is_live()) {
add_filter('pre_site_transient_update_core','__return_null');
add_filter('pre_site_transient_update_plugins','__return_null');
add_filter('pre_site_transient_update_themes','__return_null');
}

// ---

add_filter('manage_pages_columns', function($columns){
	$sorted = []; foreach($columns as $key => $label) {
		if ($key == 'author') $sorted['order'] = 'Order';
    	if ($key == 'author') $sorted['template'] = 'Template';
		$sorted[$key] = $label;
 	}
	return $sorted;
});

add_action('manage_pages_custom_column', function($column, $post_id){
	global $post;
	if ($column == 'template') echo ($template = get_page_template_slug($post_id)) ? ucfirst(strtok($template,'.')) : '—';
	if ($column == 'order') echo $post->menu_order;
},10,2);

add_filter('manage_dictionary_posts_columns', function($columns){
	$sorted = []; foreach($columns as $key => $label) {
		if ($key == 'date') $sorted['slug'] = 'Slug';
		$sorted[$key] = $label;
 	}
	return $sorted;
});

add_action('manage_dictionary_posts_custom_column', function($column, $post_id){
	if ($column == 'slug') echo get_post($post_id)->post_name;
},10,2);

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
	$url = preg_replace('/\?post_type=[^\&]+\&/','?',$url);
	$url = preg_replace('/\?(page_id|p)=([0-9]+)\&/',"$1/$2/?",$url);
	return $url;
});

add_filter('get_attached_file', function($url){
	//$url = preg_replace('/^.*(\/uploads\/.+)$/', "$1", $url);
	return $url;
});

// ---

add_action('save_post', function($post_id){
	if (in_array(get_post_type($post_id),['acf-field','acf-field-group','nav_menu_item','attachment','revision','dictionary','slide'])) return;
	if ($route = _wp_url($post_id)) $GLOBALS['wpdb']->get_results("UPDATE wp_posts SET guid = 'route:{$route}' WHERE ID = '{$post_id}'");
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
