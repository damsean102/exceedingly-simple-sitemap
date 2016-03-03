<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       http://samdean.co.uk/
 * @since      1.0.0
 *
 * @package    Exclude_From_Sitemap
 * @subpackage Exclude_From_Sitemap/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Exclude_From_Sitemap
 * @subpackage Exclude_From_Sitemap/includes
 * @author     Sam Dean <sam@samdean.co.uk>
 */
class Exclude_From_Sitemap_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'exclude-from-sitemap',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
