<?php
/**
 * Custom template for displaying the main prayer requests archive.
 * Override this by copying it to your theme and making the desired changes.
 *
 * @link https://sparkweb.com.au
 * @since 1.0.0
 * @version 1.0.0
 *
 * @package	Dunham_Prayer_Wall
 * @subpackage Dunham_Prayer_Wall/public/templates
 */

get_header();

// Put the posts into an array because the template is also used by other processes so can't rely on The Loop
$requests = array();
if (have_posts()) {
	while (have_posts()) {
		/**
		 * @var WP_Post $post
		 */
		the_post();
		$requests[] = $post;
	}
}
dunham_prayer_wall_locate_template('dunham-prayer-wall.php', array('requests' => $requests));
get_footer();
