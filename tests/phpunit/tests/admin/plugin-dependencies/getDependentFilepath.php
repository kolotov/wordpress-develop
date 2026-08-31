<?php
/**
 * Tests for the WP_Plugin_Dependencies::get_dependent_filepath() method.
 *
 * @package WordPress
 */

require_once __DIR__ . '/base.php';

#[\PHPUnit\Framework\Attributes\Group( 'admin' )]
#[\PHPUnit\Framework\Attributes\Group( 'plugins' )]
#[\PHPUnit\Framework\Attributes\CoversMethod( WP_Plugin_Dependencies::class, 'get_dependent_filepath' )]
#[\PHPUnit\Framework\Attributes\CoversMethod( WP_Plugin_Dependencies::class, 'get_plugin_dirnames' )]
class Tests_Admin_WPPluginDependencies_GetDependentFilepath extends WP_PluginDependencies_UnitTestCase {

	/**
	 * Tests that the expected dependent filepath is retrieved.
	 *
	 *
	 *
	 * @param string       $dependent_slug The dependent slug.
	 * @param string[]     $plugins        An array of plugin data.
	 * @param string|false $expected       The expected result.
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '22316' )]
	#[\PHPUnit\Framework\Attributes\DataProvider( 'data_get_dependent_filepath' )]
	public function test_should_return_filepaths_for_installed_dependents( $dependent_slug, $plugins, $expected ) {
		$this->set_property_value( 'plugins', $plugins );
		self::$instance::initialize();

		$this->assertSame(
			$expected,
			self::$instance::get_dependent_filepath( $dependent_slug ),
			'The incorrect filepath was returned.'
		);
	}

	/**
	 * Data provider.
	 *
	 * @return array[]
	 */
	public static function data_get_dependent_filepath() {
		return array(
			'a plugin that exists'            => array(
				'dependent_slug' => 'dependent',
				'plugins'        => array( 'dependent/dependent.php' => array( 'RequiresPlugins' => 'woocommerce' ) ),
				'expected'       => 'dependent/dependent.php',
			),
			'no plugins'                      => array(
				'dependent_slug' => 'dependent',
				'plugins'        => array(),
				'expected'       => false,
			),
			'a plugin that starts with slug/' => array(
				'dependent_slug' => 'dependent',
				'plugins'        => array( 'dependent-pro/dependent.php' => array( 'RequiresPlugins' => 'woocommerce' ) ),
				'expected'       => false,
			),
			'a plugin that ends with slug/'   => array(
				'dependent_slug' => 'dependent',
				'plugins'        => array( 'not-dependent/not-dependent.php' => array( 'RequiresPlugins' => 'woocommerce' ) ),
				'expected'       => false,
			),
			'a plugin that does not exist'    => array(
				'dependent_slug' => 'dependent2',
				'plugins'        => array( 'dependent/dependent.php' => array( 'RequiresPlugins' => 'woocommerce' ) ),
				'expected'       => false,
			),
		);
	}
}
