<?php

/**
 *
 */
#[\PHPUnit\Framework\Attributes\Group( 'block-supports' )]
#[\PHPUnit\Framework\Attributes\CoversFunction( 'wp_get_block_style_variation_name_from_class' )]
class Tests_Block_Supports_WpGetBlockStyleVariationNameFromClass extends WP_UnitTestCase {
	/**
	 * Tests variation names are extracted correctly from a CSS class string.
	 *
	 *
	 *
	 *
	 * @param string     $class_string CSS class string.
	 * @param array|null $expected     Expected variation names.
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '61312' )]
	#[\PHPUnit\Framework\Attributes\DataProvider( 'data_block_style_variation_name_extraction' )]
	public function test_block_style_variation_name_extraction( $class_string, $expected ) {
		$actual = wp_get_block_style_variation_name_from_class( $class_string );

		$this->assertSame(
			$expected,
			$actual,
			'Block style variation names extracted from CSS class string should match'
		);
	}

	/**
	 * Data provider.
	 *
	 * @return array
	 */
	public static function data_block_style_variation_name_extraction() {
		return array(
			// @ticket 61312
			'missing class string' => array(
				'class_string' => null,
				'expected'     => null,
			),
			// @ticket 61312
			'empty class string'   => array(
				'class_string' => '',
				'expected'     => array(),
			),
			// @ticket 61312
			'no variation'         => array(
				'class_string' => 'is-style no-variation',
				'expected'     => array(),
			),
			// @ticket 61312
			'single variation'     => array(
				'class_string' => 'custom-class is-style-outline',
				'expected'     => array( 'outline' ),
			),
			// @ticket 61312
			'multiple variations'  => array(
				'class_string' => 'is-style-light custom-class is-style-outline',
				'expected'     => array( 'light', 'outline' ),
			),
		);
	}
}
