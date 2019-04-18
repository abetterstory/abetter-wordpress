@debug('default:components/html/head/social.blade.php')
@php

$post->ogsite = get_option('blogname');
$post->ogtype = ($f = get_field('seo_type',$post)) ? $f : 'website';
$post->oglocale = get_locale();
$post->ogdomain = ($canonical = env('APP_CANONICAL')) ? $canonical : url('/');

$post->ogurl = $post->ogdomain . (($f = get_field('seo_url',$post)) ? _relative($f) : $item->url);
$post->ogurl = rtrim($post->ogurl,'/'); // Laravel removes trailing slash

if (!$post->ogimage = ($f = get_field('seo_image',$post)) ? $f : '') {
	$post->ogimage = ($f = $item->image) ? $f : _dictionary('seo_image_default',NULL,'');
}
if ($post->ogimage) $post->ogimage = $post->ogdomain . _image($post->ogimage,'w1024');

if (!$post->ogdescription = get_field('seo_description',$post)) {
	$post->ogdescription = ($f = $item->excerpt) ? $f : _dictionary('seo_description_default',NULL,'');
	$post->ogdescription = _excerpt($post->ogdescription,300);
}

if (!$post->ogtitle = get_field('seo_title',$post)) {
	$post->ogtitle = ($f = $item->headline) ? $f : $post->post_title;
	$post->ogtitle = str_replace('{TITLE}',$post->ogtitle,_dictionary('seo_title_default',NULL,$post->ogtitle));
	$post->ogtitle = _excerpt($post->ogtitle,60);
}

@endphp
@if(!empty($post->ogsite))<meta property="og:site_name" content="{{ $post->ogsite }}" />@endif
@if(!empty($post->ogtype))<meta property="og:type" content="{{ $post->ogtype }}" />@endif
@if(!empty($post->ogtitle))<meta property="og:title" content="{{ $post->ogtitle }}" />@endif
@if(!empty($post->ogdescription))<meta property="og:description" content="{{ $post->ogdescription }}" />@endif
@if(!empty($post->ogimage))<meta property="og:image" content="{{ $post->ogimage }}" />@endif
@if(!empty($post->ogurl))<meta property="og:url" content="{{ $post->ogurl }}" />@endif
@if(!empty($post->oglocale))<meta property="og:locale" content="{{ $post->oglocale }}" />@endif
