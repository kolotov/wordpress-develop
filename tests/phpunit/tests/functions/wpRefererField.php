<?php

/**
 * Tests for the wp_referer_field() function.
 *
 * @since 6.1.0
 *
 *
 */
#[\PHPUnit\Framework\Attributes\Group( 'functions' )]
#[\PHPUnit\Framework\Attributes\CoversFunction( 'wp_referer_field' )]
class Tests_Functions_wpRefererField extends WP_UnitTestCase {

	/**
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '55578' )]
	public function test_wp_referer_field() {
		$_SERVER['REQUEST_URI'] = '/test/';

		wp_referer_field();
		$this->expectOutputString( '<input type="hidden" name="_wp_http_referer" value="/test/" />' );
	}

	/**
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '55578' )]
	public function test_wp_referer_field_return() {
		$_SERVER['REQUEST_URI'] = '/test/';

		$this->assertSame( '<input type="hidden" name="_wp_http_referer" value="/test/" />', wp_referer_field( false ) );
	}

	/**
	 * Tests that the display argument is respected.
	 *
	 *
	 *
	 * @param mixed $display Whether to echo or return the referer field.
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '54106' )]
	#[\PHPUnit\Framework\Attributes\DataProvider( 'data_wp_referer_field_should_respect_display_arg' )]
	public function test_wp_referer_field_should_respect_display_arg( $display ) {
		$actual = $display ? get_echo( 'wp_referer_field' ) : wp_referer_field( false );

		$this->assertSame( '<input type="hidden" name="_wp_http_referer" value="" />', $actual );
	}

	/**
	 * Data provider for test_wp_referer_field_should_respect_display_arg().
	 *
	 * @return array[]
	 */
	public static function data_wp_referer_field_should_respect_display_arg() {
		return array(
			'true'         => array( true ),
			'(int) 1'      => array( 1 ),
			'(string) "1"' => array( '1' ),
			'false'        => array( false ),
			'null'         => array( null ),
			'(int) 0'      => array( 0 ),
			'(string) "0"' => array( '0' ),
		);
	}

	/**
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '54106' )]
	public function test_wp_referer_field_with_referer() {
		$old_request_uri        = $_SERVER['REQUEST_URI'];
		$_SERVER['REQUEST_URI'] = 'edit.php?_wp_http_referer=edit.php';

		$actual = wp_referer_field( false );

		$_SERVER['REQUEST_URI'] = $old_request_uri;

		$this->assertSame( '<input type="hidden" name="_wp_http_referer" value="edit.php" />', $actual );
	}
}
