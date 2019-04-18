@debug('default:components/cover/cover.blade.php')

@inject('Cover')

@if($Cover->visible)
<section class="component--cover {{ $Cover->class }} cloak">

	@style('cover.scss')

	@component('components.slide',['item' => $Cover],TRUE)

	@script('cover.js')

</section>
@if($Cover->video_play)
@component('components.video.video-player',TRUE)
@endif
@endif
