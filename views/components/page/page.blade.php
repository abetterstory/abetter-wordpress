<section id="page">

	@inject('Page')

	<article>

		{!! $Page->content !!}

	</article>

	@style('../section/section.scss')
	@style('page.scss')

</section>
