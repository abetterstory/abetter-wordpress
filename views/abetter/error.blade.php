@debug('Default template: override with /resources/views/<theme>/error.blade.php')

@include('components.html.start')

	@component('components.menu',TRUE)

	@component('components.error',TRUE)

	@component('components.footer',TRUE)

@include('components.html.end')
