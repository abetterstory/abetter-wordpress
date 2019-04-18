@debug('default:components/html/head/favicon.blade.php')
@php
$site->favicon = (($file = '/images/icons/favicon.png') && is_file(resource_path().$file)) ? $file : '';
@endphp
@if(!empty($site->favicon))<link rel="icon" href="{{ $site->favicon }}" />@endif
