@debug('default:search.blade.php')

@include('components.html.start')

	@component('components.menu',TRUE)
	@component('components.cover',TRUE)
	@component('components.main')

		@component('components.page',TRUE)
		@component('components.search',TRUE)

	@endcomponent
	@component('components.footer',TRUE)

@include('components.html.end')
