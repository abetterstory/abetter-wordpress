$Ready(function(){

	var $win = window;
	var $doc = document;

	var $this = $doc.querySelector('.component--footer');
	var $container = $doc.querySelector('.footer--container');

	$win.footer_sticky = function() {
		var wh = $win.innerHeight || $doc.documentElement.clientHeight || $doc.body.clientHeight;
		var fh = $container.offsetHeight;
		if ($this.offsetTop < (wh - fh)) {
			$doc.body.classList.add('footer-sticky');
		} else {
			$doc.body.classList.remove('footer-sticky');
		}
	}

	$win.addEventListener('resize',function(){
		footer_sticky();
	},{ passive: true });

	footer_sticky();

	// ---

	$this.classList.remove('cloak');

});
