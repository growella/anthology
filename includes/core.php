<?php
/**
 * Core plugin functionality.
 *
 * @package Anthology
 * @author  Growella
 */

namespace Growella\Anthology\Core;

/**
 * Register and enqueue scripts within WordPress.
 */
function enqueue_admin_assets() {
	wp_enqueue_style(
		'anthology-admin',
		plugins_url( 'assets/css/admin.min.css', __DIR__ ),
		null,
		ANTHOLOGY_VERSION
	);

	wp_enqueue_script(
		'anthology-admin',
		plugins_url( 'assets/js/admin.min.js', __DIR__ ),
		array( 'jquery', 'jquery-ui-sortable' ),
		ANTHOLOGY_VERSION,
		true
	);
}
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\enqueue_admin_assets' );

/**
 * Given a WP_Query object and an [ordered] array of IDs, re-order WP_Query::posts to match.
 *
 * This function will sort through $query->posts and re-order the posts based on the $order array.
 * Any IDs not found in $order will be left in-place.
 *
 * @param WP_Query $query The WP_Query object.
 * @param array    $order Optional. An ordered array of post IDs. Default is empty.
 * @return WP_Query The $query object, with its posts array re-ordered.
 */
function sort_query_by_series_order( $query, $order = array() ) {

	// Return early if there's nothing to do.
	if ( empty( $query->posts ) || empty( $order ) ) {
		return $query;
	}

	usort( $query->posts, function ( $a, $b ) use ( $order ) {

		// Handle values that aren't in $order.
		if ( ! in_array( $a->ID, $order, true ) ) {
			return 1;

		} elseif ( ! in_array( $b->ID, $order, true ) ) {
			return -1;
		}

		return array_search( $a->ID, $order, true ) < array_search( $b->ID, $order, true ) ? -1 : 1;
	} );

	return $query;
}
