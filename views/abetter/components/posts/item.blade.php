<li class="list-item">
	@if ($item->image)
	<a class="item-image" href="{{ $item->url }}"><img src="{{ $item->image }}" /></a>
	@endif
	<h4 class="item-label">{{ $item->label }}</h4>
	<a class="item-headline" href="{{ $item->url }}"><h2>{{ $item->headline }}</h2></a>
	<p class="item-lead">{{ _excerpt($item->excerpt,300) }}</p>
	<a class="item-link" href="{{ $item->url }}">{{ $item->link or _dictionary('post_more') }}</a>
</li>
