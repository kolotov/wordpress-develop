<?php

/**
 * Tests for WP_Text_Diff_Renderer_Table.
 *
 */




#[\PHPUnit\Framework\Attributes\Group( 'diff' )]
class Tests_Diff_WpTextDiffRendererTable extends WP_UnitTestCase {
	/**
	 * @var WP_Text_Diff_Renderer_Table
	 */
	private $diff_renderer_table;

	public static function set_up_before_class() {
		parent::set_up_before_class();
		require_once ABSPATH . 'wp-includes/Text/Diff/Renderer.php';
		require_once ABSPATH . 'wp-includes/class-wp-text-diff-renderer-table.php';
	}

	public function set_up() {
		parent::set_up();
		$this->diff_renderer_table = new WP_Text_Diff_Renderer_Table();
	}

	/**
	 *
	 *
	 * @param string $property_name Property name to get.
	 * @param mixed $expected       Expected value.
	 */
	#[\PHPUnit\Framework\Attributes\DataProvider( 'data_compat_fields' )]
	#[\PHPUnit\Framework\Attributes\Ticket( '58898' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Text_Diff_Renderer_Table', '__get' )]
	public function test_should_get_compat_fields( $property_name, $expected ) {
		$this->assertSame( $expected, $this->diff_renderer_table->$property_name );
	}

	/**
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '58898' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Text_Diff_Renderer_Table', '__get' )]
	public function test_should_throw_deprecation_when_getting_dynamic_property() {
		$this->assertExpectedUserDeprecation(
			'WP_Text_Diff_Renderer_Table::__get()',
			function () {
				$this->assertNull( $this->diff_renderer_table->undeclared_property, 'Getting a dynamic property should return null from WP_Text_Diff_Renderer_Table::__get()' );
			}
		);
	}

	/**
	 *
	 *
	 * @param string $property_name Property name to set.
	 */
	#[\PHPUnit\Framework\Attributes\DataProvider( 'data_compat_fields' )]
	#[\PHPUnit\Framework\Attributes\Ticket( '58898' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Text_Diff_Renderer_Table', '__set' )]
	public function test_should_set_compat_fields( $property_name, $expected ) {
		$value                                     = uniqid();
		$this->diff_renderer_table->$property_name = $value;

		$this->assertSame( $value, $this->diff_renderer_table->$property_name );
	}

	/**
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '58898' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Text_Diff_Renderer_Table', '__set' )]
	public function test_should_throw_deprecation_when_setting_dynamic_property() {
		$this->assertExpectedUserDeprecation(
			'WP_Text_Diff_Renderer_Table::__set()',
			function () {
				$this->diff_renderer_table->undeclared_property = 'some value';
			}
		);
	}

	/**
	 *
	 *
	 * @param string $property_name Property name to check.
	 * @param mixed $expected       Expected value.
	 */
	#[\PHPUnit\Framework\Attributes\DataProvider( 'data_compat_fields' )]
	#[\PHPUnit\Framework\Attributes\Ticket( '58898' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Text_Diff_Renderer_Table', '__isset' )]
	public function test_should_isset_compat_fields( $property_name, $expected ) {
		$actual = isset( $this->diff_renderer_table->$property_name );
		if ( is_null( $expected ) ) {
			$this->assertFalse( $actual );
		} else {
			$this->assertTrue( $actual );
		}
	}

	/**
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '58898' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Text_Diff_Renderer_Table', '__isset' )]
	public function test_should_throw_deprecation_when_isset_of_dynamic_property() {
		$this->assertExpectedUserDeprecation(
			'WP_Text_Diff_Renderer_Table::__isset()',
			function () {
				$this->assertFalse( isset( $this->diff_renderer_table->undeclared_property ), 'Checking a dynamic property should return false from WP_Text_Diff_Renderer_Table::__isset()' );
			}
		);
	}

	/**
	 *
	 *
	 * @param string $property_name Property name to unset.
	 */
	#[\PHPUnit\Framework\Attributes\DataProvider( 'data_compat_fields' )]
	#[\PHPUnit\Framework\Attributes\Ticket( '58898' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Text_Diff_Renderer_Table', '__unset' )]
	public function test_should_unset_compat_fields( $property_name, $expected ) {
		unset( $this->diff_renderer_table->$property_name );
		$this->assertFalse( isset( $this->diff_renderer_table->$property_name ) );
	}

	/**
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '58898' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Text_Diff_Renderer_Table', '__unset' )]
	public function test_should_throw_deprecation_when_unset_of_dynamic_property() {
		$this->assertExpectedUserDeprecation(
			'WP_Text_Diff_Renderer_Table::__unset()',
			function () {
				unset( $this->diff_renderer_table->undeclared_property );
			}
		);
	}

	/**
	 * Data provider.
	 *
	 * @return array
	 */
	public static function data_compat_fields() {
		return array(
			'_show_split_view'     => array(
				'property_name' => '_show_split_view',
				'expected'      => true,
			),
			'inline_diff_renderer' => array(
				'property_name' => 'inline_diff_renderer',
				'expected'      => 'WP_Text_Diff_Renderer_inline',
			),
			'_diff_threshold'      => array(
				'property_name' => '_diff_threshold',
				'expected'      => 0.6,
			),
		);
	}
}
