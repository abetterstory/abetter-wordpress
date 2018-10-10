@php _debug('tracking');
$site->ga = ($ga = env('APP_GA')) ? $ga : 'UA-127222126-1'; // ABetter Dev
@endphp

@if (!empty($site->ga))
<script async src="https://www.googletagmanager.com/gtag/js?id={{$site->ga}}"></script>
<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','{{$site->ga}}');</script>
@endif
