@debug('Default component: ~/views/<theme>/components/html/head/device.blade.php')
@php
@endphp
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
@if (!empty($site->manifest))<link rel="manifest" href="{{ $site->manifest }}" />@endif
@if (!empty($site->pwa))<script async src="{{ $site->pwa }}"></script>@endif
@if (!empty($site->app))
	<meta name="mobile-web-app-capable" content="yes" />
	<meta name="application-name" content="{{ $site->app }}" />
	@if (!empty($site->app_color))<meta name="theme-color" content="{{ $site->app_color }}" />@endif
@endif
@if (!empty($site->app))
	<meta name="apple-mobile-web-app-capable" content="yes" />
	<meta name="apple-mobile-web-app-title" content="{{ $site->app }}" />
	@if (!empty($site->app_statusbar))<meta name="apple-mobile-web-app-status-bar-style" content="{{ $site->app_statusbar }}" />@endif
	@if (!empty($site->app_icon))<link rel="apple-touch-icon" href="{{ $site->app_icon }}" />@endif
	@if (!empty($site->app_splash))<link rel="apple-touch-startup-image" href="{{ $site->app_splash }}" />@endif
@endif
@if (!empty($site->app))
	<meta name="msapplication-tooltip" content="{{ $site->app }}" />
	@if (!empty($site->ms_url))<meta name="msapplication-starturl" content="{{ $site->ms_url }}" />@endif
	@if (!empty($site->ms_color))<meta name="msapplication-navbutton-color" content="{{ $site->ms_color }}" />@endif
@endif
