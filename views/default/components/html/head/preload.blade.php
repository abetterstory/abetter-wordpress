@debug('default:components/html/head/preload.blade.php')
@php
$site->prefetch = [
	//'//www.google-analytics.com'
];
$site->preload = [
	//['as' => 'font', 'type' => 'font/woff2', 'href' => '/fonts/fontawesome/fontawesome-webfont.woff2']
];
@endphp
@if (!empty($site->prefetch))
	@foreach($site->prefetch AS $href)
		<link rel="dns-prefetch" href="{{ $href }}" />
	@endforeach
@endif
@if (!empty($site->preload))
	@foreach($site->preload AS $preload)
		<link rel="preload" crossorigin="crossorigin" as="{{ $preload['as'] }}" type="{{ $preload['type'] }}" href="{{ $preload['href'] }}" />
	@endforeach
@endif
