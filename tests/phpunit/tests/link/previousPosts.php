<?php

/**
 * Tests the `previous_posts()` function.
 *
 * @since 7.0.0
 *
 *
 */
#[\PHPUnit\Framework\Attributes\Group( 'link' )]
#[\PHPUnit\Framework\Attributes\CoversFunction( 'previous_posts' )]
class Tests_Link_PreviousPosts extends WP_UnitTestCase {

	/**
	 * The absence of a deprecation notice on PHP 8.1+ also shows that the issue is resolved.
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '64864' )]
	public function test_should_return_empty_string_on_singular() {
		$post_id = self::factory()->post->create();
		$this->go_to( get_permalink( $post_id ) );

		$this->assertSame( '', previous_posts( false ) );
	}
}
