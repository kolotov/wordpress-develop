<?php

/**
 * Tests for is_login().
 *
 *
 */
#[\PHPUnit\Framework\Attributes\Group( 'load' )]
#[\PHPUnit\Framework\Attributes\CoversFunction( 'is_login' )]
class Tests_Load_IsLogin extends WP_UnitTestCase {

	/**
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '19898' )]
	public function test_is_login() {
		$this->assertFalse( is_login() );

		$_SERVER['SCRIPT_NAME'] = '/wp-login.php';

		$this->assertTrue( is_login() );
	}
}
