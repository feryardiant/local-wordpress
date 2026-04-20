<?php

namespace UnitTests;

use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

/**
 * Base Test Case for all unit tests.
 */
abstract class BaseTestCase extends PHPUnitTestCase {
	/**
	 * Setup the test environment.
	 */
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		// Mock WP functions used in the main file
		Functions\when( '__' )->returnArg( 1 );
		Functions\when( '_x' )->returnArg( 1 );
		Functions\when( 'esc_attr' )->returnArg( 1 );
		Functions\when( 'esc_html' )->returnArg( 1 );
		Functions\when( 'esc_html__' )->returnArg( 1 );
		Functions\when( 'esc_html_e' )->echoArg( 1 );

		Functions\when( 'is_wp_error' )->justReturn( false );
		Functions\when( 'wp_parse_args' )->alias(
			fn( $a, $b ) => array_merge( $b, $a )
		);
	}

	/**
	 * Tear down the test environment.
	 */
	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	protected static function package_file( string $file_path ): string {
		return dirname( ABSPATH, 3 ) . '/packages/' . $file_path;
	}
}
