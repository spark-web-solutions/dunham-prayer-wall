<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://dunhamandcompany.com/
 * @since      1.0.0
 *
 * @package    Dunham_Prayer_Wall
 * @subpackage Dunham_Prayer_Wall/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Dunham_Prayer_Wall
 * @subpackage Dunham_Prayer_Wall/includes
 * @author     Dunham + Company <plugins@sparkweb.com.au>
 */
class Dunham_Prayer_Wall_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'dunham-prayer-wall',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
