<?php

declare(strict_types=1);

namespace IntegrationTests;

use Brain\Monkey;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

/**
 * Base Test Case for integration tests using real WordPress core.
 */
abstract class BaseTestCase extends PHPUnitTestCase {
	/**
	 * Setup before any test in this class runs.
	 */
	public static function setUpBeforeClass(): void {
		// Path to the WordPress core directory for testing.
		if ( ! defined( 'WP_CORE_DIR' ) ) {
			define( 'WP_CORE_DIR', ABSPATH );
		}

		$_root = dirname( ABSPATH, 3 );

		// Path to the wp-phpunit includes directory.
		// $_tests_dir = $_root . '/vendor/wp-phpunit/wp-phpunit';

		// Load the test functions.
		// require_once $_tests_dir . '/includes/functions.php';

		// 'WP_TESTS_SKIP_INSTALL';

		// Start up the WP testing environment.
		// require $_tests_dir . '/includes/bootstrap.php';
	}

	/**
	 * Setup the test environment.
	 */
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
	}

	/**
	 * Tear down the test environment.
	 */
	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}
}
