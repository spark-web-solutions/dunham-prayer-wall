<?php

/**
 * The public-facing functionality of the plugin.
 * @link https://dunhamandcompany.com/
 * @since 1.0.0
 * @package Dunham_Prayer_Wall
 * @subpackage Dunham_Prayer_Wall/public
 */

/**
 * The public-facing functionality of the plugin.
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 * @package Dunham_Prayer_Wall
 * @subpackage Dunham_Prayer_Wall/public
 * @author Dunham + Company <plugins@sparkweb.com.au>
 */
class Dunham_Prayer_Wall_Public {

	/**
	 * The ID of this plugin.
	 * @since 1.0.0
	 * @access private
	 * @var string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 * @since 1.0.0
	 * @access private
	 * @var string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Where our custom templates are stored
	 *
	 * @since 1.0.0
	 * @access private
	 * @var string $templates_path The absolute path to our templates directory
	 */
	private $templates_path;

	/**
	 * Initialize the class and set its properties.
	 * @since 1.0.0
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version The version of this plugin.
	 */
	public function __construct($plugin_name, $version) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->templates_path = trailingslashit(trailingslashit(dirname(__FILE__)).'templates');
	}

	/**
	 * Use our templates for prayer requests unless overridden in the theme
	 * @param string $template Path to template file
	 * @return string Path to template file
	 * @since 1.0.0
	 */
	public function template_include($template) {
		$template_name = basename($template);
		if (is_singular('prayerrequest')) {
			$template_name = 'single-prayerrequest.php';
		} elseif (is_post_type_archive('prayerrequest')) {
			$template_name = 'archive-prayerrequest.php';
		}

		if ($template_name !== basename($template) && file_exists($this->templates_path.$template_name)) {
			$template = $this->templates_path.$template_name;
		}

		return $template;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 * @since 1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/dunham-prayer-wall-public.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script('masonry');
		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/dunham-prayer-wall-public.js', array('jquery', 'masonry'), $this->version, true);
		wp_localize_script($this->plugin_name, 'prayerRequests', array('ajaxurl' => admin_url('admin-ajax.php')));
	}

	/**
	 * Retrieve the list of available prayer categories
	 * @return WP_Term[]|number|WP_Error
	 * @since 1.0.0
	 */
	public static function get_categories() {
		$args = array(
				'taxonomy' => 'prayercategory',
				'hide_empty' => false,
				'orderby' => 'name',
				'order' => 'ASC',
		);
		return get_terms($args);
	}

	/**
	 * Send comment notification email to prayer requester
	 * @param integer $comment_id
	 * @see wp_notify_postauthor()
	 */
	public function send_prayer_comment_notification($comment_id) {
		$comment = get_comment($comment_id);
		if ($comment instanceof WP_Comment && 1 == $comment->comment_approved && 'prayerrequest' == get_post_type($comment->comment_post_ID)) {
			$requester = get_post_meta($comment->comment_post_ID, 'email', true);
			if ($requester && $comment->comment_author_email != $requester) {
				// The following logic is largely a simplified version of the native WP wp_notify_postauthor() function, with a few wording changes for the email content
				$post = get_post($comment->comment_post_ID);

				$switched_locale = switch_to_locale(get_locale());

				// The blogname option is escaped with esc_html() on the way into the database in sanitize_option().
				// We want to reverse this for the plain text arena of emails.
				$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

				/* translators: Comment notification email subject. 1: Site title, 2: Post title. */
				$subject = sprintf(__('[%1$s] Comment: "%2$s"', 'dunham-prayer-wall'), $blogname, $post->post_title);

				$comment_content = wp_specialchars_decode($comment->comment_content);
				/* translators: %s: Post title. */
				$notify_message = sprintf(__('New comment on your prayer request "%s"', 'dunham-prayer-wall'), $post->post_title) . "\r\n";
				/* translators: %s: Comment author's name. */
				$notify_message .= sprintf(__('Author: %s', 'dunham-prayer-wall'), $comment->comment_author) . "\r\n";

				/* translators: %s: Comment text. */
				$notify_message .= sprintf(__('Comment: %s', 'dunham-prayer-wall'), "\r\n" . $comment_content) . "\r\n\r\n";
				$notify_message .= __('You can see all comments on your request here:', 'dunham-prayer-wall') . "\r\n";

				$notify_message .= get_permalink($comment->comment_post_ID) . "#comments\r\n\r\n";
				/* translators: %s: Comment URL. */
				$notify_message .= sprintf(__('Permalink: %s', 'dunham-prayer-wall'), get_comment_link($comment)) . "\r\n";

				$wp_email = 'wordpress@' . preg_replace('#^www\.#', '', wp_parse_url(network_home_url(), PHP_URL_HOST));

				if ('' === $comment->comment_author) {
					$from = "From: \"$blogname\" <$wp_email>";
				} else {
					$from = "From: \"$comment->comment_author\" <$wp_email>";
				}

				$message_headers = "$from\n" . 'Content-Type: text/plain; charset="' . get_option('blog_charset') . "\"\n";

				wp_mail($requester, wp_specialchars_decode($subject), $notify_message, $message_headers);

				if ($switched_locale) {
					restore_previous_locale();
				}
			}
		}
	}
}
