@debug('default:components/search/search.blade.php')

@inject('Search')

<section class="component--search">

	@style('search.scss')
	@style('styles/grid.scss')

	@component('components.search.searchform',TRUE)

	<content class="block--grid">

		<div class="row">

			@foreach($Search->posts AS $item)
			<div class="column {{ $item->type ?? '' }} c2">
				@component('components.search.searchitem',['item' => $item],TRUE)
			</div>
			@endforeach

		</div>

	</content>

</section>
