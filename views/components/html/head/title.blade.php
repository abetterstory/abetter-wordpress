@php _debug('title');
$post->title = $post->post_title;
$post->title = preg_replace('/(<br>|<br\/>|<br \/>)/'," ",$post->title);
$post->title = preg_replace('/( +)/'," ",$post->title);
@endphp

@if (!empty($post->title))
	<title>{{ $post->title }}</title>
@endif
