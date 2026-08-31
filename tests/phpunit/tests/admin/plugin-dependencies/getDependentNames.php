<?php
/**
 * Tests for the WP_Plugin_Dependencies::get_dependent_names() method.
 *
 * @package WordPress
 */

require_once __DIR__ . '/base.php';

#[\PHPUnit\Framework\Attributes\Group( 'admin' )]
#[\PHPUnit\Framework\Attributes\Group( 'plugins' )]
#[\PHPUnit\Framework\Attributes\CoversMethod( WP_Plugin_Dependencies::class, 'get_dependent_names' )]
#[\PHPUnit\Framework\Attributes\CoversMethod( WP_Plugin_Dependencies::class, 'get_plugins' )]
#[\PHPUnit\Framework\Attributes\CoversMethod( WP_Plugin_Dependencies::class, 'convert_to_slug' )]
#[\PHPUnit\Framework\Attributes\CoversMethod( WP_Plugin_Dependencies::class, 'get_dependents' )]
class Tests_Admin_WPPluginDependencies_GetDependentNames extends WP_PluginDependencies_UnitTestCase {

	/**
	 * Tests that dependent names are retrieved.
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '22316' )]
	public function test_should_get_dependent_names() {
		$this->set_property_value(
			'plugins',
			array(
				'dependent/dependent.php'   => array(
					'Name'            => 'Dependent 1',
					'RequiresPlugins' => 'dependency',
				),
				'dependent2/dependent2.php' => array(
					'Name'            => 'Dependent 2',
					'RequiresPlugins' => 'dependency',
				),
			)
		);

		self::$instance::initialize();

		$this->assertSame(
			array( 'Dependent 1', 'Dependent 2' ),
			self::$instance::get_dependent_names( 'dependency/dependency.php' )
		);
	}

	/**
	 * Tests that dependent names are sorted.
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '22316' )]
	public function test_should_sort_dependent_names() {
		$this->set_property_value(
			'plugins',
			array(
				'dependent2/dependent2.php' => array(
					'Name'            => 'Dependent 2',
					'RequiresPlugins' => 'dependency',
				),
				'dependent/dependent.php'   => array(
					'Name'            => 'Dependent 1',
					'RequiresPlugins' => 'dependency',
				),
			)
		);

		self::$instance::initialize();

		$this->assertSame(
			array( 'Dependent 1', 'Dependent 2' ),
			self::$instance::get_dependent_names( 'dependency/dependency.php' )
		);
	}
}
