@php global $level; $level = $level ?? 1; $level++; @endphp
<li class="menu-item {{ $style ?? '' }} {{ $item->style }} {{ $item->current }} {{ $item->front }} {{ ($item->items)?'has-children':'' }}">
	<a href="{{ $item->url }}" target="{{ $item->target }}"@if($item->target=='_blank') rel="noopener"@endif data-track="{{ $track ?? 'menu-mobile-link' }}">
		<span class="menu-item-label">{{ $item->title }}</span>
	</a>
	@if ($item->items)
	<ul class="sub-menu level-{{ $level }}" data-level="{{ $level }}">
		<li class="menu-item back-item"><a href="javascript:void(0)" data-track="menu-mobile-back"><span class="menu-item-label">{{ $Menu->back_label }}</span></a></li>
		<li class="menu-item is-parent"><a href="{{ $item->url }}" data-track="{{ $track ?? 'menu-mobile-link' }}"><span class="menu-item-label">{{ $item->title }}</span></a></li>
		@foreach ($item->items AS $subitem)
			@component('components.menu.itemoffcanvas',['item' => $subitem, 'style' => 'sub-item', 'track' => $track ?? 'menu-mobile-link'],TRUE)
		@endforeach
	</ul>
	@endif
</li>
