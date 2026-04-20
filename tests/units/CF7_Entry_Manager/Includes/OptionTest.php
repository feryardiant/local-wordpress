<?php

namespace UnitTests\CF7_Entry_Manager\Includes;

use CF7_Entry_Manager\Option;
use UnitTests\CF7_Entry_Manager\TestCase;

/**
 * Class OptionTest
 */
class OptionTest extends TestCase {
	/**
	 * Setup before any test in this class runs.
	 */
	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		require_once static::package_file( 'cf7-entry-manager/includes/class-option.php' );
	}

	public function test_dummy() {
		$this->assertTrue( class_exists( Option::class ) );
	}
}
