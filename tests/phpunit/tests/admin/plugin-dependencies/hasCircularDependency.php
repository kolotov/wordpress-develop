<?php
/**
 * Tests for the WP_Plugin_Dependencies::has_circular_dependency() method.
 *
 * @package WordPress
 */

require_once __DIR__ . '/base.php';

#[\PHPUnit\Framework\Attributes\Group( 'admin' )]
#[\PHPUnit\Framework\Attributes\Group( 'plugins' )]
#[\PHPUnit\Framework\Attributes\CoversMethod( WP_Plugin_Dependencies::class, 'has_circular_dependency' )]
#[\PHPUnit\Framework\Attributes\CoversMethod( WP_Plugin_Dependencies::class, 'get_circular_dependencies' )]
#[\PHPUnit\Framework\Attributes\CoversMethod( WP_Plugin_Dependencies::class, 'check_for_circular_dependencies' )]
class Tests_Admin_WPPluginDependencies_HasCircularDependency extends WP_PluginDependencies_UnitTestCase {

	/**
	 * Tests that false is returned if Plugin Dependencies has not been initialized.
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '60457' )]
	public function test_should_return_false_before_initialization() {
		$this->set_property_value(
			'plugins',
			array(
				'dependent/dependent.php'   => array(
					'Name'            => 'Dependent',
					'RequiresPlugins' => 'dependency',
				),
				'dependency/dependency.php' => array(
					'Name'            => 'Dependency',
					'RequiresPlugins' => 'dependent',
				),
			)
		);

		// Ensure Plugin Dependencies has not been initialized.
		$this->assertFalse(
			$this->get_property_value( 'initialized' ),
			'Plugin Dependencies has been initialized.'
		);

		$this->assertSame(
			self::$static_properties['circular_dependencies_slugs'],
			$this->get_property_value( 'circular_dependencies_slugs' ),
			'"circular_dependencies_slugs" was not set to its default value.'
		);

		$this->assertFalse(
			self::$instance->has_circular_dependency( 'dependency' ),
			'false was not returned before initialization.'
		);
	}

	/**
	 * Tests that a plugin with a circular dependency will return true.
	 *
	 *
	 *
	 * @param string  $plugin_to_check The plugin file of the plugin to check.
	 * @param array[] $plugins         An array of plugins.
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '22316' )]
	#[\PHPUnit\Framework\Attributes\DataProvider( 'data_circular_dependencies' )]
	public function test_should_return_true_when_a_plugin_has_circular_dependency( $plugin_to_check, $plugins ) {
		$this->set_property_value( 'plugins', $plugins );
		self::$instance::initialize();

		$this->assertTrue( self::$instance::has_circular_dependency( $plugin_to_check ) );
	}

	/**
	 * Data provider.
	 *
	 * @return array[]
	 */
	public static function data_circular_dependencies() {
		return array(
			'a plugin that depends on itself' => array(
				'plugin_to_check' => 'dependency/dependency.php',
				'plugins'         => array(
					'dependency/dependency.php' => array(
						'Name'            => 'Dependency 1',
						'RequiresPlugins' => 'dependency',
					),
				),
			),
			'two plugins'                     => array(
				'plugin_to_check' => 'dependency/dependency.php',
				'plugins'         => array(
					'dependency/dependency.php'   => array(
						'Name'            => 'Dependency 1',
						'RequiresPlugins' => 'dependency2',
					),
					'dependency2/dependency2.php' => array(
						'Name'            => 'Dependency 2',
						'RequiresPlugins' => 'dependency',
					),
				),
			),
			'three plugins'                   => array(
				'plugin_to_check' => 'dependency/dependency.php',
				'plugins'         => array(
					'dependency/dependency.php'   => array(
						'Name'            => 'Dependency 1',
						'RequiresPlugins' => 'dependency2',
					),
					'dependency2/dependency2.php' => array(
						'Name'            => 'Dependency 2',
						'RequiresPlugins' => 'dependency3',
					),
					'dependency3/dependency3.php' => array(
						'Name'            => 'Dependency 3',
						'RequiresPlugins' => 'dependency',
					),
				),
			),
			'four plugins'                    => array(
				'plugin_to_check' => 'dependency/dependency.php',
				'plugins'         => array(
					'dependency/dependency.php'   => array(
						'Name'            => 'Dependency 1',
						'RequiresPlugins' => 'dependency4',
					),
					'dependency2/dependency2.php' => array(
						'Name'            => 'Dependency 2',
						'RequiresPlugins' => 'dependency3',
					),
					'dependency3/dependency3.php' => array(
						'Name'            => 'Dependency 3',
						'RequiresPlugins' => 'dependency',
					),
					'dependency4/dependency4.php' => array(
						'Name'            => 'Dependency 4',
						'RequiresPlugins' => 'dependency2',
					),
				),
			),
		);
	}

	/**
	 * Tests that a plugin with no circular dependencies will return false.
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '22316' )]
	public function test_should_return_false_when_a_plugin_has_no_circular_dependency() {
		$this->set_property_value(
			'plugins',
			array(
				'dependency/dependency.php' => array(
					'Name'            => 'Dependency 1',
					'RequiresPlugins' => 'dependency2',
				),
			)
		);

		self::$instance::initialize();

		$this->assertFalse( self::$instance::has_circular_dependency( 'dependent/dependent.php' ) );
	}
}
