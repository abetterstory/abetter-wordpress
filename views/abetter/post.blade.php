@debug('Default template: override with /resources/views/<theme>/post.blade.php')

@include('components.html.start')

	@component('components.menu',TRUE)

	@component('components.post',TRUE)

	@component('components.footer',TRUE)

@include('components.html.end')
