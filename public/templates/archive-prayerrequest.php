<?php
/**
 * Custom template for displaying the main prayer requests archive.
 * Override this by copying it to your theme and making the desired changes.
 *
 * @link https://sparkweb.com.au
 * @since 1.0.0
 *
 * @package	Dunham_Prayer_Wall
 * @subpackage Dunham_Prayer_Wall/public/templates
 */

get_header();
$prayer_count = Dunham_Prayer_Wall_Admin::count_prayers();
?>
<style>
:root {
<?php
for ($c =1; $c <= 3; $c++) {
	$colour = Dunham_Prayer_Wall_Admin::get_setting('colour'.$c);
	echo '--prayer-request-bg-colour-'.$c.': '.$colour.';'."\n";
	echo '--prayer-request-text-colour-'.$c.': '.dunham_prayer_wall_get_contrast_colour($colour).';'."\n";
}
?>
}
</style>
<div class="<?php echo apply_filters('dunham_prayer_wall_archive_wrapper_class', 'prayer-wrapper'); ?>">
    <p class="total-prayer-counter text-right"><?php printf(_n('%s prayer prayed', '%s prayers prayed', $prayer_count, 'dunham-prayer-wall'), number_format($prayer_count)); ?></p>
    <h1><?php post_type_archive_title(); ?></h1>
    <div class="<?php echo apply_filters('dunham_prayer_wall_archive_grid_class', 'prayer-grid'); ?>">
		<div class="<?php echo apply_filters('dunham_prayer_wall_archive_grid_item_class', 'prayer-grid-item request request-1', null); ?>">
			<div class="<?php echo apply_filters('dunham_prayer_wall_archive_grid_item_content_class', 'content'); ?>">
				<p><?php _e('We\'d love to pray for you.', 'dunham-prayer-wall'); ?></p>
				<p><a class="button dunham-submit-prayer-modal" href="#"><?php _e('Submit Prayer Request', 'dunham-prayer-wall'); ?></a></p>
				<div class="dunham_prayer_modal" id="dunham_submit_prayer_modal">
					<a href="#" class="veil"></a>
					<div class="dunham_prayer_modal_content" id="dunham_submit_prayer_modal_content">
						<a href="#" class="dunham_prayer_modal_close">✖</a>
						<h2><?php _e('Make your Prayer Request', 'dunham-prayer-wall'); ?></h2>
						<p><?php _e('We\'d love to pray for you! Simply fill in the form below and our prayer team will join you in bringing your request to the Lord.', 'dunham-prayer-wall'); ?></p>
						<?php $categories = Dunham_Prayer_Wall_Public::get_categories();?>
						<form action="" method="post" id="dunham_prayer_wall_submit_request_form" class="prayer-submit-form">
							<p><?php _e('Your personal details are confidential and will not be displayed or shared.', 'dunham-prayer-wall'); ?></p>
							<label for="dunham_prayer_wall_submit_request_name"><?php _e('Your Name', 'dunham-prayer-wall'); ?></label>
							<input type="text" name="name" id="dunham_prayer_wall_submit_request_name" required>
							<label for="dunham_prayer_wall_submit_request_email"><?php _e('Your Email', 'dunham-prayer-wall'); ?></label>
							<input type="email" name="email" id="dunham_prayer_wall_submit_request_email" required>
							<label for="dunham_prayer_wall_submit_request_location"><?php _e('Your Location', 'dunham-prayer-wall'); ?></label>
							<input type="text" name="location" id="dunham_prayer_wall_submit_request_location" required>
							<label for="dunham_prayer_wall_submit_request_type"><?php _e('Request Type', 'dunham-prayer-wall'); ?></label>

								<select name="request_type" id="dunham_prayer_wall_submit_request_type" required>
									<option value=""><?php _e('Please Select', 'dunham-prayer-wall'); ?></option>
<?php
foreach ($categories as $category) {
?>
									<option value="<?php echo $category->term_id; ?>"><?php echo $category->name; ?></option>
<?php
}
?>
								</select>

							<label for="dunham_prayer_wall_submit_request_subject"><?php _e('Request Subject (optional)', 'dunham-prayer-wall'); ?></label>
							<input type="text" name="subject" id="dunham_prayer_wall_submit_request_subject">
							<label for="dunham_prayer_wall_submit_request_details"><?php _e('Prayer Request Details', 'dunham-prayer-wall'); ?></label>
							<textarea name="request_details" id="dunham_prayer_wall_submit_request_details" rows="10" cols="75" required></textarea>
							<input type="submit" class="submit button"><span id="dunham_prayer_wall_submit_request_messages"></span>
						</form>
					</div>
				</div>
			</div>
		</div>
<?php
if (have_posts()) {
    while (have_posts()) {
        /**
         * @var WP_Post $post
         */
        the_post();
		$style = rand(1, 3);
?>
		<div class="<?php echo apply_filters('dunham_prayer_wall_archive_grid_item_class', 'prayer-grid-item request request-'.$style, $post); ?>">
			<div class="<?php echo apply_filters('dunham_prayer_wall_archive_grid_item_content_class', 'content'); ?>">
<?php
		$content = dunham_prayer_wall_post_extract();
		$link = get_the_permalink($post->ID);
		$author = dunham_prayer_wall_privatise_author_name(get_post_meta($post->ID, 'name', true), get_post_meta($post->ID, 'location', true));
		$prayer_count = (int)get_post_meta($post->ID, '_prayers', true);
		$args = array(
				'post_id' => $post->ID,
				'type' => 'comment',
				'status' => 'approve',
		);
		$comments = get_comments($args);
		$comment_count = count($comments);
		if (has_post_thumbnail()) {
?>
				<a href="<?php echo $link; ?>"><?php echo wp_get_attachment_image(get_post_thumbnail_id(), 'medium'); ?></a>
<?php
		}
?>
				<a class="prayer-counter" href="#" data-request-id="<?php echo esc_attr($post->ID); ?>" title="<?php echo esc_attr(__("Let us know you've prayed!", 'dunham-prayer-wall')); ?>"><span id="prayer_count_<?php echo $post->ID; ?>"><?php echo number_format_i18n($prayer_count); ?></span> 🙏</a>
				<a class="comment-counter" href="<?php echo $link; ?>" title="<?php esc_attr(__('Submit a response to this request', 'dunham-prayer-wall')); ?>"><span id="comment_count_<?php echo $post->ID; ?>"><?php echo number_format_i18n($comment_count); ?></span> 📝</a>
		    	<h2 class="h4"><a href="<?php echo $link; ?>"><?php echo get_the_title(); ?></a></h2>
		    	<?php echo wpautop($content); ?>
		    	<p class="text-right author">- <?php echo $author; ?></p>
<?php
		if ('...' == substr(trim($content), -3)) {
?>
    			<div class="row collapse">
					<a href="<?php echo $link; ?>"><?php _e('Read More', 'dunham-prayer-wall'); ?></a>
				</div>
<?php
		}
?>
			</div>
		</div>
<?php
    }
?>
    </div>
<?php
}
?>
</div>
<?php
get_footer();
