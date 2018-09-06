@php _debug('scripts');
$site->scripts_manifest = 'scripts/manifest.js';
$site->scripts_vendor = 'scripts/vendor.js';
//$site->scripts_app = 'scripts/app.js'; // Moved into body
@endphp

@if (!empty($site->scripts_manifest))<script src="{{ url('/').mix($site->scripts_manifest) }}" type="text/javascript"></script>@endif
@if (!empty($site->scripts_vendor))<script src="{{ url('/').mix($site->scripts_vendor) }}" type="text/javascript"></script>@endif
@if (!empty($site->scripts_app))<script src="{{ url('/').mix($site->scripts_app) }}" type="text/javascript"></script>@endif
