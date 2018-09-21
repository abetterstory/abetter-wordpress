@php _debug('l10n');
$site->canonical = '';
$site->localizations = [];
@endphp

@if (!empty($site->canonical))<link rel="canonical" href="{{ $site->canonical }}" />@endif

@if (!empty($site->localizations))
	@foreach($site->localizations AS $locale => $href)
		<link rel="alternate" href="{{ $href }}" hreflang="{{ $locale }}" />
	@endforeach
@endif
