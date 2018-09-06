@php _debug('styles');
$site->styles_app = 'styles/app.css';
@endphp

@if (!empty($site->styles_app))
	<link href="{{ url('/').mix($site->styles_app) }}" rel="stylesheet" type="text/css">
@endif
