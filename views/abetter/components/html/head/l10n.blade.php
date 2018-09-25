@php _debug('l10n');
$post->canonical = url('/').$item->url;
$post->localizations = [];
@endphp

@if(!empty($post->canonical))<link rel="canonical" href="{{ $post->canonical }}" />@endif

@if(!empty($post->localizations))
	@foreach($post->localizations AS $locale => $href)
	<link rel="alternate" href="{{ $href }}" hreflang="{{ $locale }}" />
	@endforeach
@endif
