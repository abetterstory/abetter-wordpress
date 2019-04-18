$Ready(function(){

	var self = this;

	var $win = window;
	var $doc = document;
	var $body = $doc.body;
	var $this = $doc.querySelector('.component--video-player');
	var $container = $this.querySelector('.video-player--container');

	// ---

	self.YT_id;
	self.YT_container;
	self.YT_player;
	self.YT_loaded;

	self.YT_load = function(callback) {
		var yts = $doc.createElement('script');
		yts.src = "/proxy/www.youtube.com/iframe_api";
		$doc.getElementsByTagName('head')[0].appendChild(yts);
		$win.onYouTubeIframeAPIReady = callback;
		self.YT_loaded = true;
	}

	self.YT_open = function(source) {
		self.YT_id = source.replace(/.*\/(.*)$/,"$1");
		self.YT_container = 'video-id-' + self.YT_id;
		$container.id = self.YT_container;
		self.YT_player = new YT.Player(self.YT_container,{
			videoId: self.YT_id,
			playerVars: {
				controls: 1,
				modestbranding: 0,
				showinfo: 0,
				cc_load_policy: 1,
				autoplay: 1,
				loop: 1,
				rel: 0
			},
			events: {
				'onReady': function(event){
					$this.classList.add('video-ready');
					event.target.playVideo();
				}
			}
		});
		$this.classList.add('video-block');
		setTimeout(function(){ $this.classList.add('video-open'); },50);
	}

	self.YT_close = function() {
		$this.classList.remove('video-open','video-ready');
		setTimeout(function(){ $this.classList.remove('video-block'); },1000);
		if (!self.YT_player) return;
		self.YT_player.stopVideo();
		self.YT_player.destroy();
		self.YT_player = null;
	}

	// ---

	$win.videoPlayer = function(source) {
		if (!source) return self.YT_close();
		if (self.YT_loaded) return self.YT_open(source);
		self.YT_load(function(){
			self.YT_open(source);
		});
	}

});
