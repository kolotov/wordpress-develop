<?php

#[\PHPUnit\Framework\Attributes\Group( 'comment' )]
#[\PHPUnit\Framework\Attributes\CoversFunction( 'pings_open' )]
class Tests_Comment_PingsOpen extends WP_UnitTestCase {

	#[\PHPUnit\Framework\Attributes\Ticket( '54159' )]
	public function test_post_does_not_exist() {
		$this->assertFalse( pings_open( 99999 ) );
	}

	#[\PHPUnit\Framework\Attributes\Ticket( '54159' )]
	public function test_post_exist_status_open() {
		$post = self::factory()->post->create_and_get();
		$this->assertTrue( pings_open( $post ) );
	}

	#[\PHPUnit\Framework\Attributes\Ticket( '54159' )]
	public function test_post_exist_status_closed() {
		$post              = self::factory()->post->create_and_get();
		$post->ping_status = 'closed';

		$this->assertFalse( pings_open( $post ) );
	}
}
