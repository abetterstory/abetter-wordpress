@php
if (empty($item->url)) $item->url = 'javascript:void(0)';
if (preg_match('/(is|has)-icon/',$item->style)) {
	$item->icon = $item->style;
	$item->style = preg_replace('/fa[^ ]*/',"",$item->style);
}
@endphp
<li class="menu-item {{ $item->style }} {{ $item->current }} {{ $item->front }} {{ ($item->items)?'has-children':'' }}">
	<a href="{{ $item->url }}" target="{{ $item->target }}"@if($item->target=='_blank') rel="noopener"@endif {{ ($item->items)?'onclick=menu_touchify(event)':'' }} data-track="{{ $track ?? 'menu-link' }}">
		@if (!empty($item->icon))<i class="{{ $item->icon }}"></i>@endif
		<span class="menu-item-label">{!! $item->label !!}</span>
	</a>
	@if ($item->items)
	<ul class="sub-menu">
		<li class="menu-item is-parent"><a href="{{ $item->url }}" target="{{ $item->target }}"@if($item->target=='_blank') rel="noopener"@endif data-track="{{ $track ?? 'menu-link' }}"><span class="menu-item-label">{!! $item->label !!}</span></a></li>
		@foreach ($item->items AS $subitem)
			@component('components.menu.item',['item' => $subitem, 'track' => $track ?? 'menu-link'],TRUE)
		@endforeach
	</ul>
	@endif
</li>
