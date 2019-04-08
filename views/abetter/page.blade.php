@debug('Default template: override with /resources/views/<theme>/page.blade.php')

@include('components.html.start')

	@component('components.menu',TRUE)

	@component('components.page',TRUE)

	@component('components.footer',TRUE)

@include('components.html.end')
