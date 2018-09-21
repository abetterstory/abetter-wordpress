<header id="menu">

	@inject('Menu')

	<section>

		<section class="left">

			<ul class="brand">
				<li>
					<a href="{{ $Menu->brand_url }}">
						<figure>
							@if ($Menu->brand_logo_svg)
								{!! $Menu->brand_logo_svg !!}
							@elseif ($Menu->brand_logo)
								<img alt="{{ $Menu->brand_label }}" src="{{ $Menu->brand_logo }}" />
							@endif
						</figure>
						<span>{{ $Menu->brand_label }}</span>
					</a>
				</li>
			</ul>

		</section>

		<section class="right">

			<ul class="main">
				@foreach ($Menu->main_items AS $item)
					@component('components.menu.item',['item' => $item],TRUE)
				@endforeach
			</ul>

			@if ($Menu->language_items)
			<ul class="language">
				@foreach ($Menu->language_items AS $item)
					@component('components.menu.item',['item' => $item],TRUE)
				@endforeach
			</ul>
			@endif

			<ul class="search">
				<li>
					<form id="menu-search-form" action="{{ $Menu->search_url }}">
						<fieldset>
							<input id="menu-search-input" name="s" placeholder="{{ $Menu->search_placeholder }}" />
						</fieldset>
						<a class="submit" onclick="menu_searchSubmit()">
							<i class="fa fa-search"></i>
						</a>
					</form>
				</li>
			</ul>

			<ul class="mobile">
				<li>
					<a onclick="menu_mobileToggle(event)" ontouchstart="menu_mobileToggle(event)">
						<i class="open fa fa-bars"></i>
						<i class="close fa fa-times"></i>
						<span>{{ $Menu->mobile_label }}</span>
					</a>
				</li>
			</ul>

		</section>

	</section>

	<section class="off-canvas">
		<ul class="mobile-menu">
			<li class="mobile-search">
				<form id="mobile-search-form" action="{{ $Menu->search_url }}">
					<input id="mobile-search-input" name="s" placeholder="{{ $Menu->search_placeholder }}" />
					<a class="submit" onclick="menu_mobileSearchSubmit()" no-tap>
						<i class="fa fa-search"></i>
					</a>
				</form>
			</li>
			<li class="mobile-menu-divider"></li>
			@foreach ($Menu->main_items AS $item)
				@component('components.menu.item',['item' => $item, 'classname' => 'mobile-menu-item'],TRUE)
			@endforeach
		</ul>
	</section>

	@style('menu.scss')
	@script('menu.js')

</header>
