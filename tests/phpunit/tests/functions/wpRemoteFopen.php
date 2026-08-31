<?php
/**
 *
 */
#[\PHPUnit\Framework\Attributes\Group( 'http' )]
#[\PHPUnit\Framework\Attributes\Group( 'external-http' )]
#[\PHPUnit\Framework\Attributes\Group( 'functions' )]
#[\PHPUnit\Framework\Attributes\CoversFunction( 'wp_remote_fopen' )]
class Tests_Functions_wpRemoteFopen extends WP_UnitTestCase {

	/**
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '48845' )]
	public function test_wp_remote_fopen_empty() {
		$this->assertFalse( wp_remote_fopen( '' ) );
	}

	/**
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '48845' )]
	public function test_wp_remote_fopen_bad_url() {
		$this->assertFalse( wp_remote_fopen( 'wp.com' ) );
	}

	/**
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '48845' )]
	public function test_wp_remote_fopen() {
		// This URL gives a direct 200 response.
		$url      = 'https://s.w.org/screenshots/3.9/dashboard.png';
		$response = wp_remote_fopen( $url );

		$this->assertIsString( $response );
		$this->assertSame( 153204, strlen( $response ) );
	}
}
