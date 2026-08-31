<?php
/**
 * Tests for the WP_Plugin_Dependencies::has_dependencies() method.
 *
 * @package WordPress
 */

require_once __DIR__ . '/base.php';

/**
 *
 */
#[\PHPUnit\Framework\Attributes\Group( 'admin' )]
#[\PHPUnit\Framework\Attributes\Group( 'plugins' )]
#[\PHPUnit\Framework\Attributes\CoversMethod( WP_Plugin_Dependencies::class, 'has_dependencies' )]
class Tests_Admin_WPPluginDependencies_HasDependencies extends WP_PluginDependencies_UnitTestCase {

	/**
	 * Tests that a plugin with dependencies will return true.
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '22316' )]
	public function test_should_return_true_when_a_plugin_has_dependencies() {
		$this->set_property_value( 'dependencies', array( 'dependent/dependent.php' => array() ) );
		$this->assertTrue( self::$instance::has_dependencies( 'dependent/dependent.php' ) );
	}

	/**
	 * Tests that a plugin with no dependencies will return false.
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '22316' )]
	public function test_should_return_false_when_a_plugin_has_no_dependencies() {
		$this->set_property_value( 'dependencies', array( 'dependent2/dependent2.php' => array() ) );
		$this->assertFalse( self::$instance::has_dependencies( 'dependent/dependent.php' ) );
	}
}
