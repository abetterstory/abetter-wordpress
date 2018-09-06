<article id="page">

	@inject('Page')

	<div class="row uk-section">
		<div class="column uk-container">

			{!! $Page->content !!}

		</div>
	</div>

	@style('page.scss')

</section>
