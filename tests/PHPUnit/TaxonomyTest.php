<?php
/**
 * Tests for the taxonomy-specific functionality.
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
		$term               = new \stdClass;
		$term->slug         = 'slug';
		\WP_Query::$__posts = array( 'one', 'two', 'three' );

		M::wpFunction( __NAMESPACE__ . '\get_series_query', array(
			'return' => new \WP_Query(),
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

		M::wpFunction( 'the_date' );
		M::wpFunction( 'wp_reset_postdata' );

		M::wpPassthruFunction( 'esc_attr' );
		M::wpPassthruFunction( 'esc_html_x' );

		ob_start();
		render_series_ordering( $term );
		$output = ob_get_contents();
		ob_end_clean();

		$this->assertContains( 'name="anthology-series-order[]', $output );

		\WP_Query::reset();
	}

	public function testRenderSeriesOrderingForEmptyTerm() {
		$term               = new \stdClass;
		$term->slug         = 'slug';
		\WP_Query::$__posts = array();

		M::wpFunction( __NAMESPACE__ . '\get_series_query', array(
			'return' => new \WP_Query(),
		) );

		M::wpFunction( 'the_date' );
		M::wpFunction( 'wp_reset_postdata' );

		M::wpPassthruFunction( 'esc_html_e' );
		M::wpPassthruFunction( 'esc_html_x' );

		ob_start();
		render_series_ordering( $term );
		$output = ob_get_contents();
		ob_end_clean();

		$this->assertContains( '<p class="error">', $output );

		\WP_Query::reset();
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

	public function testGetSeriesQuery() {
		$term             = new \stdClass;
		$term->term_id    = 123;
		$term->taxonomy   = 'anthology-series';
		$tax              = new \stdClass;
		$tax->object_type = array( 'post' );
		\WP_Query::$__posts = array( 'one', 'two', 'three' );

		M::wpFunction( 'get_term_by', array(
			'args'   => array( 'slug', 'slug', 'anthology-series' ),
			'return' => $term,
		) );

		M::wpFunction( 'get_term_meta', array(
			'return' => array( 3, 2, 1 ),
		) );

		M::wpFunction( 'get_taxonomy', array(
			'return' => $tax,
		) );

		M::wpPassthruFunction( 'wp_parse_args' );
		M::wpPassthruFunction( 'Growella\Anthology\Core\sort_query_by_series_order', array(
			'times'   => 1,
		) );

		$result = get_series_query( 'slug', array( 'limit' => 10 ) );

		$this->assertEquals( \WP_Query::$__instance, $result );
		$this->assertArrayHasKey( 'tax_query', \WP_Query::$__data );

		\WP_Query::reset();
	}

	public function testGetDefaultSeriesForPost() {
		$terms = array(
			new \stdClass,
			new \stdClass,
		);

		M::wpFunction( 'get_the_terms', array(
			'times'  => 1,
			'args'   => array( 123, 'anthology-series' ),
			'return' => $terms,
		) );

		M::wpFunction( 'is_wp_error', array(
			'return' => false,
		) );

		$this->assertEquals( $terms[0], get_default_series_for_post( 123 ) );
	}

	public function testGetDefaultSeriesForPostChecksForWPErrors() {
		M::wpFunction( 'get_the_terms', array(
			'return' => new \stdClass,
		) );

		M::wpFunction( 'is_wp_error', array(
			'return' => true,
		) );

		$this->assertFalse( get_default_series_for_post( 123 ) );
	}

	public function testGetDefaultSeriesForPostReturnsFalseIfNoTerms() {
		M::wpFunction( 'get_the_terms', array(
			'return' => false,
		) );

		M::wpFunction( 'is_wp_error', array(
			'return' => false,
		) );

		$this->assertFalse( get_default_series_for_post( 123 ) );
	}
}
