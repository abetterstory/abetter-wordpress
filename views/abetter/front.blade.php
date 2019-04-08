@debug('Default template: override with /resources/views/<theme>/front.blade.php')

@include('components.html.start')

	@component('components.menu',TRUE)

	@component('components.canvas',TRUE)

	@component('components.footer',TRUE)

@include('components.html.end')
