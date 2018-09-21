@php _debug('component');
$site->component = TRUE;
@endphp

@if (!empty($site->component))
<script>window.$Ready=function(fn){if(document.readyState!='loading')return fn.call();document.addEventListener("DOMContentLoaded",function(){fn.call()})}</script>
@endif
