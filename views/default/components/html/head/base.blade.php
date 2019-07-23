@debug('default:components/html/head/base.blade.php')
@php
$site->base = ($base = env('APP_BASE')) ? $base : '';
@endphp
@if(!empty($site->base))<base href="{{ $site->base }}" />@endif
