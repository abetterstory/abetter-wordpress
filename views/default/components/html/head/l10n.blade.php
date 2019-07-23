@debug('default:components/html/head/l10n.blade.php')
@php
$post->canonical_domain = ($canonical = env('APP_CANONICAL')) ? $canonical : url('/');
$post->canonical_url = $post->canonical_domain.$item->url;
$post->localizations = [];
@endphp
@if(!empty($post->canonical_url))<link rel="canonical" href="{{ $post->canonical_url }}" />@endif
@if(!empty($post->localizations))
	@foreach($post->localizations AS $locale => $href)
	<link rel="alternate" href="{{ $href }}" hreflang="{{ $locale }}" />
	@endforeach
@endif
