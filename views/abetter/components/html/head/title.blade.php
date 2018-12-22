@php _debug('title');

if (!$post->title = get_field('seo_title',$post)) {
	$post->title = $item->headline ?? $post->post_title;
	$post->title = str_replace('{TITLE}',$post->title,_dictionary('seo_title_default',NULL,$post->title));
	$post->title = _excerpt($post->title,60);
}

@endphp

@if(!empty($post->title))<title>{{ $post->title }}</title>@endif
