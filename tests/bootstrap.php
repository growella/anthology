<?php
/**
 * Bootstrap the test suite.
 *
 * @package Growella\TableOfContents
 * @author  Growella
 */

if ( ! defined( 'PROJECT' ) ) {
	define( 'PROJECT', __DIR__ . '/../includes/' );
}

if ( ! defined( 'ANTHOLOGY_VERSION' ) ) {
	define( 'ANTHOLOGY_VERSION', 'TESTMODE' );
}

if ( ! file_exists( __DIR__ . '/../vendor/autoload.php' ) ) {
	throw new PHPUnit_Framework_Exception(
		'ERROR: You must use Composer to install the test suite\'s dependencies!' . PHP_EOL
	);
}

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/test-tools/TestCase.php';
require_once __DIR__ . '/test-tools/dummy-files/wp-includes/class-wp-query.php';

WP_Mock::setUsePatchwork( true );
WP_Mock::bootstrap();
WP_Mock::tearDown();
