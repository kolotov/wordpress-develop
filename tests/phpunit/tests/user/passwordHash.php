<?php

/**
 * Tests for the PasswordHash external library.
 *
 * PasswordHash is no longer used to hash user passwords or security keys, but it is still used to
 * hash post passwords and as a fallback to verify old passwords that were hashed by phpass. The
 * library therefore needs to remain compatible with the latest versions of PHP.
 *
 */
#[\PHPUnit\Framework\Attributes\CoversNothing]
class Tests_User_PasswordHash extends WP_UnitTestCase {

	public static function set_up_before_class() {
		parent::set_up_before_class();

		require_once ABSPATH . WPINC . '/class-phpass.php';
	}

	/**
	 * Tests that PasswordHash::gensalt_blowfish() does not throw a deprecation notice on PHP 8.1.
	 *
	 * The notice that we should not see:
	 * `Deprecated: Implicit conversion from float to int loses precision`.
	 *
	 *
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '56340' )]
	#[\PHPUnit\Framework\Attributes\RequiresPhp( '>= 8.1.0' )]
	#[\PHPUnit\Framework\Attributes\DoesNotPerformAssertions]
	public function test_gensalt_blowfish_should_not_throw_deprecation_notice_on_php81() {
		$hasher = new PasswordHash( 8, true );
		$hasher->gensalt_blowfish( 'a password string' );
	}
}
