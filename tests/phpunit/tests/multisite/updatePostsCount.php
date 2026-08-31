<?php

/**
 * Test that update_posts_count() gets called via default filters on multisite.
 *
 *
 */
#[\PHPUnit\Framework\Attributes\Group( 'ms-required' )]
#[\PHPUnit\Framework\Attributes\Group( 'ms-site' )]
#[\PHPUnit\Framework\Attributes\Group( 'multisite' )]
#[\PHPUnit\Framework\Attributes\CoversFunction( 'update_posts_count' )]
#[\PHPUnit\Framework\Attributes\CoversFunction( '_update_posts_count_on_transition_post_status' )]
#[\PHPUnit\Framework\Attributes\CoversFunction( '_update_posts_count_on_delete' )]
class Tests_Multisite_UpdatePostsCount extends WP_UnitTestCase {

	/**
	 * Tests that posts count is updated correctly when posts are added or deleted.
	 *
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '27952' )]
	#[\PHPUnit\Framework\Attributes\Ticket( '53443' )]
	public function test_update_posts_count() {
		$blog_id = self::factory()->blog->create();
		switch_to_blog( $blog_id );

		$original_post_count = (int) get_site()->post_count;

		$post_id = self::factory()->post->create();

		$post_count_after_creating = get_site()->post_count;

		wp_delete_post( $post_id, true );

		$post_count_after_deleting = get_site()->post_count;

		restore_current_blog();

		/*
		 * Check that posts count is updated when a post is created:
		 * add_action( 'transition_post_status', '_update_posts_count_on_transition_post_status', 10, 3 );
		 *
		 * Check that _update_posts_count_on_transition_post_status() is called on that filter,
		 * which then calls update_posts_count() to update the count.
		 */
		$this->assertSame( $original_post_count + 1, $post_count_after_creating, 'Post count should be incremented by 1.' );

		/*
		 * Check that posts count is updated when a post is deleted:
		 * add_action( 'after_delete_post', '_update_posts_count_on_delete', 10, 2 );
		 *
		 * Check that _update_posts_count_on_delete() is called on that filter,
		 * which then calls update_posts_count() to update the count.
		 */
		$this->assertSame( $original_post_count, $post_count_after_deleting, 'Post count should match the original count.' );
	}
}
