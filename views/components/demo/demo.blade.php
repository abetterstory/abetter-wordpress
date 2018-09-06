<section class="component--demo">

	@inject('Demo')

	<div class="row uk-section">
		<div class="column uk-container">

			<h1>{{ $Demo->title }}</h1>

			<p class="lead">{{ $Demo->lead }}</p>

			<img src="{{ $Demo->image }}" />

			{!! $Demo->body !!}

		</div>
	</div>

	@style('demo.scss')
	@script('demo.js')

</section>
