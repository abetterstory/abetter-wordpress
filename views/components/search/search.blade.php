<section id="search" class="component--list">

	@inject('Search')

	<article>

		<div class="list-intro">
			{!! $Search->content !!}
		</div>

		<ul class="list-items">
			@foreach ($Search->items as $item)
				@component('components.search.item',['item' => $item],TRUE)
			@endforeach
		</ul>

	</article>

	@style('search.scss')

	@style('/components/section/section.scss')
	@style('/components/list/list.scss')

	@script('search.js')

</section>
