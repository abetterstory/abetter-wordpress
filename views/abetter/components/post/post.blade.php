<article id="post">

	@inject('Post')

	<div class="row uk-section">
		<div class="column uk-container">

			{!! $Post->content !!}

		</div>
	</div>

	@style('post.scss')

</section>
