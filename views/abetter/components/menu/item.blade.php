<li id="menu-{{ $item->id }}" class="{{ $classname or 'menu-item' }} {{ $item->style }} {{ $item->current }} {{ $item->front }} {{ ($item->items)?'has-children':'' }}">
	<a href="{{ $item->url }}" target="{{ $item->target }}">
		<span class="menu-item-label">{{ $item->title }}</span>
	</a>
	@if ($item->items)
	<ul class="sub-menu">
		@foreach ($item->items AS $subitem)
			@component('components.menu.item',['item' => $subitem, 'classname' => (!empty($classname))?$classname:''],TRUE)
		@endforeach
	</ul>
	@endif
</li>
