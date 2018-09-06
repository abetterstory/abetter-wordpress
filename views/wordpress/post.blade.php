<!doctype html>
<html @include('components.html.attr')>
    <head>@include('components.html.head')</head>
    <body @include('components.html.body.attr')>

		@component('components.menu',TRUE)

		@component('components.header',TRUE)
		@component('components.post',TRUE)

		@component('components.footer',TRUE)

		@include('components.html.body.scripts')

		@lab()

    </body>
</html>
