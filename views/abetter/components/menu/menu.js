$Ready(function(){

	var $win = window;
	var $doc = document;

	var $this = $doc.querySelector('.component--menu');
	var $mobile = $doc.querySelector('.component--menu--mobile');
	var $hit = $doc.querySelector('.component--menu--hit');
	var $thisclass = $this.classList;

	// ---

	var smo = 'mobile-menu-open';
	var isTouch = (function(){ try { $doc.createEvent('TouchEvent'); return true; } catch (e) { return false; }})();

	$win.menu_touchify = function(e) {
		if (!isTouch) return;
		e.preventDefault();
		var $e = (e.target.tagName == 'A') ? e.target : e.target.parentNode;
		var $parent = $e.parentNode;
		if ($parent.classList.contains(smo)) {
			$parent.classList.remove(smo,'touchify');
		} else {
			$parent.classList.add(smo,'touchify');
		}
	}

	// ---

	var s_pos = function() { return $win.pageYOffset || $doc.documentElement.scrollTop; };
	var s_dir = function(s) { var d = 0; if (s > s_x) d = 1; else if (s < s_x) d = -1; return d; }
	var st = 'scroll-top', su = 'scroll-up', sd = 'scroll-down', sc = 'scroll-change';
	var mso = 'menu-search-open';
	var s_delay = null;
	var s_x = s_pos();

	$win.menu_scroll = function() {
		var s = s_pos(), d = s_dir(s);
		if (s < 60) {
			$thisclass.add(st);
			$thisclass.remove(sd,su,sc);
		} else {
			if (d == 1) {
				if ($thisclass.contains(su)) $thisclass.add(sc);
				$thisclass.add(sd);
				$thisclass.remove(st,su,mso);
			} else if (d == -1) {
				if ($thisclass.contains(sd)) $thisclass.add(sc);
				$thisclass.add(su);
				$thisclass.remove(st,sd);
			}
			clearTimeout(s_delay);
			s_delay = setTimeout(function(){
				$thisclass.remove(sc);
			},1000);
		}
		s_x = s;
	}

	$hit.addEventListener('mouseover',function(){
		$thisclass.add(su);
		$thisclass.remove(sd);
	});

	$win.addEventListener('scroll',function(){
		menu_scroll();
	},{ passive: true });

	menu_scroll();

	// ---

	var mmo = 'mobile-menu-open';

	$win.menu_mobileToggle = function(event) {
		if ($win.menu_mobileToggle_active) return;
		$win.menu_mobileToggle_active = true;
		setTimeout(function(){ $win.menu_mobileToggle_active = false; },500);
		$doc.body.classList.toggle(mmo);
		$thisclass.toggle(mmo);
		if (!$thisclass.contains(mmo)) {
			$mobile.classList.remove('slide-out','slide-in');
			$mobileActives = $mobile.querySelectorAll('.is-active');
			[].forEach.call($mobileActives,function($active){
				$active.classList.remove('is-active');
			});
		}
	}

	// ---

	;[].slice.call($mobile.querySelectorAll('.has-children > a')).forEach(function($a){
		var $li = $a.parentNode;
		var level = ($li.parentNode.dataset.level || 1);
		$a.addEventListener('click',function(e){
			if ($li.classList.contains('is-active')) return;
			e.preventDefault();
			$mobile.classList.remove('slide-out');
			$mobile.classList.add('slide-in');
			$mobile.dataset.level = level;
			;[].slice.call($mobile.querySelectorAll('.is-active')).forEach(function($active){
				$active.classList.remove('is-active');
			});
			$li.classList.add('is-active');
		});
	});

	;[].slice.call($mobile.querySelectorAll('.back-item > a')).forEach(function($a){
		var $li = $a.parentNode;
		var level = ($li.parentNode.dataset.level || 1) - 1;
		$a.addEventListener('click',function(e){
			e.preventDefault();
			$mobile.classList.remove('slide-in');
			$mobile.classList.add('slide-out');
			$mobile.dataset.level = level;
			setTimeout(function(){
				;[].slice.call($mobile.querySelectorAll('.is-active')).forEach(function($active){
					$active.classList.remove('is-active');
				});
			},500);
		});
	});

	// ---

	$thisclass.remove('cloak');

});
