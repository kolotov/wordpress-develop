<?php

/**
 */


#[\PHPUnit\Framework\Attributes\Group( 'meta' )]

#[\PHPUnit\Framework\Attributes\CoversFunction( 'update_metadata' )]
class Tests_Meta_UpdateMetadata extends WP_UnitTestCase {
	/**
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '35795' )]
	public function test_slashed_key_for_new_metadata() {
		update_metadata( 'post', 123, wp_slash( 'foo\foo' ), 'bar' );

		$found = get_metadata( 'post', 123, 'foo\foo', true );
		$this->assertSame( 'bar', $found );
	}

	/**
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '35795' )]
	public function test_slashed_key_for_existing_metadata() {
		global $wpdb;

		add_metadata( 'post', 123, wp_slash( 'foo\foo' ), 'bar' );
		update_metadata( 'post', 123, wp_slash( 'foo\foo' ), 'baz' );

		$found = get_metadata( 'post', 123, 'foo\foo', true );
		$this->assertSame( 'baz', $found );
	}

	/**
	 *
	 *
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '54316' )]
	#[\PHPUnit\Framework\Attributes\Group( 'user' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'clean_user_cache' )]
	public function test_clear_user_metadata_caches() {
		global $wpdb;

		$user_id = self::factory()->user->create();

		update_metadata( 'user', $user_id, 'key', 'value1' );

		$found = get_metadata( 'user', $user_id, 'key', true );
		$this->assertSame( 'value1', $found );

		// Simulate updating the DB from outside of WordPress.
		$wpdb->update(
			$wpdb->usermeta,
			array(
				'meta_value' => 'value2',
			),
			array(
				'user_id'  => $user_id,
				'meta_key' => 'key',
			)
		);

		// Clear the user caches.
		clean_user_cache( $user_id );

		// Verify metadata cache was cleared.
		$found = get_metadata( 'user', $user_id, 'key', true );
		$this->assertSame( 'value2', $found );
	}

	/**
	 *
	 *
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '54316' )]
	#[\PHPUnit\Framework\Attributes\Group( 'user' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'clean_user_cache' )]
	public function test_clear_post_metadata_caches() {
		global $wpdb;

		$post_id = self::factory()->post->create();

		update_metadata( 'post', $post_id, 'key', 'value1' );

		$found = get_metadata( 'post', $post_id, 'key', true );
		$this->assertSame( 'value1', $found );

		// Simulate updating the DB from outside of WordPress.
		$wpdb->update(
			$wpdb->postmeta,
			array(
				'meta_value' => 'value2',
			),
			array(
				'post_id'  => $post_id,
				'meta_key' => 'key',
			)
		);

		// Clear the post caches.
		clean_post_cache( $post_id );

		// Verify metadata cache was cleared.
		$found = get_metadata( 'post', $post_id, 'key', true );
		$this->assertSame( 'value2', $found );
	}
}
