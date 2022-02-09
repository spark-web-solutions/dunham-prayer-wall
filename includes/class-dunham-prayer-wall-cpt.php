<?php
/**
 * The file that defines the CPT helper class
 *
 * A class definition that simplifies creating custom post types
 *
 * @link       https://dunhamandcompany.com/
 * @since      1.0.0
 *
 * @package    Dunham_Prayer_Wall
 * @subpackage Dunham_Prayer_Wall/includes
 */

/**
 * The CPT helper class.
 *
 * This is a helper class to simplify the creation of custom post types
 *
 * @since      1.0.0
 * @package    Dunham_Prayer_Wall
 * @subpackage Dunham_Prayer_Wall/includes
 * @author     Dunham + Company <plugins@sparkweb.com.au>
 */
class Dunham_Prayer_Wall_Cpt {
    public function __construct($singular, $plural, array $args = array(), $slug = '') {
        $this->plural = $plural;
        $this->singular = $singular;
        $this->slug = !empty($slug) ? $slug : str_replace(' ', '', strtolower($singular));
        $this->args = $args;
        add_action('init', array($this, 'register'), 1);
        add_filter('post_updated_messages', array($this, 'messages'));
    }

    public function register() {
        $labels = array(
                'name' => _x(ucfirst($this->plural), 'post type general name'),
                'singular_name' => _x(ucfirst($this->singular), 'post type singular name'),
                'add_new' => _x('Add New', ucfirst($this->singular)),
                'add_new_item' => __('Add New ' . ucfirst($this->singular)),
                'edit_item' => __('Edit ' . ucfirst($this->singular)),
                'new_item' => __('New ' . ucfirst($this->singular)),
                'all_items' => __('All ' . ucfirst($this->plural)),
                'view_item' => __('View ' . ucfirst($this->singular)),
                'search_items' => __('Search ' . ucfirst($this->plural)),
                'not_found' => __('No ' . ucfirst($this->plural) . ' found'),
                'not_found_in_trash' => __('No ' . ucfirst($this->plural) . ' found in the Trash'),
                'parent_item_colon' => '',
                'menu_name' => ucfirst($this->plural),
        );

        $default_args = array(
                'labels' => $labels,
                'description' => 'Holds our ' . ucfirst($this->singular) . ' posts',
                'public' => true,
                'menu_position' => 20,
                'supports' => array(
                        'title',
                ),
                'has_archive' => true,
                'hierarchical' => true,
                'show_in_rest' => true,
        );

        $args = array_replace_recursive($default_args, $this->args);

        register_post_type($this->slug, $args);
    }

    // Set Messages
    public function messages($messages) {
        global $post, $post_ID;
        $cpttype = strtolower($this->singular);
        $messages[$cpttype] = array(
                0 => '', // Unused. Messages start at index 1.
                1 => sprintf(__(ucfirst($this->singular) . ' Post updated.', 'your_text_domain'), esc_url(get_permalink($post_ID))),
                2 => __(ucfirst($this->singular) . ' updated.', 'your_text_domain'),
                3 => __(ucfirst($this->singular) . ' deleted.', 'your_text_domain'),
                4 => __(ucfirst($this->singular) . ' Post updated.', 'your_text_domain') ,
                /* translators: %s: date and time of the revision */
                5 => isset($_GET['revision']) ? sprintf(__(ucfirst($this->singular) . ' Post restored to revision from %s', 'your_text_domain'), wp_post_revision_title((int)$_GET['revision'], false)) : false,
                6 => sprintf(__(ucfirst($this->singular) . ' Post published. <a href="%s">View ' . ucfirst($this->singular) . ' Post</a>', 'your_text_domain'), esc_url(get_permalink($post_ID))),
                7 => __(ucfirst($this->singular) . ' Post saved.', 'your_text_domain'),
                8 => sprintf(__(ucfirst($this->singular) . ' Post submitted. <a target="_blank" href="%s">Preview ' . ucfirst($this->singular) . ' Post</a>', 'your_text_domain'), esc_url(add_query_arg('preview', 'true', get_permalink($post_ID)))),
                9 => sprintf(__(ucfirst($this->singular) . ' Post scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview ' . ucfirst($this->singular) . ' Post</a>', 'your_text_domain'),
                // translators: Publish box date format, see http://php.net/date
                date_i18n(__('M j, Y @ G:i'), strtotime($post->post_date)), esc_url(get_permalink($post_ID))),
                10 => sprintf(__(ucfirst($this->singular) . ' Post draft updated. <a target="_blank" href="%s">Preview ' . ucfirst($this->singular) . ' Post</a>', 'your_text_domain'), esc_url(add_query_arg('preview', 'true', get_permalink($post_ID))))
        );
        return $messages;
    }
}
