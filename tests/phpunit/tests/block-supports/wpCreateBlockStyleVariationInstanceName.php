<?php

#[\PHPUnit\Framework\Attributes\Group( 'block-supports' )]
class Tests_Block_Supports_WpCreateBlockStyleVariationInstanceName extends WP_UnitTestCase {
	/**
	 * Tests that the block style variations block support creates
	 * the correct variation instance name.
	 *
	 *
	 *
	 * @expectedDeprecated wp_create_block_style_variation_instance_name
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '61312' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'wp_create_block_style_variation_instance_name' )]
	public function test_block_style_variation_instance_name_generation() {
		$block    = array( 'name' => 'test/block' );
		$actual   = wp_create_block_style_variation_instance_name( $block, 'my-variation' );
		$expected = 'my-variation--' . md5( serialize( $block ) );

		$this->assertSame( $expected, $actual, 'Block style variation instance name should be correct' );
	}
}
