<?php

/**
 *
 */
#[\PHPUnit\Framework\Attributes\Group( 'post' )]
#[\PHPUnit\Framework\Attributes\Group( 'media' )]
#[\PHPUnit\Framework\Attributes\Group( 'upload' )]
#[\PHPUnit\Framework\Attributes\CoversFunction( 'wp_count_attachments' )]
class Tests_Post_wpCountAttachments extends WP_UnitTestCase {

	/**
	 * Tests that the result is cached.
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '55227' )]
	public function test_wp_count_attachments_should_cache_the_result() {
		$mime_type = 'image/jpeg';
		$cache_key = 'attachments:image_jpeg';

		self::factory()->post->create_many(
			3,
			array(
				'post_type'      => 'attachment',
				'post_mime_type' => $mime_type,
			)
		);
		$expected = wp_count_attachments( $mime_type );
		$actual   = wp_cache_get( $cache_key, 'counts' );

		$this->assertEquals( $expected, $actual );
	}
}
