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
