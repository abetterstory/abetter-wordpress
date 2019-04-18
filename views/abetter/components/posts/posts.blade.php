@debug('default:components/posts/posts.blade.php')

@inject('Posts')

<section class="component--posts">

	@style('posts.scss')
	@style('styles/typography.scss')
	@style('styles/grid.scss')

	@if(!empty($Posts->intro))
	<section class="block--intro block--typography responsive animated">
		{!! $Posts->intro !!}
	</section>
	@endif

	<content class="block--grid">

		<div class="row">

			@foreach($Posts->posts AS $item)
			<div class="column {{ $item->type ?? '' }}">
				@component('components.card',['item' => $item],TRUE)
			</div>
			@endforeach

		</div>

	</content>

</section>
