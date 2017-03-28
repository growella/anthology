<?php
/**
 * Output functionality for Anthology series.
 *
 * @package Anthology
 * @author  Series
 */

namespace Growella\Anthology\Output;

use Growella\Anthology\Taxonomy as Taxonomy;

/**
 * Shortcode handler for the [series] shortcode.
 *
 * @param array $atts {
 *   Shortcode attributes. Accepts all the same arguments as get_series_query(), plus the $series
 *   attribute, specified below.
 *
 *   @type string $series The series slug to build. Will default to the first series found for the
 *                        current post.
 * }
 * @return string The rendered shortcode contents.
 */
function render_series_shortcode( $atts ) {
	$defaults = array(
		'series' => null,
	);

	/**
	 * Modify default settings for the Anthology shortcode.
	 *
	 * @param array $defaults Default shortcode attributes.
	 */
	$defaults = apply_filters( 'anthology_series_shortcode_defaults', $defaults );

	// Merge the defaults in with user-supplied values.
	$atts   = shortcode_atts( $defaults, $atts, 'series' );
	$series = $atts['series'];
	unset( $atts['series'] );

	// If we weren't given a series, determine which one should be used.
	if ( ! $series ) {
		$default = Taxonomy\get_default_series_for_post( get_the_ID() );
		$series  = $default ? $default->slug : false;
	}

	return render_series_list( $series, $atts );
}
add_shortcode( 'series', __NAMESPACE__ . '\render_series_shortcode' );

/**
 * Build a list of all posts in the given $series.
 *
 * @param string $series The series slug. If the slug is either empty or does not exist, the return
 *                       value from this function will be empty.
 * @param array  $args   Optional. Additional arguments to pass to get_series_query(). Default is
 *                       an empty array. @see Growella\Anthology\Taxonomy/get_series_query() for a
 *                       full list of available arguments.
 * @return string        The rendered list of posts, or an empty string if there are no posts in
 *                       the given series.
 */
function render_series_list( $series, $args = array() ) {
	$posts   = Taxonomy\get_series_query( $series, $args );
	$output  = '';
	$current = get_the_ID();

	if ( $posts->have_posts() ) {
		$output .= '<ol class="anthology-series-post-list">';

		while ( $posts->have_posts() ) {
			$posts->the_post();

			$output .= sprintf(
				'<li class="anthology-series-post %s"><a href="%s" rel="bookmark">%s</a></li>',
				get_the_ID() === $current ? 'current-post' : '',
				esc_url( get_permalink() ),
				esc_html( get_the_title() )
			);
		}

		$output .= '</ol>';
	}

	/**
	 * Filter the rendered series list for an Anthology series.
	 *
	 * @param string   $output The rendered output of the list.
	 * @param WP_Query $posts  A WP_Query object containing the posts used to construct the list.
	 */
	return apply_filters( 'anthology_render_series_list', $output, $posts );
}
