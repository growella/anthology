<?php
/**
 * Tests for the theme template tags.
 *
 * @package Growella\Anthology
 * @author  Growella
 */

namespace Growella\Anthology\Output;

use WP_Mock as M;

class OutputTest extends \Growella\Anthology\TestCase {

	protected $testFiles = array(
		'output.php',
	);

	public function setUp() {
		M::wpFunction( 'add_shortcode' );

		parent::setUp();
	}

	public function testRenderSeriesShortcode() {
		M::wpPassthruFunction( 'shortcode_atts' );
	}
}
