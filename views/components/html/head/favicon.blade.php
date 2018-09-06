@php _debug('favicon');
$site->favicon = '';
$site->favicon_ico = '';
$site->favicon_192 = '';
@endphp

@if (!empty($site->favicon))<link rel="icon" href="{{ $site->favicon }}" />@endif
@if (!empty($site->favicon_ico))<link rel="icon" sizes="16x16 32x32 48x48 64x64" href="{{ $site->favicon_ico }}" />@endif
@if (!empty($site->favicon_192))<link rel="icon" sizes="192x192" href="{{ $site->favicon_192 }}" />@endif
