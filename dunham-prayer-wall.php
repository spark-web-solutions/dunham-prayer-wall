<?php
/**
 * @link https://dunhamandcompany.com/
 * @since 1.0.0
 * @package Dunham_Prayer_Wall
 * Plugin Name: Prayer Wall
 * Plugin URI: https://dunhamandcompany.com/
 * Update URI: https://dunhamandcompany.com/
 * Description: Add a prayer wall to your site to allow people to submit and view prayer requests
 * Version: 1.0.1
 * Author: Dunham + Company
 * Author URI: https://dunhamandcompany.com/
 * Text Domain: dunham-prayer-wall
 * Domain Path: /languages
 * Licence: Copyright Dunham and Company. Unauthorised distribution of this software, with or without modifications is expressly prohibited.
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die();
}

/**
 * Current plugin version.
 */
define('DUNHAM_PRAYER_WALL_VERSION', '1.0.1');
define('DUNHAM_PRAYER_WALL_PATH', plugin_dir_path(__FILE__));
define('DUNHAM_PRAYER_WALL_BASE', plugin_basename(__FILE__));

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-dunham-prayer-wall-activator.php
 */
function activate_dunham_prayer_wall() {
	require_once DUNHAM_PRAYER_WALL_PATH . 'includes/class-dunham-prayer-wall-activator.php';
	Dunham_Prayer_Wall_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-dunham-prayer-wall-deactivator.php
 */
function deactivate_dunham_prayer_wall() {
	require_once DUNHAM_PRAYER_WALL_PATH . 'includes/class-dunham-prayer-wall-deactivator.php';
	Dunham_Prayer_Wall_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_dunham_prayer_wall');
register_deactivation_hook(__FILE__, 'deactivate_dunham_prayer_wall');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require DUNHAM_PRAYER_WALL_PATH . 'includes/class-dunham-prayer-wall.php';

/**
 * Begins execution of the plugin.
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 * @since 1.0.0
 */
function run_dunham_prayer_wall() {
	new Dunham_Prayer_Wall();
}
run_dunham_prayer_wall();

/**
 * Logic to find our custom templates - check the theme first using locate_template(), and use our local version as fallback
 * @param string $template
 * @param array $args
 * @since 1.0.0
 */
function dunham_prayer_wall_locate_template($template, $args) {
	$located = locate_template('dunham-prayer-wall/'.$template, true, true, $args);
	if (empty($located)) {
		extract($args);
		include_once(DUNHAM_PRAYER_WALL_PATH.'public/templates/'.$template);
	}
}
