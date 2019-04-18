$Ready(function(){

	var $win = window;
	var $doc = document;
	var $body = $doc.body;
	var $this = $doc.querySelector('.component--cover');
	var $video = $doc.querySelector('.component--cover video');
	var $sources = $doc.querySelectorAll('.component--cover video source');
	var $menu = $doc.querySelector('.component--menu');
	var $main = $doc.querySelector('.component--main');

	if ($menu) $menu.classList.add('transparent');

	// ---

	if ($this.classList.contains('parallax')) {
		$main.classList.add('parallax');
		var parallax = window.BasicScroll.create({
			elem: $this,
			from: 'top-top', to: 'bottom-top',
			props: {
				'--cover--opacity': { from: '1', to: '-0.2' },
				'--main--pos': { from: '0', to: '50vh' }
			}
		}).start();
	}

	// ---

	if ($video) {

		var cover_video_load = function() {
			var playrate = Number($video.getAttribute('playrate') || 1);
			[].forEach.call($sources,function($source){
				$source.src = $source.dataset.src;
			});
			$video.load();
			$video.oncanplay = function(){
				$this.classList.add('ready');
			}
			$video.playbackRate = playrate;
		}

		cover_video_load();

	}

	// ---

	$this.classList.remove('cloak');

});
