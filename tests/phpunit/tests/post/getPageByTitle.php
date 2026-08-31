<?php

#[\PHPUnit\Framework\Attributes\Group( 'post' )]
#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'get_page_by_title' )]
class Tests_Post_GetPageByTitle extends WP_UnitTestCase {

	/**
	 * Tests that `get_page_by_title()` has been deprecated.
	 *
	 *
	 * @expectedDeprecated get_page_by_title
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '57041' )]
	public function test_get_page_by_title_should_be_deprecated() {
		$this->assertNull( get_page_by_title( '#57041 Page' ) );
	}
}
