<?php
/**
 * Tests for the main plugin functionality.
 *
 * @package Growella\Anthology
 * @author  Growella
 */

namespace Growella\Anthology\Core;

use WP_Mock as M;

class CoreTest extends \Growella\Anthology\TestCase {

	protected $testFiles = array(
		'core.php',
	);

	public function testEnqueueAdminAssets() {
		M::wpFunction( 'wp_enqueue_style', array(
			'times' => 1,
			'args'  => array( 'anthology-admin', '*', '*', ANTHOLOGY_VERSION ),
		) );

		M::wpFunction( 'wp_enqueue_script', array(
			'times' => 1,
			'args'  => array( 'anthology-admin', '*', '*', '*', ANTHOLOGY_VERSION ),
		) );

		M::wpFunction( 'plugins_url' );

		enqueue_admin_assets();
	}
}
