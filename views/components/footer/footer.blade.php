<footer id="footer">

	@inject('Footer')

	<section class="footer-container">

		<ul class="brand boilerplate">
			<li>
				<figure>
					@if ($Footer->brand_logo_svg)
						{!! $Footer->brand_logo_svg !!}
					@elseif ($Footer->brand_logo)
						<img alt="{{ $Footer->brand_label }}" src="{{ $Footer->brand_logo }}" />
					@endif
				</figure>
				<p>{{ $Footer->brand_boilerplate }}</p>
			</li>
		</ul>

		<ul class="social">
			@foreach ($Footer->social_items AS $item)
				<li><a href="{{ $item->url }}" target="{{ $item->target }}"><i class="{{ $item->style }}"></i></a></li>
			@endforeach
		</ul>

		<ul class="legal">
			<small>{{ $Footer->brand_copyright }}</small>
		</ul>

	</section>

	@style('footer.scss')
	@script('footer.js')

</footer>
