$Ready(function(){

	var $this = document.getElementById('footer');
	var $container = document.getElementsByClassName('footer-container');

	footer_sticky = function() {
		var wh = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;
		var fh = $container[0].offsetHeight;
		if ($this.offsetTop < (wh - fh)) {
			document.body.classList.add('footer-sticky');
		} else {
			document.body.classList.remove('footer-sticky');
		}
	}

	window.onresize = function(){ footer_sticky(); };

	footer_sticky();

});
