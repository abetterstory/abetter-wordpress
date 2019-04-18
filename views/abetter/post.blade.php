@debug('default:post.blade.php')

@include('components.html.start')

	@component('components.menu',TRUE)
	@component('components.cover',TRUE)
	@component('components.main')

		@component('components.post',TRUE)

	@endcomponent
	@component('components.footer',TRUE)

@include('components.html.end')
