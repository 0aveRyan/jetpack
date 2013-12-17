<?php

/**
 * A last try to show posts, in case the Featured Content plugin returns no IDs.
 *
 * @param array $featured_ids
 * @return array
 */
function twentyfourteen_featured_content_post_ids( $featured_ids ) {
	if ( empty( $featured_ids ) ) {
		$featured_ids = array_slice( get_option( 'sticky_posts', array() ), 0, 6 );
	}

	return $featured_ids;
}
add_action( 'featured_content_post_ids', 'twentyfourteen_featured_content_post_ids' );

/**
 * Sets a default tag of 'featured' for Featured Content.
 *
 * @param array $settings
 * @return array
 */
function twentyfourteen_featured_content_default_settings( $settings ) {
	$settings['tag-name'] = 'featured';

	return $settings;
}
add_action( 'featured_content_default_settings', 'twentyfourteen_featured_content_default_settings' );

/**
 * Removes post flair markup from post content if we're not in the loop and
 * it's a formatted post.
 *
 * @param string $content
 * @return string
 */
function twentyfourteen_mute_content_filters( $content ) {
	$formats = get_theme_support( 'post-formats' );
	if ( ! in_the_loop() && has_post_format( $formats[0] ) ) {
		$parts = explode( '<div id="jp-post-flair"', $content );
		$content = $parts[0];
	}
	return $content;
}
add_filter( 'the_content', 'twentyfourteen_mute_content_filters', 9999 );
