<section class="component--video-block">

	@style('video-block.scss')

	<a href="javascript:videoPlayer('{{ $video ?? '' }}')" data-track="video-play:{{ preg_replace('/https?\:\/\//','',$video) }}">

		{!! $slot !!}

		<nav>@svg('/images/svg/link.svg')<span>@dictionary('video_play_label')</span></nav>

	</a>

</section>
