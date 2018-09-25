@php _debug('favicon');
$site->favicon = (($file = '/images/icons/favicon.png') && is_file(resource_path().$file)) ? $file : '';
@endphp

@if(!empty($site->favicon))<link rel="icon" href="{{ $site->favicon }}" />@endif
