<?php

#[\PHPUnit\Framework\Attributes\Group( 'comment' )]
#[\PHPUnit\Framework\Attributes\CoversFunction( 'comments_open' )]
class Tests_Comment_CommentsOpen extends WP_UnitTestCase {

	#[\PHPUnit\Framework\Attributes\Ticket( '54159' )]
	public function test_post_does_not_exist() {
		$this->assertFalse( comments_open( 99999 ) );
	}

	#[\PHPUnit\Framework\Attributes\Ticket( '54159' )]
	public function test_post_exist_status_open() {
		$post = self::factory()->post->create_and_get();
		$this->assertTrue( comments_open( $post ) );
	}

	#[\PHPUnit\Framework\Attributes\Ticket( '54159' )]
	public function test_post_exist_status_closed() {
		$post                 = self::factory()->post->create_and_get();
		$post->comment_status = 'closed';

		$this->assertFalse( comments_open( $post ) );
	}
}
