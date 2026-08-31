<?php

/**
 * Test the block WP_Duotone class.
 *
 *
 */

#[\PHPUnit\Framework\Attributes\Group( 'block-supports' )]




class Tests_Block_Supports_Duotone extends WP_UnitTestCase {
	/**
	 * Tests whether the duotone preset class is added to the block.
	 *
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '58555' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Duotone', 'render_duotone_support' )]
	public function test_render_duotone_support_preset() {
		$block         = array(
			'blockName' => 'core/image',
			'attrs'     => array( 'style' => array( 'color' => array( 'duotone' => 'var:preset|duotone|blue-orange' ) ) ),
		);
		$wp_block      = new WP_Block( $block );
		$block_content = '<figure class="wp-block-image size-full"><img src="/my-image.jpg" /></figure>';
		$expected      = '<figure class="wp-block-image size-full wp-duotone-blue-orange"><img src="/my-image.jpg" /></figure>';
		$this->assertSame( $expected, WP_Duotone::render_duotone_support( $block_content, $block, $wp_block ) );
	}

	/**
	 * Tests whether the duotone unset class is added to the block.
	 *
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '58555' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Duotone', 'render_duotone_support' )]
	public function test_render_duotone_support_css() {
		$block         = array(
			'blockName' => 'core/image',
			'attrs'     => array( 'style' => array( 'color' => array( 'duotone' => 'unset' ) ) ),
		);
		$wp_block      = new WP_Block( $block );
		$block_content = '<figure class="wp-block-image size-full"><img src="/my-image.jpg" /></figure>';
		$expected      = '/<figure class="wp-block-image size-full wp-duotone-unset-\d+"><img src="\\/my-image.jpg" \\/><\\/figure>/';
		$this->assertMatchesRegularExpression( $expected, WP_Duotone::render_duotone_support( $block_content, $block, $wp_block ) );
	}

	/**
	 * Tests whether the duotone custom class is added to the block.
	 *
	 */
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Duotone', 'render_duotone_support' )]
	public function test_render_duotone_support_custom() {
		$block         = array(
			'blockName' => 'core/image',
			'attrs'     => array( 'style' => array( 'color' => array( 'duotone' => array( '#FFFFFF', '#000000' ) ) ) ),
		);
		$wp_block      = new WP_Block( $block );
		$block_content = '<figure class="wp-block-image size-full"><img src="/my-image.jpg" /></figure>';
		$expected      = '/<figure class="wp-block-image size-full wp-duotone-ffffff-000000-\d+"><img src="\\/my-image.jpg" \\/><\\/figure>/';
		$this->assertMatchesRegularExpression( $expected, WP_Duotone::render_duotone_support( $block_content, $block, $wp_block ) );
	}

	/**
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '65576' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Duotone', 'restore_image_outer_container' )]
	public function test_restore_image_outer_container_moves_duotone_class_to_wrapper_in_classic_theme() {
		switch_theme( 'default' );

		$block_content = '<div class="wp-block-image"><figure class="alignright wp-duotone-blue-orange size-full"><img src="/my-image.jpg"></figure></div>';
		$expected      = '<div class="wp-block-image wp-duotone-blue-orange"><figure class="alignright size-full"><img src="/my-image.jpg"></figure></div>';

		$this->assertEqualHTML( $expected, WP_Duotone::restore_image_outer_container( $block_content ) );
	}

	/**
	 * Tests whether the slug is extracted from the attribute.
	 *
	 */
	#[\PHPUnit\Framework\Attributes\DataProvider( 'data_get_slug_from_attribute' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Duotone', 'get_slug_from_attribute' )]
	public function test_get_slug_from_attribute( $data_attr, $expected ) {

		$reflection = new ReflectionMethod( 'WP_Duotone', 'get_slug_from_attribute' );
		if ( PHP_VERSION_ID < 80100 ) {
			$reflection->setAccessible( true );
		}

		$this->assertSame( $expected, $reflection->invoke( null, $data_attr ) );
	}

