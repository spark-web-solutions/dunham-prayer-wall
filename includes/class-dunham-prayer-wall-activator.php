<?php
/**
 * Fired during plugin activation
 *
 * @link       https://dunhamandcompany.com/
 * @since      1.0.0
 *
 * @package    Dunham_Prayer_Wall
 * @subpackage Dunham_Prayer_Wall/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Dunham_Prayer_Wall
 * @subpackage Dunham_Prayer_Wall/includes
 * @author     Dunham + Company <plugins@sparkweb.com.au>
 */
class Dunham_Prayer_Wall_Activator {
	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		add_action('init', 'flush_rewrite_rules');
	}
}
