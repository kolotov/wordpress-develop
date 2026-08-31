<?php
/**
 * Tests for the WP_Plugin_Dependencies::has_dependents() method.
 *
 * @package WordPress
 */

require_once __DIR__ . '/base.php';

#[\PHPUnit\Framework\Attributes\Group( 'admin' )]
#[\PHPUnit\Framework\Attributes\Group( 'plugins' )]
#[\PHPUnit\Framework\Attributes\CoversMethod( WP_Plugin_Dependencies::class, 'has_dependents' )]
#[\PHPUnit\Framework\Attributes\CoversMethod( WP_Plugin_Dependencies::class, 'convert_to_slug' )]
class Tests_Admin_WPPluginDependencies_HasDependents extends WP_PluginDependencies_UnitTestCase {

	/**
	 * Tests that a plugin with dependents will return true.
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '22316' )]
	public function test_should_return_true_when_a_plugin_has_dependents() {
		$this->set_property_value( 'dependency_slugs', array( 'dependent' ) );
		$this->assertTrue( self::$instance::has_dependents( 'dependent/dependent.php' ) );
	}

	/**
	 * Tests that a single file plugin with dependents will return true.
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '22316' )]
	public function test_should_return_true_when_a_single_file_plugin_has_dependents() {
		$this->set_property_value( 'dependency_slugs', array( 'dependent' ) );
		$this->assertTrue( self::$instance::has_dependents( 'dependent.php' ) );
	}

	/**
	 * Tests that a plugin with no dependents will return false.
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '22316' )]
	public function test_should_return_false_when_a_plugin_has_no_dependents() {
		$this->set_property_value( 'dependency_slugs', array( 'dependent2' ) );
		$this->assertFalse( self::$instance::has_dependents( 'dependent/dependent.php' ) );
	}

	/**
	 * Tests that 'hello.php' is converted to 'hello-dolly'.
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '22316' )]
	public function test_should_convert_hellophp_to_hello_dolly() {
		$this->set_property_value( 'dependency_slugs', array( 'hello-dolly' ) );
		$this->assertTrue( self::$instance::has_dependents( 'hello.php' ) );
	}
}
