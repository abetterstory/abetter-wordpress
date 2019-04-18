@debug('default:components/footer/footer.blade.php')

@inject('Footer')

<footer class="component--footer cloak">

	@style('footer.scss')

	<section class="footer--container">

		<ul class="brand boilerplate">
			<li>
				<figure>
					<a href="{{ $Footer->front_url }}">
						<figure>{!! $Footer->brand_svg !!}</figure>
					</a>
				</figure>
				<p>{{ $Footer->brand_boilerplate }}</p>
			</li>
		</ul>

		<ul class="legal">
			<li>
				<p>{{ $Footer->brand_copyright }}</p>
			</li>
		</ul>

	</section>

	@script('footer.js')

</footer>
