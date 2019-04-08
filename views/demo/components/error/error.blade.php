<section id="error">

	@inject('ErrorComponent')

	<article>

		{!! $ErrorComponent->content !!}

	</article>

	@style('../section/section.scss')
	@style('error.scss')

</section>
