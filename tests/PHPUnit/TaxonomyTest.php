<?php
/**
 * Tests for the main plugin functionality.
 *
 * @package Growella\Anthology
 * @author  Growella
 */

namespace Growella\Anthology\Taxonomy;

use WP_Mock as M;

class TaxonomyTest extends \Growella\Anthology\TestCase {

	protected $testFiles = array(
		'taxonomy.php',
	);

	public function testRegisterSeriesTaxonomy() {
		M::wpFunction( 'register_taxonomy', array(
			'times' => 1,
			'return' => function ( $taxonomy, $post_types, $args ) {
				$this->assertEquals( 'anthology-series', $taxonomy );
				$this->assertFalse( $args['hierarchical'] );
			}
		) );

		M::wpPassthruFunction( '__' );
		M::wpPassthruFunction( '_x' );

		register_series_taxonomy();
	}

	public function testSaveSeriesOrder() {
		$_POST = array(
			'anthology-nonce'        => 'abc123',
			'anthology-series-order' => array( 3, 2, 1 ),
		);

		M::wpFunction( 'wp_verify_nonce', array(
			'return' => true,
		) );

		M::wpFunction( 'update_term_meta', array(
			'times'  => 1,
			'args'   => array( 42, 'anthology-order', array( 3, 2, 1 ) ),
		) );

		M::wpPassthruFunction( 'absint' );

		save_series_order( 42 );

		// Reset $_POST.
		$_POST = array();
	}

	public function testRenderSeriesOrdering() {
		$term             = new \stdClass;
		$term->ID         = 123;
		$term->taxonomy   = 'anthology-series';
		$tax              = new \stdClass;
		$tax->object_type = array( 'post' );
		\WP_Query::$__posts = array( 'one', 'two', 'three' );

		M::wpFunction( 'get_term_meta', array(
			'return' => array( 3, 2, 1 ),
		) );

		M::wpFunction( 'get_taxonomy', array(
			'return' => $tax,
		) );

		M::wpFunction( 'the_title', array(
			'return' => 'title',
		) );

		M::wpFunction( 'get_the_ID', array(
			'return_in_order' => array( 3, 2, 1 ),
		) );

		M::wpFunction( 'the_permalink', array(
			'return' => 'title',
		) );

		M::wpFunction( 'wp_nonce_field', array(
			'times'  => 1,
			'args'   => array( 'anthology-series-ordering', 'anthology-nonce' ),
		) );

		M::wpFunction( 'wp_reset_postdata' );

		M::wpPassthruFunction( 'esc_attr' );
		M::wpPassthruFunction( 'esc_html_x' );

		ob_start();
		render_series_ordering( $term );
		$output = ob_get_contents();
		ob_end_clean();

		$this->assertContains( 'name="anthology-series-order[]', $output );

		\WP_Query::$__posts = array();
	}

	public function testRenderSeriesOrderingForEmptyTerm() {
		$term             = new \stdClass;
		$term->ID         = 123;
		$term->taxonomy   = 'anthology-series';
		$tax              = new \stdClass;
		$tax->object_type = array( 'post' );

		M::wpFunction( 'get_term_meta', array(
			'return' => array(),
		) );

		M::wpFunction( 'get_taxonomy', array(
			'return' => $tax,
		) );

		M::wpFunction( 'wp_reset_postdata' );

		M::wpPassthruFunction( 'esc_html_e' );
		M::wpPassthruFunction( 'esc_html_x' );

		ob_start();
		render_series_ordering( $term );
		$output = ob_get_contents();
		ob_end_clean();

		$this->assertContains( '<p class="error">', $output );
	}

	public function testSaveSeriesOrderChecksPostSuperglobalForOrder() {
		$_POST = array( 'anthology-nonce' => 'abc123' );

		M::wpFunction( 'update_term_meta', array(
			'times'  => 0,
		) );

		$this->assertEmpty( save_series_order( 42 ), 'A missing order should cause save_series_order() to return early.' );

		// Reset $_POST.
		$_POST = array();
	}

	public function testSaveSeriesOrderChecksPostSuperglobalForNonce() {
		$_POST = array( 'anthology-series-order' => array( 3, 2, 1 ) );

		M::wpFunction( 'update_term_meta', array(
			'times'  => 0,
		) );

		$this->assertEmpty( save_series_order( 42 ), 'A missing nonce should cause save_series_order() to return early.' );

		// Reset $_POST.
		$_POST = array();
	}

	public function testSaveSeriesOrderVerifiesNonce() {
		$_POST = array(
			'anthology-nonce'        => 'abc123',
			'anthology-series-order' => array( 3, 2, 1 ),
		);

		M::wpFunction( 'wp_verify_nonce', array(
			'return' => false,
		) );

		M::wpFunction( 'update_term_meta', array(
			'times'  => 0,
		) );

		save_series_order( 42 );

		// Reset $_POST.
		$_POST = array();
	}
}
