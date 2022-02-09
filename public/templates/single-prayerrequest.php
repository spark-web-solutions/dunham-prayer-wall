<?php
/**
 * Custom template for displaying a single prayer request.
 * Override this by copying it to your theme and making the desired changes.
 *
 * @link https://sparkweb.com.au
 * @since 1.0.0
 *
 * @package	Dunham_Prayer_Wall
 * @subpackage Dunham_Prayer_Wall/public/templates
 */

get_header();

while (have_posts()) {
	the_post();
	/**
	 * @var WP_Post $post
	 */
	$author = dunham_prayer_wall_privatise_author_name(get_post_meta($post->ID, 'name', true), get_post_meta($post->ID, 'location', true));
	$prayer_count = (int)get_post_meta($post->ID, '_prayers', true);
?>
<div class="<?php echo apply_filters('dunham_prayer_wall_single_wrapper_class', 'single-prayer-wrapper'); ?>">
	<article <?php post_class(apply_filters('dunham_prayer_wall_single_post_class', 'single-prayer')); ?>>
		<a class="prayer-counter" href="#" data-request-id="<?php echo esc_attr($post->ID); ?>" title="<?php echo esc_attr(__("Let us know you've prayed", 'dunham-prayer-wall')); ?>"><span id="prayer_count_<?php echo $post->ID; ?>"><?php echo number_format_i18n($prayer_count); ?></span> 🙏</a>
		<h1><?php echo get_the_title($post); ?></h1>
		<p><?php printf(__('Added %s ago', 'dunham-prayer-wall'), human_time_diff(get_the_time('U'), current_time('timestamp'))); ?></p>
		<?php the_content(); ?>
		<p class="text-right">- <?php echo $author; ?></p>
<?php
	if (has_post_thumbnail()) {
?>
		<p class="text-center"><img src="<?php echo wp_get_attachment_image(get_post_thumbnail_id(), 'large'); ?>" alt="" width="600" height="600"></p>
<?php
	}
?>
	</article>
	<div id="comments" class="<?php echo apply_filters('dunham_prayer_wall_single_comments_class', 'single-prayer-comments'); ?>">
<?php
	$args = array(
			'post_id' => $post->ID,
			'type' => 'comment',
			'status' => 'approve',
	);
	$comments = get_comments($args);
	$comment_count = count($comments);
	$form_args = array(
			'title_reply' => __('Be the first to respond!', 'dunham-prayer-wall'),
			'comment_notes_before' => Dunham_Prayer_Wall_Admin::get_setting('comments-blurb'),
	);
	if ($comment_count > 0) {
		$form_args['title_reply'] = __('Add your thoughts...', 'dunham-prayer-wall');
		echo '<h2>'.sprintf(_n('%s response', '%s responses', $comment_count), number_format_i18n($comment_count)).'</h2>'."\n";
		foreach ($comments as $comment) {
			$comment_author = get_user_by('email', $comment->comment_author_email);
			if ($comment_author instanceof WP_User && dunham_prayer_wall_is_official_prayer($comment_author)) {
				$comment_avatar = get_avatar($comment->comment_author_email);
				$comment_author_name = $comment->comment_author;
			} else {
				$comment_avatar = get_avatar(null); // Display default avatar
				$comment_author_name = dunham_prayer_wall_privatise_author_name($comment->comment_author);
			}
			echo '  <div class="'.apply_filters('dunham_prayer_wall_single_comment_class', 'single-prayer-comment').'" id="comment-'.$comment->comment_ID.'">'."\n";
			echo '	  <div class="text-center commenter">'."\n";
			echo $comment_avatar;
			echo '	  <br>'."\n";
			echo $comment_author_name;
			echo '	  </div>'."\n";
			echo '	  <div class="comments">'."\n";
			echo apply_filters('the_content', $comment->comment_content);
			echo '		  <p class="text-right">'.sprintf(__('%s ago', 'dunham-prayer-wall'), human_time_diff(strtotime($comment->comment_date), current_time('timestamp'))).'</p>'."\n";
			echo '	  </div>'."\n";
			echo '  </div>'."\n";
			echo '<hr>'."\n";
		}
	}
	comment_form($form_args);
?>
		<p class="text-right"><a href="<?php echo get_post_type_archive_link('prayerrequest'); ?>" class="button"><?php _e('Return to Prayer Page', 'dunham-prayer-wall'); ?></a></p>
	</div>
</div>
<?php
}

get_footer();
