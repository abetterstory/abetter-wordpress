<?php

namespace ABetter\Wordpress;

use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController {

	public $args = array();
	public $slug = '';
	public $languages = array();
	public $language = '';
	public $post = NULL;

	// ---

	public function __construct($args=NULL) {
		$this->loadWp();
	}

	public function loadWp() {
		define('WP_USE_THEMES', FALSE);
		require_once public_path('wp').'/wp-load.php';
	}

	// ---

	public function getDefaultLanguage() {
		return strtolower(strtok(get_bloginfo('language'),'-'));
	}

	public function getAvailableLanguages() {
		$this->languages = array($this->getDefaultLanguage());
		//$this->languages[] = 'se';
		$this->languages = array_unique($this->languages);
		return $this->languages;
	}

	public function getRequestLanguage() {
		$this->language = (!empty($this->args[0]) && in_array($this->args[0],$this->getAvailableLanguages())) ? $this->args[0] : '';
		if ($this->language) array_splice($this->args, 0, 1);
		return $this->language;
	}

	public function getRequestSlug() {
		return (!empty($this->args)) ? $this->args[count($this->args)-1] : '';
	}

	// ---

	public function isFront() {
		return (empty($this->args) || empty($this->args[0])) ? TRUE : FALSE;
	}

	public function isPosts() {
		return (($i = get_option('page_for_posts')) && $i == $this->post->ID) ? TRUE : FALSE;
	}

	// ---

	public function getPost() {
		if ($this->isFront()) return \ABetterWordpressPost::getFront();
		return \ABetterWordpressPost::getPost($this->slug);
	}

	public function getPostTemplateSuggestions() {
		$suggestions = array();
		$suggestions[] = ($this->post->post_type == 'post') ? 'page' : 'post';
		$suggestions[] = $this->post->post_type;
		$suggestions[] = $this->post->post_type.'--'.$this->post->post_name;
		if ($t = basename(get_page_template_slug($this->post->ID),'.php')) $suggestions[] = $t;
		if ($this->isPosts()) $suggestions[] = 'posts';
		if ($this->isFront()) $suggestions[] = 'front';
		$suggestions = array_reverse($suggestions);
		return $suggestions;
	}

	// ---

	public function handle() {
		$this->args = func_get_args();
		$this->language = $this->getRequestLanguage();
		$this->slug = $this->getRequestSlug();
		$this->post = $this->getPost();
		if (empty($this->post)) return abort(404);
		foreach ($this->getPostTemplateSuggestions() AS $suggestion) {
			if (view()->exists('wordpress.'.$suggestion)) {
				return view('wordpress.'.$suggestion)->with(['post' => $this->post]);
			}
		}
		return "No template found in views/wordpress/";
    }

}
