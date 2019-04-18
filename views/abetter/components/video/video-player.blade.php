@debug('default:components/video/video-player.blade.php')

<aside class="component--video-player">

	@style('video-player.scss')

	<a onclick="videoPlayer()" data-track="video-close">
		<nowrap><span>@dictionary('video_close',NULL,'Close')</span><i class="icon close">✕</i></nowrap>
	</a>

	<figure class="video-player--container"></figure>

	@script('video-player.js')

</aside>
