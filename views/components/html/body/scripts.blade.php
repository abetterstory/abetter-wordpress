@php _debug('scripts');
$site->scripts_app = 'scripts/app.js';
$site->theme_js = _dictionary('theme_js',NULL,'');
@endphp

@if ($site->scripts_app)
<script src="{{ url('/').mix($site->scripts_app) }}" type="text/javascript"></script>
@endif

@if ($site->theme_js)
<script>
{!! $site->theme_js !!}
</script>
@endif
