<div class="item--search {{ $item->type ?? '' }}">

	@style('searchitem.scss')

	@if(!empty($item->image))
	<figure>
		<a href="{{ $item->url ?? '' }}" style="background-image:url('/image/w800{{ $item->image }}')" /></a>
	</figure>
	@endif

	<hgroup>
		@if(!empty($item->more))
		<a href="{{ $item->more }}">
			@if(!empty($item->headline))<h4>{{ $item->headline }}</h4>@endif
			@if(!empty($item->excerpt))<p>{{ $item->excerpt }}</p>@endif
			@if(!empty($item->link))<p nav>{{ $item->link }}</p>@endif
		</a>
		@else
		@if(!empty($item->label))<label>{{ $item->label }}</label>@endif
		@if(!empty($item->headline))<h4><a href="{{ $item->url ?? '' }}">{{ $item->headline }}</a></h4>@endif
		@if(!empty($item->dateline))<date>{!! $item->dateline !!}</date>@endif
		@if(!empty($item->excerpt))<p>{{ $item->excerpt }}</p>@endif
		@if(!empty($item->link))<a nav href="{{ $item->url ?? '' }}">{{ $item->link }}</a>@endif
		@endif
	</hgroup>

</div>
