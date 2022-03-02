<?php
/**
 * The file that defines the core plugin class
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 * @link https://dunhamandcompany.com/
 * @since 1.0.0
 * @package Dunham_Prayer_Wall
 * @subpackage Dunham_Prayer_Wall/includes
 */

/**
 * The core plugin class.
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 * @since 1.0.0
 * @package Dunham_Prayer_Wall
 * @subpackage Dunham_Prayer_Wall/includes
 * @author Dunham + Company <plugins@sparkweb.com.au>
 */
class Dunham_Prayer_Wall {

	/**
	 * The unique identifier of this plugin.
	 * @since 1.0.0
	 * @access protected
	 * @var string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 * @since 1.0.0
	 * @access protected
	 * @var string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 * @since 1.0.0
	 */
	public function __construct() {
		if (defined('DUNHAM_PRAYER_WALL_VERSION')) {
			$this->version = DUNHAM_PRAYER_WALL_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'dunham-prayer-wall';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_ia();
	}

	/**
	 * Load the required dependencies for this plugin.
	 * Include the following files that make up the plugin:
	 * - Dunham_Prayer_Wall_i18n. Defines internationalization functionality.
	 * - Dunham_Prayer_Wall_Admin. Defines all hooks for the admin area.
	 * - Dunham_Prayer_Wall_Public. Defines all hooks for the public side of the site.
	 * @since 1.0.0
	 * @access private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-dunham-prayer-wall-i18n.php';

		/**
		 * Class for simplifying creation of custom CPTs
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-dunham-prayer-wall-cpt.php';

		/**
		 * Class for simplifying creation of custom taxonomies
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-dunham-prayer-wall-tax.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-dunham-prayer-wall-admin.php';

		/**
		 * The class responsible for handling plugin updates.
		 */
		require_once plugin_dir_path(dirname(__FILE__)).'admin/class-dunham-prayer-wall-updates.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-dunham-prayer-wall-public.php';
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 * Uses the Dunham_Prayer_Wall_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 * @since 1.0.0
	 * @access private
	 */
	private function set_locale() {
		$plugin_i18n = new Dunham_Prayer_Wall_i18n();

		add_action('plugins_loaded', array($plugin_i18n, 'load_plugin_textdomain'));
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 * @since 1.0.0
	 * @access private
	 */
	private function define_admin_hooks() {
		$plugin_admin = new Dunham_Prayer_Wall_Admin($this->get_plugin_name(), $this->get_version());

		// Core functions
		add_action('admin_init', array($plugin_admin, 'updates'));
		add_action('admin_init', array($plugin_admin, 'register_settings'));
		add_action('admin_menu', array($plugin_admin, 'menu'));

		// Admin scripts and styles
        add_action('admin_enqueue_scripts', array($plugin_admin, 'enqueue_styles'));
        add_action('admin_enqueue_scripts', array($plugin_admin, 'enqueue_scripts'));

        // AJAX functionality
        add_action('wp_ajax_dunham_prayer_wall_submit_request', array($plugin_admin, 'ajax_submit_request'));
        add_action('wp_ajax_nopriv_dunham_prayer_wall_submit_request', array($plugin_admin, 'ajax_submit_request'));
        add_action('wp_ajax_dunham_prayer_wall_pray', array($plugin_admin, 'ajax_pray'));
        add_action('wp_ajax_nopriv_dunham_prayer_wall_pray', array($plugin_admin, 'ajax_pray'));

        // Misc
        add_action('transition_post_status', array($plugin_admin, 'notify_submitter'), 10, 3);
        add_filter('plugin_action_links_'.DUNHAM_PRAYER_WALL_BASE,  array($plugin_admin, 'settings_link'));
        add_action('transition_comment_status', array($plugin_admin, 'comment_status_transition'), 10, 3);
        add_filter('comment_post',  array($plugin_admin, 'comment_added'), 10, 3);

        // Cron
        add_action('dunham_prayer_wall_send_summary_email', array($plugin_admin, 'send_summary_email'));
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 * @since 1.0.0
	 * @access private
	 */
	private function define_public_hooks() {
		$plugin_public = new Dunham_Prayer_Wall_Public($this->get_plugin_name(), $this->get_version());

		add_action('template_include', array($plugin_public, 'template_include'));
		add_action('wp_enqueue_scripts', array($plugin_public, 'enqueue_styles'));
		add_action('wp_enqueue_scripts', array($plugin_public, 'enqueue_scripts'));

		add_action('wp_set_comment_status', array($plugin_public, 'send_prayer_comment_notification'));
		add_action('comment_post', array($plugin_public, 'send_prayer_comment_notification'));
		add_action('pre_get_posts', array($plugin_public, 'pre_get_posts'));

		add_shortcode('dunham_prayer_wall', array($plugin_public, 'shortcode_dunham_prayer_wall'));
	}

	/**
	 * Register our custom information architecture
	 * @since 1.0.0
	 * @access private
	 */
	private function define_ia() {
		new Dunham_Prayer_Wall_Cpt('Prayer Request', 'Prayer Requests', array(
				'public' => true,
				'has_archive' => 'prayer',
				'show_ui' => true,
				'menu_icon' => 'dashicons-testimonial',
				'supports' => array(
						'title',
						'editor',
						'comments',
						'author',
						'thumbnail'
				),
				'rewrite' => array(
						'slug' => 'request',
						'with_front' => false
				)
		));

		new Dunham_Prayer_Wall_Tax('Prayer Category', 'Prayer Categories', array('prayerrequest'), array(
				'public' => false,
				'has_archive' => false,
				'show_ui' => true,
		));

		$db_version = get_option('dunham_prayer_wall_version', 0);
		if (version_compare($db_version, DUNHAM_PRAYER_WALL_VERSION, '<')) {
			add_action('init', function() {
				flush_rewrite_rules(false);
				update_option('dunham_prayer_wall_version', DUNHAM_PRAYER_WALL_VERSION);
			});
		}

		add_action('admin_init', array($this, 'create_default_categories'));
	}

	/**
	 * Set up default prayer request categories if none currently exist
	 * @since 1.0.0
	 */
	public function create_default_categories() {
		$cat_count = wp_count_terms(array('taxonomy' => 'prayercategory', 'hide_empty' => false));
		if (0 === (int)$cat_count) {
			$default_categories = array(
					'Emotional',
					'Financial',
					'Forgiveness/Repentance',
					'God\'s Wisdom/Guidance',
					'Health/Healing',
					'My Christian Walk',
					'Other',
					'Relationships',
					'Salvation for Family/Friend',
					'Spiritual',
					'Work/Employment',
			);
			foreach ($default_categories as $category) {
				wp_insert_term($category, 'prayercategory');
			}
		}
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 * @since 1.0.0
	 * @return string The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * Retrieve the version number of the plugin.
	 * @since 1.0.0
	 * @return string The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}

/**
 * Determine whether a user has admin rights for prayer requests
 * @param WP_User $user
 * @return boolean
 * @since 1.0.0
 */
function dunham_prayer_wall_is_official_prayer(WP_User $user = null) {
	if (!$user instanceof WP_User) {
		$user = wp_get_current_user();
	}
	return user_can($user, 'edit_posts');
}

/**
 * Takes a name and reduces it to protect privacy
 * @param string $name Person's name to be protected
 * @param string $location Optional. Will be appended to name if provided.
 * @return string
 * @since 1.0.0
 */
function dunham_prayer_wall_privatise_author_name($name, $location = '') {
	$name = strtoupper(substr($name, 0, 1));

	if (!empty($location)) {
		$name .= ', '.$location;
	}

	return $name;
}

/**
 * Generate "teaser" text from longer content
 * @param string $content Text to generate teaser from
 * @param integer $max_chars Optional. Default 250.
 * @param string $suffix Optional. Default '...'.
 * @return string Teaser text
 * @since 1.0.0
 */
function dunham_prayer_wall_extract($content, $max_chars = 250, $suffix = '...') {
	$content = str_replace("\n", ' ', strip_shortcodes($content));
	if (strlen(strip_tags($content)) > $max_chars) {
		return substr(strip_tags($content), 0, strrpos(substr(strip_tags($content), 0, $max_chars), ' ')+1).$suffix."\n";
	}
	return $content;
}

/**
 * Generate "teaser" text for post. Will use custom excerpt if defined, otherwise will look for WP "More" tag and return preceding content, else generate automatic extract via @see dunham_prayer_wall_extract().
 * @param integer|WP_Post $post Optional. Post to use (will use global $post if not specified).
 * @param integer $max_chars Optional. Default 250.
 * @param string $suffix Optional. Default '...'.
 * @return string|boolean Teaser text or false on failure
 * @since 1.0.0
 */
function dunham_prayer_wall_post_extract($post = null, $max_chars = 250, $suffix = '...') {
	$post = get_post($post);
	if (!$post instanceof WP_Post) {
		return false;
	}
	if (!empty($post->post_excerpt)) { // Custom Excerpt
		$output = get_the_excerpt($post);
	} elseif (preg_match('/<!--more(.*?)?-->/', $post->post_content)) { // More
		global $more;
		$tmp_more = $more;
		$more = false;
		$output = get_the_content('', false, $post).$suffix;
		$more = $tmp_more;
	} else {
		$output = dunham_prayer_wall_extract($post->post_content, $max_chars, $suffix);
	}

	return $output;
}

/**
 * Determine whether white or black provides a better contrast against the given colour
 * @param string $hexColor
 * @return string Either black ('#000000') or white ('#FFFFFF'), whichever provides better contrast
 * @since 1.0.0
 * @link https://stackoverflow.com/a/42921358/2117389
 */
function dunham_prayer_wall_get_contrast_colour($hexColor) {
	// hexColor RGB
	$R1 = hexdec(substr($hexColor, 1, 2));
	$G1 = hexdec(substr($hexColor, 3, 2));
	$B1 = hexdec(substr($hexColor, 5, 2));

	// Black RGB
	$blackColor = "#000000";
	$R2BlackColor = hexdec(substr($blackColor, 1, 2));
	$G2BlackColor = hexdec(substr($blackColor, 3, 2));
	$B2BlackColor = hexdec(substr($blackColor, 5, 2));

	// Calc contrast ratio
	$L1 = 0.2126 * pow($R1 / 255, 2.2) +
	0.7152 * pow($G1 / 255, 2.2) +
	0.0722 * pow($B1 / 255, 2.2);

	$L2 = 0.2126 * pow($R2BlackColor / 255, 2.2) +
	0.7152 * pow($G2BlackColor / 255, 2.2) +
	0.0722 * pow($B2BlackColor / 255, 2.2);

	$contrastRatio = 0;
	if ($L1 > $L2) {
		$contrastRatio = (int)(($L1 + 0.05) / ($L2 + 0.05));
	} else {
		$contrastRatio = (int)(($L2 + 0.05) / ($L1 + 0.05));
	}

	// If contrast is more than 5, return black
	if ($contrastRatio > 5) {
		return '#000000';
	} else {
		// if not, return white
		return '#FFFFFF';
	}
}
