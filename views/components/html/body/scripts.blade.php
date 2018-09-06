@php _debug('scripts');
$site->scripts_app = 'scripts/app.js';
@endphp
@if (!empty($site->scripts_app))<script src="{{ url('/').mix($site->scripts_app) }}" type="text/javascript"></script>@endif