	/**
	 * Data provider.
	 *
	 * @return array[].
	 */
	public static function data_get_slug_from_attribute() {
		return array(
			'pipe-slug'                       => array( 'var:preset|duotone|blue-orange', 'blue-orange' ),
			'css-var'                         => array( 'var(--wp--preset--duotone--blue-orange)', 'blue-orange' ),
			'css-var-invalid-slug-chars'      => array( 'var(--wp--preset--duotone--.)', '.' ),
			'css-var-missing-end-parenthesis' => array( 'var(--wp--preset--duotone--blue-orange', '' ),
			'invalid'                         => array( 'not a valid attribute', '' ),
			'css-var-no-value'                => array( 'var(--wp--preset--duotone--)', '' ),
			'pipe-slug-no-value'              => array( 'var:preset|duotone|', '' ),
			'css-var-spaces'                  => array( 'var(--wp--preset--duotone--    ', '' ),
			'pipe-slug-spaces'                => array( 'var:preset|duotone|  ', '' ),
			'array-of-colors'                 => array( array( '#000000', '#ffffff' ), '' ),
			'empty-array'                     => array( array(), '' ),
		);
	}

	/**
	 * Tests whether the CSS declarations are generated even if the block content is
	 * empty. This is needed to make the CSS output stable across paginations for
	 * features like the enhanced pagination of the Query block.
	 *
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '59694' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Duotone', 'render_duotone_support' )]
	public function test_css_declarations_are_generated_even_with_empty_block_content() {
		$block    = array(
			'blockName' => 'core/image',
			'attrs'     => array( 'style' => array( 'color' => array( 'duotone' => 'var:preset|duotone|blue-orange' ) ) ),
		);
		$wp_block = new WP_Block( $block );

		$block_css_declarations_property = new ReflectionProperty( 'WP_Duotone', 'block_css_declarations' );
		if ( PHP_VERSION_ID < 80100 ) {
			$block_css_declarations_property->setAccessible( true );
		}
		$previous_value = $block_css_declarations_property->getValue();
		$block_css_declarations_property->setValue( null, array() );

		try {
			WP_Duotone::render_duotone_support( '', $block, $wp_block );
			$actual = $block_css_declarations_property->getValue();
		} finally {
			$block_css_declarations_property->setValue( null, $previous_value );
		}

		$this->assertNotEmpty( $actual );
	}

	/**
	 */
	#[\PHPUnit\Framework\Attributes\DataProvider( 'data_is_preset' )]
	public function test_is_preset( $data_attr, $expected ) {
		$reflection = new ReflectionMethod( 'WP_Duotone', 'is_preset' );
		if ( PHP_VERSION_ID < 80100 ) {
			$reflection->setAccessible( true );
		}

		$this->assertSame( $expected, $reflection->invoke( null, $data_attr ) );
	}

	/**
	 * Data provider.
	 *
	 * @return array[].
	 */
	public static function data_is_preset() {
		return array(
			'pipe-slug'                       => array( 'var:preset|duotone|blue-orange', true ),
			'css-var'                         => array( 'var(--wp--preset--duotone--blue-orange)', true ),
			'css-var-invalid-slug-chars'      => array( 'var(--wp--preset--duotone--.)', false ),
			'css-var-missing-end-parenthesis' => array( 'var(--wp--preset--duotone--blue-orange', false ),
			'invalid'                         => array( 'not a valid attribute', false ),
			'array-of-colors'                 => array( array( '#000000', '#ffffff' ), false ),
			'empty-array'                     => array( array(), false ),
		);
	}

	/**
	 */
	#[\PHPUnit\Framework\Attributes\DataProvider( 'data_colord_parse_hue' )]
	#[\PHPUnit\Framework\Attributes\Ticket( '59496' )]
	public function test_colord_parse_hue( $value, $unit, $expected ) {
		$reflection = new ReflectionMethod( 'WP_Duotone', 'colord_parse_hue' );
		if ( PHP_VERSION_ID < 80100 ) {
			$reflection->setAccessible( true );
		}

		$this->assertSame( $expected, $reflection->invoke( null, $value, $unit ) );
	}

	/**
	 * Data provider.
	 *
	 * @return array[].
	 */
	public static function data_colord_parse_hue() {
		return array(
			'deg-angle-unit'                => array( 120, 'deg', 120.0 ),
			'grad-angle-unit'               => array( 120, 'grad', 108.0 ),
			'turn-angle-unit'               => array( 120, 'turn', 43200.0 ),
			'rad-angle-unit'                => array( 120, 'rad', 6875.493541569878 ),
			'empty-angle-unit'              => array( 120, '', 120.0 ),
			'invalid-angle-unit'            => array( 120, 'invalid', 120.0 ),
			'negative-value-deg-angle-unit' => array( -120, 'deg', -120.0 ),
		);
	}
}
