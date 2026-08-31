<?php

/**
 * Tests for the behavior of `wp_verify_fast_hash()`.
 *
 *
 */
#[\PHPUnit\Framework\Attributes\Group( 'functions' )]
#[\PHPUnit\Framework\Attributes\CoversFunction( 'wp_verify_fast_hash' )]
class Tests_Functions_wpVerifyFastHash extends WP_UnitTestCase {

	/**
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '21022' )]
	public function test_wp_verify_fast_hash_verifies_hash() {
		$password = 'password';

		$hash = wp_fast_hash( $password );

		$this->assertTrue( wp_verify_fast_hash( $password, $hash ) );
	}

	/**
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '21022' )]
	public function test_wp_verify_fast_hash_fails_unprefixed_hash() {
		$password = 'password';

		$hash = wp_fast_hash( $password );

		$this->assertFalse( wp_verify_fast_hash( $password, substr( $hash, 9 ) ) );
	}

	/**
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '21022' )]
	public function test_wp_verify_fast_hash_fails_partial_hash() {
		$password = 'password';

		$hash = wp_fast_hash( $password );

		$this->assertFalse( wp_verify_fast_hash( $password, substr( $hash, 0, -3 ) ) );
	}

	/**
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '21022' )]
	public function test_wp_verify_fast_hash_verifies_phpass_hash() {
		require_once ABSPATH . WPINC . '/class-phpass.php';

		$password = 'password';

		$hash = ( new PasswordHash( 8, true ) )->HashPassword( $password );

		$this->assertTrue( wp_verify_fast_hash( $password, $hash ) );
	}
}
