<?php

namespace GummiIO\AcfComponentField;

use GummiIO\AcfComponentField\Acf;
use GummiIO\AcfComponentField\Admin;
use GummiIO\AcfComponentField\Features\Converter;
use GummiIO\AcfComponentField\Query;
use GummiIO\AcfComponentField\Screens\EditFieldGroup;
use GummiIO\AcfComponentField\Screens\FieldGroup;
use GummiIO\AcfComponentField\Screens\Tools;
use GummiIO\AcfComponentField\Updater;
use GummiIO\AcfComponentField\Upgrader;

/**
 * Plugin Core Class
 *
 * Class which holds the global component plugin instance
 *
 * @since   2.0.0
 * @version 2.0.0
 */
class Core
{
	/**
	 * Plugin entry file path
	 *
     * @since   2.0.0
     * @version 2.0.0
	 */
	protected $file;

	/**
	 * Plugin current version
	 *
     * @since   2.0.0
     * @version 2.0.0
	 */
	protected $version;

	/**
	 * Constructor
	 *
     * @since   2.0.0
     * @version 2.0.0
	 * @param string $file    Plugin's entry file path
	 * @param string $version Plugin's current version
	 */
	public function __construct($file, $version)
	{
		$this->file    = $file;
		$this->version = $version;

		$this->loadModules();

		add_action('plugins_loaded', [$this, 'loadTranslations']);
	}

	/**
	 * Load all the plugin modules
	 *
     * @since   2.0.0
     * @version 2.0.0
	 */
	public function loadModules()
	{
		$this->upgrader = new Upgrader;
		$this->updateer = new Updater;
		$this->admin    = new Admin;
		$this->acf      = new Acf;
		$this->query    = new Query;

		$this->screens  = [
			'edit_field_group' => new EditFieldGroup,
			'field_group'      => new FieldGroup,
			'tools'            => new Tools,
		];

		$this->features  = [
			'converter' => new Converter,
		];

		$this->integrations  = [

		];
	}

	/**
	 * Load the translation files
	 *
     * @since   2.0.0
     * @version 2.0.0
	 */
	public function loadTranslations()
	{
        $domain = 'acf-component_field';
        $locale = apply_filters('plugin_locale', get_locale(), $domain);

        load_textdomain($domain, WP_LANG_DIR . "/plugins/{$domain}-{$locale}.mo");
        load_plugin_textdomain($domain, false, $this->path('assets/langs'));
	}

	/**
	 * Get the current plugin version
	 *
     * @since   2.0.0
     * @version 2.0.0
	 */
	public function version()
	{
		return $this->version;
	}

	/**
	 * Resolve the absolute file path from the plugin's root folder
	 *
     * @since   2.0.0
     * @version 2.0.0
	 * @param  string  $path      File path from the plugin's rood folder
	 * @param  boolean $withDebug Whether it should modify the path to include .min
	 */
    public function path($path = '', $withDebug = false)
    {
    	if ($withDebug && ! SCRIPT_DEBUG) {
    		$path = $this->appendMinExtension($path);
    	}

        return plugin_dir_path($this->file) . trim($path, '/\\');
    }

	/**
	 * Resolve the file url from the plugin's root folder
	 *
     * @since   2.0.0
     * @version 2.0.0
	 * @param  string  $uri       File path from the plugin's rood folder
	 * @param  boolean $withDebug Whether it should modify the path to include .min
	 */
    public function url($uri = '', $withDebug = false)
    {
    	if ($withDebug && ! SCRIPT_DEBUG) {
    		$uri = $this->appendMinExtension($uri);
    	}

        return plugin_dir_url($this->file) . trim($uri, '/\\');
    }

    /**
     * Alter the path, add the .min before the extension
	 *
     * @since   2.0.0
     * @version 2.0.0
     * @param  string $path File path
     */
    protected function appendMinExtension($path)
    {
		$splitExtension   = explode('.', $path);
		$extension        = array_pop($splitExtension);
		$splitExtension[] = 'min';
		$splitExtension[] = $extension;
		return implode('.', $splitExtension);
    }
}
