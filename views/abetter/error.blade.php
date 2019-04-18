@debug('default:error.blade.php')

@include('components.html.start')

	@component('components.menu',TRUE)
	@component('components.cover',TRUE)
	@component('components.main')

		@component('components.page',TRUE)
		@component('components.error',TRUE)

	@endcomponent
	@component('components.footer',TRUE)

@include('components.html.end')
