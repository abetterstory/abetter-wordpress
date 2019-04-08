<li id="menu-{{ $item->id }}" class="{{ $classname ?? 'menu-item' }} {{ $item->style }} {{ $item->current }} {{ $item->front }} {{ ($item->items)?'has-children':'' }}">
	<a href="{{ $item->url }}" target="{{ $item->target }}">
		@if (!empty($item->icon))<i class="{{ $item->icon }}"></i>@endif
		<span class="menu-item-label">{{ $item->label }}</span>
	</a>
	@if ($item->items && empty($nosub))
	<ul class="sub-menu">
		@foreach ($item->items AS $subitem)
			@component('components.menu.item',['item' => $subitem, 'classname' => $classname ?? ''],TRUE)
		@endforeach
	</ul>
	@endif
</li>
