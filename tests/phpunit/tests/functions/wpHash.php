<?php

/**
 * Tests for the behavior of `wp_hash()`
 *
 *
 */
#[\PHPUnit\Framework\Attributes\Group( 'functions' )]
#[\PHPUnit\Framework\Attributes\CoversFunction( 'wp_hash' )]
class Tests_Functions_wpHash extends WP_UnitTestCase {

	/**
	 *
	 */
	#[\PHPUnit\Framework\Attributes\DataProvider( 'data_wp_hash_uses_specified_algorithm' )]
	#[\PHPUnit\Framework\Attributes\Ticket( '62005' )]
	public function test_wp_hash_uses_specified_algorithm( string $algo, int $expected_length ) {
		$hash = wp_hash( 'data', 'auth', $algo );

		$this->assertSame( $expected_length, strlen( $hash ) );
	}

	public static function data_wp_hash_uses_specified_algorithm() {
		return array(
			array( 'md5', 32 ),
			array( 'sha1', 40 ),
			array( 'sha256', 64 ),
		);
	}

	/**
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '62005' )]
	public function test_wp_hash_throws_exception_on_invalid_algorithm() {
		$this->expectException( 'InvalidArgumentException' );

		wp_hash( 'data', 'auth', 'invalid' );
	}
}
