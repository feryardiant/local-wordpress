<?php

namespace UnitTests\CF7_Entry_Manager;

use UnitTests\BaseTestCase;
use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

/**
 * Class PluginTest
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class PluginTest extends BaseTestCase {
	/**
	 * Test that constants are defined and hooks are registered on load.
	 */
	public function test_plugin_initialization() {
		// Mock WP functions used in the main file
		Functions\when( 'register_activation_hook' )->justReturn();
		Functions\when( 'register_deactivation_hook' )->justReturn();
		Functions\when( 'plugin_dir_url' )->justReturn( 'https://example.com/wp-content/plugins/cf7-entry-manager/' );
		Functions\when( 'register_post_type' )->justReturn();

		// Set WP version global if not available
		if ( ! isset( $GLOBALS['wp_version'] ) ) {
			$GLOBALS['wp_version'] = getenv( 'WP_VERSION' ) ?: '6.9';
		}

		// Expect hooks to be added
		// Actions\expectAdded( 'admin_notices' )->never();
		// Actions\expectAdded( 'admin_enqueue_scripts' )->once();
		// Actions\expectAdded( 'wpcf7_init' )->once();
		Actions\expectAdded( 'init' )
			->once()
			->whenHappen(
				function ( $callback ) {
					Filters\expectAdded( 'user_contactmethods' )->once();
					$callback();
				}
			);

		// Load the plugin file
		require static::package_file( 'cf7-entry-manager/cf7-entry-manager.php' );

		// Verify constants
		$this->assertTrue( defined( 'CF7EM_VERSION' ) );
		$this->assertEquals( '0.1.0', CF7EM_VERSION );
		$this->assertTrue( defined( 'CF7EM__MINIMUM_WP_VERSION' ) );
		$this->assertTrue( defined( 'CF7EM__MINIMUM_PHP_VERSION' ) );

		$this->addToAssertionCount( 5 );
	}
}
