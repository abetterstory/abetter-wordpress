<section id="posts" class="component--list">

	@inject('Posts')

	<article>

		<div class="list-intro">
			{!! $Posts->content !!}
		</div>

		<ul class="list-items">
			@foreach ($Posts->items as $item)
				@component('components.posts.item',['item' => $item],TRUE)
			@endforeach
		</ul>

	</article>

	@style('posts.scss')

	@style('../section/section.scss')
	@style('../list/list.scss')

	@script('posts.js')

</section>
