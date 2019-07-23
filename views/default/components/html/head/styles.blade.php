@debug('default:components/html/head/styles.blade.php')
@php
$site->styles_app = (($file = 'styles/app.css') && is_file(public_path().'/'.$file)) ? $file : '';
$site->theme_css = _dictionary('theme_css',NULL,'');
@endphp
@if ($site->styles_app)
<link href="{{ mix($site->styles_app) }}" rel="stylesheet" type="text/css">
@endif
@if ($site->theme_css)
<style>
{!! $site->theme_css !!}
</style>
@endif
