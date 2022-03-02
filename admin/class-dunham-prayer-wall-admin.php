<?php

/**
 * The admin-specific functionality of the plugin.
 * @link https://dunhamandcompany.com/
 * @since 1.0.0
 * @package Dunham_Prayer_Wall
 * @subpackage Dunham_Prayer_Wall/admin
 */

/**
 * The admin-specific functionality of the plugin.
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 * @package Dunham_Prayer_Wall
 * @subpackage Dunham_Prayer_Wall/admin
 * @author Dunham + Company <plugins@sparkweb.com.au>
 */
class Dunham_Prayer_Wall_Admin {

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
	 * Initialize the class and set its properties.
	 * @since 1.0.0
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 */
	public function __construct($plugin_name, $version) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		if (isset($_GET['page']) && $_GET['page'] == dunham-prayer-wall-export && $_GET['export'] == 'csv') {
			add_action('init', array($this, 'export_csv'));
		}
	}

	/**
	 * Register the stylesheets for the admin area.
	 * @since 1.0.0
	 */
	public function enqueue_styles() {
// 		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/dunham-prayer-wall-admin.css', array(), $this->version, 'all');
		wp_enqueue_style('wp-color-picker');
	}

	/**
	 * Register the JavaScript for the admin area.
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/dunham-prayer-wall-admin.js', array('jquery', 'wp-color-picker'), $this->version, true);
	}

	/**
	 * Register our update handler
	 * @since 1.0.0
	 */
	public function updates() {
		new Dunham_Prayer_Wall_Updates(DUNHAM_PRAYER_WALL_PATH.'dunham-prayer-wall.php', 'spark-web-solutions', 'dunham-prayer-wall');
	}

	/**
	 * Add settings link to plugins page
	 * @param array $links
	 * @return array
	 * @since 1.0.0
	 */
	public function settings_link($links) {
		$url = esc_url(add_query_arg('page', 'dunham-prayer-wall-settings', get_admin_url().'options-general.php'));
		$settings_link = "<a href='$url'>" . __('Settings', 'dunham-prayer-wall') . '</a>';
		array_push($links, $settings_link);
		return $links;
	}

	/**
	 * Add our custom menu items
	 * @since 1.0.0
	 */
	public function menu() {
		add_submenu_page('options-general.php', __('Prayer Wall Settings', 'dunham-prayer-wall'), __('Prayer Wall', 'dunham-prayer-wall'), 'manage_options', 'dunham-prayer-wall-settings', array($this, 'settings_page'));
		add_submenu_page('edit.php?post_type=prayerrequest', __('Export Contacts', 'dunham-prayer-wall'), __('Export Contacts', 'dunham-prayer-wall'), 'list_users', 'dunham-prayer-wall-export', array($this, 'export_page'));
	}

	/**
	 * Export page content
	 * @since 1.0.0
	 */
	public function export_page() {
		if (!current_user_can('list_users')) {
			wp_die(__('You do not have sufficient permissions to access this page.', 'dunham-prayer-wall'));
		}

?>
<div class="wrap">
	<h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    <p>Export a CSV file containing names and email addresses for people who have requested prayer or commented on prayer requests.</p>
    <form action="<?php echo add_query_arg(array('export' => 'csv')); ?>" method="POST">
        <table class="widefat striped">
            <tr>
                <th><label for="input_start_date">Start Date</label></th>
                <td><input required name="start_date" id="input_start_date" type="date" value="<?php echo current_time('Y-m-d'); ?>"></td>
            </tr>
            <tr>
                <th><label for="input_end_date">End Date</label></th>
                <td><input required name="end_date" id="input_end_date" type="date" value="<?php echo current_time('Y-m-d'); ?>"></td>
            </tr>
        </table>
        <input type="submit" class="button" id="input_export_submit" value="Generate CSV File">
    </form>
</div>
<?php
	}

	/**
	 * Generate and download CSV file of contact details
	 * @since 1.0.0
	 */
	public function export_csv() {
		// Check for required variables
		if (empty($_POST['start_date']) || empty($_POST['end_date'])) {
			wp_die('Both start and end date are required.');
		}
		$from = new DateTime($_POST['start_date']);
		$to = new DateTime($_POST['end_date']);
		$args = array(
				'posts_per_page' => -1,
				'post_type' => 'prayerrequest',
				'date_query' => array(
						array(
								'after' => array(
										'year' => $from->format('Y'),
										'month' => $from->format('m'),
										'day' => $from->format('d'),
								),
								'before' => array(
										'year' => $to->format('Y'),
										'month' => $to->format('m'),
										'day' => $to->format('d'),
								),
								'inclusive' => true,
						),
				),
		);
		$requests = get_posts($args);
		$comments = get_comments($args);

		if (count($requests) == 0 && count($comments) == 0) {
			wp_die('No data to export.');
		}

		$export_fields = array(
				'Name',
				'Email',
				'Source',
		);

		$csv = array(
				$export_fields,
		);

		foreach ($requests as $request) {
			$csv[] = array(
					'Name' => get_post_meta($request->ID, 'name', true),
					'Email' => get_post_meta($request->ID, 'email', true),
					'Source' => 'Prayer Request',
			);
		}

		foreach ($comments as $comment) {
			$csv[] = array(
					'Name' => $comment->comment_author,
					'Email' => $comment->comment_author_email,
					'Source' => 'Comment',
			);
		}

		$fp = fopen('php://output', 'w+');
		header('Content-type: application/octet-stream');
		header('Content-disposition: attachment; filename="prayer-contacts.csv"');
		foreach ($csv as $line) {
			fputcsv($fp, $line);
		}
		fclose($fp);
		exit;
	}

	/**
	 * Settings page content
	 * @since 1.0.0
	 */
	public function settings_page() {
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have sufficient permissions to access this page.', 'dunham-prayer-wall'));
		}

		echo '<div class="wrap">';
		echo '<h1>'.esc_html(get_admin_page_title()).'</h1>';
		echo '<form action="options.php" method="post">';
		do_settings_sections('dunham-prayer-wall-settings');
		settings_fields('dunham-prayer-wall-settings');
		echo "<p class='submit'>";
		submit_button(__('Save Settings', 'dunham-prayer-wall'), 'primary', 'submit', false);
		echo "</p>";
		echo '</form>';
		echo '</div>';
	}

	/**
	 * Get details of plugin setting fields
	 * @return array
	 * @since 1.0.0
	 */
	private static function get_settings_config() {
		return array(
				array(
						'key' => 'dunham-prayer-wall-general-settings',
						'title' => __('General Settings', 'dunham-prayer-wall'),
						'page' => 'dunham-prayer-wall-settings',
						'wp_option' => 'dunham-prayer-wall-settings',
						'callback' => false,
						'fields' => array(
								array(
										'key' => 'dunham-prayer-wall-settings-approval-type',
										'title' => __('Prayer Request Approval Method', 'dunham-prayer-wall'),
										'type' => 'radio',
										'args' => array(),
										'choices' => array(
												'manual' => 'Manual Approval',
												'auto' => 'Automatic Approval (not recommended)',
										),
										'register_settings_args' => array(
												'type' => 'string',
												'sanitize_callback' => 'sanitize_text_field',
												'default' => 'manual',
										),
								),
								array(
										'key' => 'dunham-prayer-wall-settings-comments-blurb',
										'title' => __('Comment Note', 'dunham-prayer-wall'),
										'type' => 'text',
										'instructions' => __('Text displayed above the comment form.', 'dunham-prayer-wall'),
										'args' => array(
												'class' => 'large-text',
										),
										'register_settings_args' => array(
												'type' => 'string',
												'sanitize_callback' => 'sanitize_text_field',
												'default' => __('By commenting on this prayer request you agree to be added to our mailing list. Your personal details are confidential and will not be displayed or shared.', 'dunham-prayer-wall'),
										),
								),
						),
				),
				array(
						'key' => 'dunham-prayer-wall-display-settings',
						'title' => __('Display Settings', 'dunham-prayer-wall'),
						'page' => 'dunham-prayer-wall-settings',
						'wp_option' => 'dunham-prayer-wall-settings',
						'callback' => false,
						'fields' => array(
								array(
										'key' => 'dunham-prayer-wall-settings-wall-location',
										'title' => __('Prayer Wall Location', 'dunham-prayer-wall'),
										'type' => 'select',
										/* translators: %s: shortcode that can be inserted into the page */
										'instructions' => sprintf(__('The prayer wall can either be accessed via the default WordPress archive or a page containing the %s shortcode. If the page you want use is not in the list below, please check that it is published and includes the shortcode in the page content.', 'dunham-prayer-wall'), '<code>[dunham_prayer_wall]</code>'),
										'args' => array(),
										'choices' => self::get_wall_location_choices(),
										'register_settings_args' => array(
												'type' => 'string',
												'sanitize_callback' => 'sanitize_text_field',
												'default' => 'archive',
										),
								),
								array(
										'key' => 'dunham-prayer-wall-settings-colour1',
										'title' => __('Colour 1', 'dunham-prayer-wall'),
										'type' => 'colourpicker',
										'args' => array(),
										'register_settings_args' => array(
												'type' => 'string',
												'sanitize_callback' => 'sanitize_hex_color',
												'default' => '#FFFFFF',
										),
								),
								array(
										'key' => 'dunham-prayer-wall-settings-colour2',
										'title' => __('Colour 2', 'dunham-prayer-wall'),
										'type' => 'colourpicker',
										'args' => array(),
										'register_settings_args' => array(
												'type' => 'string',
												'sanitize_callback' => 'sanitize_hex_color',
												'default' => '#888888',
										),
								),
								array(
										'key' => 'dunham-prayer-wall-settings-colour3',
										'title' => __('Colour 3', 'dunham-prayer-wall'),
										'type' => 'colourpicker',
										'args' => array(),
										'register_settings_args' => array(
												'type' => 'string',
												'sanitize_callback' => 'sanitize_hex_color',
												'default' => '#000000',
										),
								),
						),
				),
				array(
						'key' => 'dunham-prayer-wall-email-settings',
						'title' => __('Notification Settings', 'dunham-prayer-wall'),
						'page' => 'dunham-prayer-wall-settings',
						'wp_option' => 'dunham-prayer-wall-settings',
						'callback' => false,
						'fields' => array(
								array(
										'key' => 'dunham-prayer-wall-settings-request-approved-email-subject',
										'title' => __('Request Approved Email Subject', 'dunham-prayer-wall'),
										'type' => 'text',
										'instructions' => __('This is the subject of the email sent to notify a user that their prayer request has been approved.', 'dunham-prayer-wall'),
										'args' => array(
												'class' => 'large-text',
										),
										'register_settings_args' => array(
												'type' => 'string',
												'sanitize_callback' => 'sanitize_text_field',
												'default' => 'Your Prayer Request has been Approved',
										),
								),
								array(
										'key' => 'dunham-prayer-wall-settings-request-approved-email-content',
										'title' => __('Request Approved Email Content', 'dunham-prayer-wall'),
										'type' => 'wp-editor',
										'instructions' => __('This is the body of the email sent to notify a user that their prayer request has been approved. Available variables: <code>{{name}}</code> (recipient name); <code>{{request_url}}</code> (link to prayer request)', 'dunham-prayer-wall'),
										'args' => array(
												'class' => 'large-text',
										),
										'register_settings_args' => array(
												'type' => 'string',
												'sanitize_callback' => 'wp_kses_post',
												'default' => __('Hi {{name}},

I just wanted to let you know that your prayer request has been approved.

You can view it here: {{request_url}}.

Our prayer team will be praying with you.

May the Lord grant you the desires of your heart!', 'dunham-prayer-wall'),
										),
								),
								array(
										'key' => 'dunham-prayer-wall-settings-prayer-received-email-subject',
										'title' => __('Prayer Received Email Subject', 'dunham-prayer-wall'),
										'type' => 'text',
										'instructions' => __('This is the subject of the email sent to notify a user that someone "official" has prayed for their request.', 'dunham-prayer-wall'),
										'args' => array(
												'class' => 'large-text',
										),
										'register_settings_args' => array(
												'type' => 'string',
												'sanitize_callback' => 'sanitize_text_field',
												'default' => 'We have been praying for you',
										),
								),
								array(
										'key' => 'dunham-prayer-wall-settings-prayer-received-email-content',
										'title' => __('Prayer Received Email Content', 'dunham-prayer-wall'),
										'type' => 'wp-editor',
										'instructions' => __('This is the body of the email sent to notify a user that someone "official" has prayed for their request. Available variables: <code>{{name}}</code> (recipient name); <code>{{request_url}}</code> (link to prayer request); <code>{{prayer_name}}</code> (name of the person who prayed)', 'dunham-prayer-wall'),
										'args' => array(
												'class' => 'large-text',
										),
										'register_settings_args' => array(
												'type' => 'string',
												'sanitize_callback' => 'wp_kses_post',
												'default' => __('Hi {{name}},

I just wanted to let you know that {{prayer_name}} has just prayed for your prayer request.

We are believing that the Lord\'s answer will come just at the right time!

You can view your request here: {{request_url}}.', 'dunham-prayer-wall'),
										),
								),
								array(
										'key' => 'dunham-prayer-wall-settings-send-follow-up-after',
										'title' => __('Send Follow Up After (Days)', 'dunham-prayer-wall'),
										'type' => 'number',
										'instructions' => __('How many days after the request is published should the follow up summary email be sent? Set to zero to disable this notification. Note that changes to this setting will only affect requests published after the change is saved.', 'dunham-prayer-wall'),
										'register_settings_args' => array(
												'type' => 'integer',
												'sanitize_callback' => 'absint',
												'default' => 14,
										),
								),
								array(
										'key' => 'dunham-prayer-wall-settings-follow-up-email-subject',
										'title' => __('Follow Up Email Subject', 'dunham-prayer-wall'),
										'type' => 'text',
										'instructions' => __('This is the subject of the email sent to provide a summary of the activity on the request to the submitter. It will be sent based on the above schedule.', 'dunham-prayer-wall'),
										'args' => array(
												'class' => 'large-text',
										),
										'register_settings_args' => array(
												'type' => 'string',
												'sanitize_callback' => 'sanitize_text_field',
												'default' => 'Prayer Request Update',
										),
								),
								array(
										'key' => 'dunham-prayer-wall-settings-follow-up-email-content',
										'title' => __('Follow Up Email Content', 'dunham-prayer-wall'),
										'type' => 'wp-editor',
										'instructions' => __('This is the body of the email sent to provide a summary of the activity on the request to the submitter. Available variables: <code>{{name}}</code> (recipient name); <code>{{prayer_count}}</code> (number of prayers); <code>{{comment_count}}</code> (number of comments); <code>{{request_url}}</code> (link to prayer request)', 'dunham-prayer-wall'),
										'args' => array(
												'class' => 'large-text',
										),
										'register_settings_args' => array(
												'type' => 'string',
												'sanitize_callback' => 'wp_kses_post',
												'default' => __('Hi {{name}},

Your prayer request has been prayed for {{prayer_count}} times, and there have been {{comment_count}} comments.

You can see all comments on your request here: {{request_url}}.

Our prayer team will continue praying with you.', 'dunham-prayer-wall'),
										),
								),
						),
				),
		);
	}

	/**
	 * Get the options for where to display the wall
	 * @since 1.0.0
	 */
	private static function get_wall_location_choices() {
		$choices = array(
				'archive' => 'WordPress Archive ('.get_post_type_archive_link('prayerrequest').')',
		);
		$args = array(
				'post_type' => 'page',
				'posts_per_page' => -1,
				's' => '[dunham_prayer_wall]',
		);
		$pages = get_posts($args);
		foreach ($pages as $page) {
			$choices[$page->ID] = $page->post_title.' ('.get_the_permalink($page).')';
		}
		return $choices;
	}

	/**
	 * Register the plugin settings
	 * @since 1.0.0
	 */
	public function register_settings() {
		$section_cfg = $this->get_settings_config();

		foreach ($section_cfg as $section) {
			add_settings_section($section['key'], $section['title'], $section['callback'], $section['page']);
			foreach ($section['fields'] as $section_field_value) {
				register_setting($section['wp_option'], $section_field_value['key'], $section_field_value['register_settings_args']);
				add_settings_field($section_field_value['key'], $section_field_value['title'], array($this, 'render_section_fields'), $section['page'], $section['key'], $section_field_value);
			}
		}
	}

	/**
	 * The callback to render settings fields
	 * @since 1.0.0
	 */
	function render_section_fields($param) {
		$value = maybe_unserialize(get_option($param['key']));
		if (empty($value)) {
			$value = $param['register_settings_args']['default'];
		}

		$prop = '';
		foreach ($param['args'] as $prop_key => $prop_value) {
			$prop .= $prop_key.'="'.$prop_value.'" ';
		}

		if (!empty($param['instructions'])) {
			echo '<p>'.$param['instructions'].'</p>';
		}

		switch ($param['type']) {
			case 'checkboxes':
				foreach ($param['choices'] as $val => $text) {
					$selected = is_array($value) ? in_array($val, $value) : $val == $value;
					echo "<label><input type='checkbox' name='{$param['key']}[]' id='{$param['key']}' value='$val' ".checked(true, $selected, false)." $prop> $text</label><br>";
				}
				break;
			case 'radio':
				foreach ($param['choices'] as $val => $text) {
					$selected = is_array($value) ? in_array($val, $value) : $val == $value;
					echo "<label><input type='radio' name='{$param['key']}' id='{$param['key']}' value='$val' ".checked(true, $selected, false)." $prop> $text</label><br>";
				}
				break;
			case 'select':
				echo '<select name="'.$param['key'].'" id="'.$param['key'].'" '.$prop.'>';
				foreach ($param['choices'] as $val => $text) {
					echo '<option value="'.$val.'" '.selected($val, $value, false).'>'.$text.'</option>';
				}
				echo '</select>';
				break;
			case 'textarea':
				echo "<textarea name='{$param['key']}' id='{$param['key']}' {$prop}>{$value}</textarea>";
				break;
			case 'wp-editor':
				wp_editor($value, $param['key'], array_merge($param['args'], array('textarea_name' => $param['key'])));
				break;
			case 'colourpicker':
				echo "<input type='text' name='{$param['key']}' id='{$param['key']}' value='{$value}' {$prop} data-colourpicker>";
				break;
			default:
				echo "<input type='{$param['type']}' name='{$param['key']}' id='{$param['key']}' value='{$value}' {$prop}>";
				break;
		}
	}

	/**
	 * Get the value for a specific setting
	 * @param string $setting_name
	 * @return mixed|boolean Option value if it exists, otherwise false
	 * @since 1.0.0
	 */
	public static function get_setting($setting_name) {
		if (false === strpos($setting_name, 'dunham-prayer-wall-settings')) {
			$setting_name = 'dunham-prayer-wall-settings-'.$setting_name;
		}
		$setting = get_option($setting_name, false);
		if (false === $setting) {
			$settings_config = self::get_settings_config();
			foreach ($settings_config as $section) {
				foreach ($section['fields'] as $field) {
					if ($setting_name == $field['key']) {
						$setting = $field['register_settings_args']['default'] ?: $setting;
						break(2);
					}
				}
			}
		}
		return $setting;
	}

	/**
	 * Set email content type to HTML
	 * @param string $content_type
	 * @return string
	 * @since 1.0.0
	 */
	public function wp_mail_content_type($content_type) {
		return 'text/html';
	}

	/**
	 * Send email to submitter when their request is approved
	 * @param string $new_status
	 * @param string $old_status
	 * @param WP_Post $post
	 * @since 1.0.0
	 */
	public function notify_submitter($new_status, $old_status, $post) {
		if ('prayerrequest' == get_post_type($post) && 'draft' == $old_status && 'publish' == $new_status) {
			$author_email = get_post_meta($post->ID, 'email', true);
			$author_name = get_post_meta($post->ID, 'name', true);
			$message = $this->get_setting('request-approved-email-content');
			$message = str_replace(array('{{name}}', '{{request_url}}'), array($author_name, get_the_permalink($post)), $message);
			add_filter('wp_mail_content_type', array($this, 'wp_mail_content_type'));
			wp_mail($author_email, $this->get_setting('request-approved-email-subject'), wpautop($message));
			remove_filter('wp_mail_content_type', array($this, 'wp_mail_content_type'));

			// Schedule a summary email based on configured schedule
			$days = absint($this->get_setting('send-follow-up-after'));
			if ($days > 0) {
				$cron_args = array('request_id' => $post->ID);
				if (!wp_next_scheduled('dunham_prayer_wall_send_summary_email', $cron_args)) {
					$schedule = strtotime('+'.$days.' days', current_time('timestamp', true));
					wp_schedule_single_event($schedule, 'dunham_prayer_wall_send_summary_email', $cron_args);
				}
			}
		}
	}

	/**
	 * Send summary email to prayer requester. Triggered by a scheduled event - this function shouldn't ever be called manually.
	 * @param integer $request_id
	 * @since 1.0.0
	 */
	public function send_summary_email($request_id) {
		$post = get_post($request_id);
		if ($post instanceof WP_Post && 'prayerrequest' == get_post_type($post) && 'publish' == get_post_status($post)) {
			$comments = get_comment_count($post->ID);
			$author_email = get_post_meta($post->ID, 'email', true);
			$author_name = get_post_meta($post->ID, 'name', true);
			$message = $this->get_setting('follow-up-email-content');
			$message = str_replace(array('{{name}}', '{{request_url}}', '{{prayer_count}}', '{{comment_count}}'), array($author_name, get_the_permalink($post), (int)get_post_meta($post->ID, '_prayers', true), $comments['approved']), $message);
			add_filter('wp_mail_content_type', array($this, 'wp_mail_content_type'));
			wp_mail($author_email, $this->get_setting('follow-up-email-subject'), wpautop($message));
			remove_filter('wp_mail_content_type', array($this, 'wp_mail_content_type'));
		}
	}

	/**
	 * AJAX handler for prayer request submission
	 * @since 1.0.0
	 */
	public function ajax_submit_request() {
		if (!wp_doing_ajax() || 'POST' !== $_SERVER['REQUEST_METHOD']) {
			wp_send_json_error(null, 405);
		}
		/**
		 * @var string $name
		 * @var string $email
		 * @var string $location
		 * @var integer $request_type
		 * @var string $subject
		 * @var string $request_details
		 */
		extract($_POST);
		if (empty($name) || empty($email) || empty($location) || empty($request_type) || empty($request_details)) {
			wp_send_json_error(__('All fields are required unless otherwise indicated.', 'dunham-prayer-wall'), 400);
		}
		if (!is_email($email)) {
			wp_send_json_error(__('Please enter a valid email address.', 'dunham-prayer-wall'), 400);
		}
		if (!term_exists((int)$request_type, 'prayercategory')) {
			wp_send_json_error(__('Invalid request type.', 'dunham-prayer-wall'), 400);
		}
		$post = array(
				'post_type' => 'prayerrequest',
				'post_title' => $subject ?: 'Prayer Request from "'.dunham_prayer_wall_privatise_author_name($name).'"',
				'post_content' => $request_details,
				'post_status' => 'auto' === $this->get_setting('dunham-prayer-wall-settings-approval-type') ? 'publish' : 'draft',
				'meta_input' => array(
						'name' => $name,
						'email' => $email,
						'location' => $location,
				),
		);
		$post_id = wp_insert_post($post, true);
		if (is_wp_error($post_id)) {
			wp_send_json_error($post_id->get_error_message(), 500);
		}

		// Have to use wp_set_object_terms() rather than passing it through the tax_input parameter to wp_insert_post() otherwise terms won't be assigned if user doesn't have admin access
		wp_set_object_terms($post_id, array((int)$request_type), 'prayercategory');

		wp_send_json(__('Success', 'dunham-prayer-wall'));
	}

	/**
	 * AJAX handler when indicating you've prayed for a request
	 * @since 1.0.0
	 */
	public function ajax_pray() {
		$request_id = (int)$_POST['id'];
		$request = get_post($request_id);
		if (!$request instanceof WP_Post || 'prayerrequest' != $request->post_type) {
			wp_send_json_error(__('Invalid request', 'dunham-prayer-wall'));
		}

		if (!session_id()) {
			session_start();
		}
		$key = 'dpwrp'; // Dunham Prayer Wall Recent Prayers ;-)
		$recent_prayers = $_SESSION[$key] ?: array();
		foreach ($recent_prayers as $pid => $p_time) {
			if (current_time('timestamp') - $p_time > HOUR_IN_SECONDS) {
				unset($recent_prayers[$pid]);
			}
		}

		if (isset($recent_prayers[$request->ID])) {
			wp_send_json_error(__('Thank you but you’ve already prayed for this person recently. There are lots more who need your prayer though...', 'dunham-prayer-wall'));
		}

		$prayer_count = (int)get_post_meta($request->ID, '_prayers', true);
		update_post_meta($request->ID, '_prayers', ++$prayer_count);
		$recent_prayers[$request->ID] = current_time('timestamp');
		$_SESSION[$key] = $recent_prayers;

		if (is_user_logged_in()) {
			$current_user = wp_get_current_user();
			if (dunham_prayer_wall_is_official_prayer($current_user)) { // If someone official prayed, send an email to the original author
				$author_email = get_post_meta($request->ID, 'email', true);
				$author_name = get_post_meta($request->ID, 'name', true);
				$message = $this->get_setting('prayer-received-email-content');
				$message = str_replace(array('{{name}}', '{{request_url}}', '{{prayer_name}}'), array($author_name, get_the_permalink($request), $current_user->first_name), $message);
				add_filter('wp_mail_content_type', array($this, 'wp_mail_content_type'));
				wp_mail($author_email, $this->get_setting('prayer-received-email-subject'), wpautop($message));
				remove_filter('wp_mail_content_type', array($this, 'wp_mail_content_type'));
			}
		}

		wp_send_json_success(array('count' => $prayer_count));
	}

	/**
	 * Count the total number of prayers
	 * @since 1.0.0
	 */
	public static function count_prayers() {
		global $wpdb;
		$sql = 'SELECT SUM(CAST(pm.meta_value AS UNSIGNED)) FROM '.$wpdb->postmeta.' pm INNER JOIN '.$wpdb->posts.' p ON (p.ID = pm.post_id AND pm.meta_key = "_prayers") WHERE p.post_type = "prayerrequest"';
		$count = $wpdb->get_var($sql);
		return $count;
	}
}
