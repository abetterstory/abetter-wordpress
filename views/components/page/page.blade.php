<section id="page">

	@inject('Page')

	<article>

		{!! $Page->content !!}

	</article>

	@style('/components/section/section.scss')
	@style('page.scss')

</section>
