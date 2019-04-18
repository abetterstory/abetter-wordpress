<div class="item--slide {{ $item->style ?? '' }}">

	@style('slide.scss')

	<hgroup>
		@if(!empty($item->label))<label>{!! $item->label !!}</label>@endif
		@if(!empty($item->headline))<h1>{!! $item->headline !!}</h1>@endif
		@if(!empty($item->lead))<p>{!! $item->lead !!}</p>@endif
		<nav>
		@if(!empty($item->link))<a class="link" href="{{ $item->url }}"><span>{!! $item->link !!}</span></a>@endif
		@if(!empty($item->video_play))<a class="play" href="javascript:void(0)" onclick="videoPlayer('{{ $item->video_play }}')"><span>{!! $item->video_link !!}</span></a>@endif
		</nav>
	</hgroup>

	@if(!empty($item->image))
	<figure class="background image" @if(!empty($item->image_background))style="background-image:url('/image/w1400{{ $item->image_filter }}{{ $item->image }}')"@endif>
		@if(empty($item->image_background))
		<img src="/image/w1400{{ $item->image_filter }}{{ $item->image }}" />
		@endif
	</figure>
	@endif

	@if(!empty($item->video_preview))
	<figure class="background video">
		<video muted="muted" loop="loop" autoplay="autoplay" webkit-playsinline playsinline playrate="{{ $item->video_preview_playrate }}">
			@if(!empty($item->video_preview_mp4))<source data-src="{{ $item->video_preview_mp4 }}" type="video/mp4" />@endif
			@if(!empty($item->video_preview_webm))<source data-src="{{ $item->video_preview_webm }}" type="video/webm" />@endif
			@if(!empty($item->video_preview_ogg))<source data-src="{{ $item->video_preview_ogg }}" type="video/ogg" />@endif
		</video>
	</figure>
	@endif
	
</div>
