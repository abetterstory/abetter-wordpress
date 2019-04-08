@debug('Default template: override with /resources/views/<theme>/search.blade.php')

@include('components.html.start')

	@component('components.menu',TRUE)

	@component('components.search',TRUE)

	@component('components.footer',TRUE)

@include('components.html.end')
