<?php

/**
 *
 */
#[\PHPUnit\Framework\Attributes\Group( 'taxonomy' )]
#[\PHPUnit\Framework\Attributes\Group( 'category' )]
#[\PHPUnit\Framework\Attributes\CoversFunction( 'get_categories' )]
class Tests_Category_GetCategories extends WP_UnitTestCase {
	/**
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '36227' )]
	public function test_wp_error_should_return_an_empty_array() {
		$found = get_categories( array( 'taxonomy' => 'foo' ) );
		$this->assertSame( array(), $found );
	}
}
