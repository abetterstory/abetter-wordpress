@php _debug('styles');
$site->styles_app = 'styles/app.css';
$site->theme_css = _dictionary('theme_css',NULL,'');
@endphp

@if ($site->styles_app)
<link href="{{ url('/').mix($site->styles_app) }}" rel="stylesheet" type="text/css">
@endif

@if ($site->theme_css)
<style>
{!! $site->theme_css !!}
</style>
@endif
