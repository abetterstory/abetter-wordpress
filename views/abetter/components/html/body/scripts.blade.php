@debug('default:components/html/body/scripts.blade.php')

@php
$site->scripts_manifest = (($file = 'scripts/manifest.js') && is_file(public_path().'/'.$file)) ? $file : '';
$site->scripts_vendor = (($file = 'scripts/vendor.js') && is_file(public_path().'/'.$file)) ? $file : '';
$site->scripts_app = (($file = 'scripts/app.js') && is_file(public_path().'/'.$file)) ? $file : '';
$site->theme_js = _dictionary('theme_js',NULL,'');
@endphp

@if (!empty($site->scripts_manifest))<script src="{{ mix($site->scripts_manifest) }}" type="text/javascript"></script>@endif
@if (!empty($site->scripts_vendor))<script src="{{ mix($site->scripts_vendor) }}" type="text/javascript"></script>@endif
@if (!empty($site->scripts_app))<script src="{{ mix($site->scripts_app) }}" type="text/javascript"></script>@endif
@if (!empty($site->theme_js))<script>{!! $site->theme_js !!}</script>@endif
