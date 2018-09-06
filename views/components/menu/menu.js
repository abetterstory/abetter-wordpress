$Ready(function(){

	var $this = document.getElementById('menu');

	var $menuSearchForm = document.getElementById('menu-search-form');
	var $menuSearchInput = document.getElementById('menu-search-input');

	var $mobileSearchForm = document.getElementById('mobile-search-form');
	var $mobileSearchInput = document.getElementById('mobile-search-input');

	var isTouch = (function(){ try { document.createEvent('TouchEvent'); return true; } catch (e) { return false; }})();

	// ---

	menu_searchSubmit = function() {
		var value = $menuSearchInput.value;
		if (!value && $menuSearchForm.classList.contains('menu-search-open')) {
			return $menuSearchForm.classList.remove('menu-search-open');
		} else if (!value) {
			$menuSearchForm.classList.add('menu-search-open');
			return $menuSearchInput.focus();
		}
		$mobileSearchForm.submit();
	}

	// ---

	menu_mobileToggle = function(event) {
		if (window.menu_mobileToggle_active) return;
		window.menu_mobileToggle_active = true;
		setTimeout(function(){ window.menu_mobileToggle_active = false; },500);
		document.body.classList.toggle('menu-mobile');
	}

	menu_mobileSearchSubmit = function() {
		var value = $mobileSearchInput.value;
		if (!value) return $mobileSearchInput.focus();
		$mobileSearchForm.submit();
	}

});
