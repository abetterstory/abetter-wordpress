@debug('default:components/html/head/seo.blade.php')
@php

if (!$post->description = get_field('seo_description',$post)) {
	$post->description = ($f = $item->excerpt) ? $f : _dictionary('seo_description_default',NULL,'');
	$post->description = _excerpt($post->description,300);
}

$post->keywords = ($f = get_field('seo_keywords',$post)) ? $f : _dictionary('seo_keywords_default',NULL,'');
$post->author = ($f = get_field('seo_author',$post)) ? $f : _dictionary('seo_author_default',NULL,'');

$post->robots = ($f = get_field('seo_robots',$post)) ? $f : _dictionary('seo_robots_default',NULL,'index,follow');;

if (env('APP_ENV') != 'production') $post->robots = 'noindex,nofollow';

@endphp
@if(!empty($post->description))<meta name="description" content="{{ $post->description }}" />@endif
@if(!empty($post->keywords))<meta name="keywords" content="{{ $post->keywords }}" />@endif
@if(!empty($post->author))<meta name="author" content="{{ $post->author }}" />@endif
@if(!empty($post->robots))<meta name="robots" content="{{ $post->robots }}" />@endif
