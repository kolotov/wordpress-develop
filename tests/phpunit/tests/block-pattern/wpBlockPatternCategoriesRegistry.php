<?php
/**
 * Unit tests for WP_Block_Pattern_Categories_Registry::is_registered().
 *
 * @package WordPress
 * @subpackage Blocks
 * @since 6.9.0
 *
 */

#[\PHPUnit\Framework\Attributes\Group( 'block-patterns' )]

#[\PHPUnit\Framework\Attributes\CoversMethod( WP_Block_Pattern_Categories_Registry::class, 'is_registered' )]

class Tests_Block_Pattern_WPBlockPatternCategoriesRegistry extends WP_UnitTestCase {

	/**
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '63957' )]
	public function test_is_registered_with_null_category_name() {
		$registry = WP_Block_Pattern_Categories_Registry::get_instance();
		$this->assertFalse( $registry->is_registered( null ) );
	}
}
