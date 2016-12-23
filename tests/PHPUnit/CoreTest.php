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

	public function testSortQueryBySeriesOrder() {
		$posts = array();

		for ( $i = 1; $i <= 3; $i++ ) {
			$post = new \stdClass;
			$post->ID = $i;

			$posts[ $i ] = $post;
		}

		// Create a dummy WP_Query object; it doesn't need any methods.
		$query = new \stdClass;
		$query->posts = $posts;

		$result = sort_query_by_series_order( $query, array( 2, 1, 3 ) );

		$this->assertEquals( 0, array_search( $posts[2], $result->posts, true ) );
		$this->assertEquals( 1, array_search( $posts[1], $result->posts, true ) );
		$this->assertEquals( 2, array_search( $posts[3], $result->posts, true ) );
	}

	public function testSortQueryBySeriesOrderReturnsEarlyIfOrderIsEmpty() {
		$query = new \stdClass;
		$query->posts = array( 'foo' );

		$this->assertEquals( $query, sort_query_by_series_order( $query, array() ) );
	}

	public function testSortQueryBySeriesOrderReturnsEarlyIfThereAreNoPosts() {
		$query = new \stdClass;
		$query->posts = array();

		$this->assertEquals( $query, sort_query_by_series_order( $query, array( 2, 1, 3 ) ) );
	}

	public function testSortQueryBySeriesOrderPutsUnaccountedValuesAtEnd() {
		$posts = array();

		for ( $i = 1; $i <= 5; $i++ ) {
			$post = new \stdClass;
			$post->ID = $i;

			$posts[ $i ] = $post;
		}

		// Create a dummy WP_Query object; it doesn't need any methods.
		$query = new \stdClass;
		$query->posts = $posts;

		$result = sort_query_by_series_order( $query, array( 2, 1, 3 ) );

		$this->assertEquals( 0, array_search( $posts[2], $result->posts, true ) );
		$this->assertEquals( 1, array_search( $posts[1], $result->posts, true ) );
		$this->assertEquals( 2, array_search( $posts[3], $result->posts, true ) );
		$this->assertEquals( 3, array_search( $posts[4], $result->posts, true ) );
		$this->assertEquals( 4, array_search( $posts[5], $result->posts, true ) );
	}
}
