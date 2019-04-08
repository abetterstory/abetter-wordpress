@debug('Default template: override with /resources/views/<theme>/posts.blade.php')

@include('components.html.start')

	@component('components.menu',TRUE)

	@component('components.posts',TRUE)

	@component('components.footer',TRUE)

@include('components.html.end')
