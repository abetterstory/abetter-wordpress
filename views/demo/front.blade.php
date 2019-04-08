@include('components.html.start')

	@component('components.menu',TRUE)

	@component('components.header',TRUE)
	@component('components.page',TRUE)
	@component('components.posts',TRUE)

	@component('components.footer',TRUE)

@include('components.html.end')
