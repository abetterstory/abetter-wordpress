@debug('default:components/menu/menu.blade.php')

@inject('Menu')

<header class="component--menu {{ $Menu->style }} cloak">

	@style('menu.scss')

	<nav class="bar"></nav>

	<nav class="main">
		<ul class="left">
			<li class="brand">
				<a href="{{ $Menu->front_url }}">
					<figure class="theme-color">{!! $Menu->brand_svg !!}</figure>
					<span class="theme-color">{{ $Menu->brand_label }}</span>
				</a>
			</li>
		</ul>
		<ul class="right">
			<li class="mobile-toggle">
				<a href="javascript:void(0);" onclick="menu_mobileToggle()">
					<span class="theme-color">{{ $Menu->menu_label }}</span>
					<figure class="theme-color" nowrap>{!! $Menu->menu_svg !!}</figure>
				</a>
			</li>
		</ul>
	</nav>

	<nav class="component--menu--mobile off-canvas">
		<ul class="mobile-menu">
			<!--
			<li class="search-item">
				<form id="mobile-search-form" action="{{ $Menu->search_url }}" onsubmit="menu_mobileSearchSubmit(event)">
					<figure>
						<input id="mobile-search-input" name="s" placeholder="{{ $Menu->search_placeholder }}" autocomplete="off" spellcheck="false" />
					</figure>
					<a class="submit" onclick="menu_mobileSearchSubmit(event)" data-track="menu-mobile-search">
						<i class="fa fa-search"></i>
					</a>
				</form>
			</li>
			-->
			@foreach ($Menu->main_items AS $item)
				@php if ($item->front) continue @endphp
				@component('components.menu.itemoffcanvas',['item' => $item],TRUE)
			@endforeach
		</ul>
	</nav>

	@script('menu.js')

</header>

<aside class="component--menu--hit"></aside>
