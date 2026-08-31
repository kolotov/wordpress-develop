<?php

/**
 */
#[\PHPUnit\Framework\Attributes\Group( 'canonical' )]
#[\PHPUnit\Framework\Attributes\Group( 'rewrite' )]
#[\PHPUnit\Framework\Attributes\Group( 'query' )]
class Tests_Canonical_Robots extends WP_Canonical_UnitTestCase {

	public function test_remove_trailing_slashes_for_robots_requests() {
		$this->set_permalink_structure( '/%postname%/' );
		$this->assertCanonical( '/robots.txt', '/robots.txt' );
		$this->assertCanonical( '/robots.txt/', '/robots.txt' );
	}
}
