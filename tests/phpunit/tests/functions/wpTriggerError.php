<?php

/**
 * Test cases for the `wp_trigger_error()` function.
 *
 * @since 6.4.0
 *
 *
 */
#[\PHPUnit\Framework\Attributes\Group( 'functions' )]
#[\PHPUnit\Framework\Attributes\CoversFunction( 'wp_trigger_error' )]
class Tests_Functions_WpTriggerError extends WP_UnitTestCase {

	/**
	 *
	 *
	 * @param string $function_name    The function name to test.
	 * @param string $message          The message to test.
	 * @param string $expected_message The expected error message.
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '57686' )]
	#[\PHPUnit\Framework\Attributes\DataProvider( 'data_should_trigger_error' )]
	public function test_should_throw_exception( $function_name, $message, $expected_message ) {
		$this->expectException( WP_Exception::class );
		$this->expectExceptionMessage( $expected_message );

		wp_trigger_error( $function_name, $message, E_USER_ERROR );
	}

	/**
	 *
	 *
	 * @param string $function_name    The function name to test.
	 * @param string $message          The message to test.
	 * @param string $expected_message The expected error message.
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '57686' )]
	#[\PHPUnit\Framework\Attributes\DataProvider( 'data_should_trigger_error' )]
	public function test_should_trigger_warning( $function_name, $message, $expected_message ) {
		$errors = array();
		set_error_handler(
			static function ( int $severity, string $message ) use ( &$errors ): bool {
				$errors[] = compact( 'severity', 'message' );
				return true;
			},
			E_USER_WARNING
		);

		try {
			wp_trigger_error( $function_name, $message, E_USER_WARNING );
		} finally {
			restore_error_handler();
		}

		$this->assertSame(
			array(
				array(
					'severity' => E_USER_WARNING,
					'message'  => $expected_message,
				),
			),
			$errors
		);
	}

	/**
	 *
	 *
	 * @param string $function_name    The function name to test.
	 * @param string $message          The message to test.
	 * @param string $expected_message The expected error message.
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '57686' )]
	#[\PHPUnit\Framework\Attributes\DataProvider( 'data_should_trigger_error' )]
	public function test_should_trigger_notice( $function_name, $message, $expected_message ) {
		$notices = array();
		set_error_handler(
			static function ( int $severity, string $message ) use ( &$notices ): bool {
				$notices[] = compact( 'severity', 'message' );
				return true;
			},
			E_USER_NOTICE
		);

		try {
			wp_trigger_error( $function_name, $message );
		} finally {
			restore_error_handler();
		}

		$this->assertSame(
			array(
				array(
					'severity' => E_USER_NOTICE,
					'message'  => $expected_message,
				),
			),
			$notices
		);
	}

	/**
	 *
	 *
	 * @param string $function_name    The function name to test.
	 * @param string $message          The message to test.
	 * @param string $expected_message The expected error message.
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '57686' )]
	#[\PHPUnit\Framework\Attributes\DataProvider( 'data_should_trigger_error' )]
	public function test_should_trigger_deprecation( $function_name, $message, $expected_message ) {
		$deprecations = array();
		set_error_handler(
			static function ( int $severity, string $message ) use ( &$deprecations ): bool {
				$deprecations[] = compact( 'severity', 'message' );
				return true;
			},
			E_USER_DEPRECATED
		);

		try {
			wp_trigger_error( $function_name, $message, E_USER_DEPRECATED );
		} finally {
			restore_error_handler();
		}

		$this->assertSame(
			array(
				array(
					'severity' => E_USER_DEPRECATED,
					'message'  => $expected_message,
				),
			),
			$deprecations
		);
	}

	/**
	 * Data provider.
	 *
	 * @return array[]
	 */
	public static function data_should_trigger_error() {
		return array(
			'function name and message are given'          => array(
				'function_name'    => 'some_function',
				'message'          => 'expected the function name and message',
				'expected_message' => 'some_function(): expected the function name and message',
			),
			'message is given'                             => array(
				'function_name'    => '',
				'message'          => 'expect only the message',
				'expected_message' => 'expect only the message',
			),
			'function name is given'                       => array(
				'function_name'    => 'some_function',
				'message'          => '',
				'expected_message' => 'some_function(): ',
			),
			'allowed HTML elements are present in message' => array(
				'function_name'    => 'some_function',
				'message'          => '<strong>expected</strong> the function name and message',
				'expected_message' => 'some_function(): <strong>expected</strong> the function name and message',
			),
			'HTML links are present in message'            => array(
				'function_name'    => 'some_function',
				'message'          => '<a href="https://example.com">expected the function name and message</a>',
				'expected_message' => 'some_function(): <a href="https://example.com">expected the function name and message</a>',
			),
			'disallowed HTML elements are present in message' => array(
				'function_name'    => 'some_function',
				'message'          => '<script>alert("expected the function name and message")</script>',
				'expected_message' => 'some_function(): alert("expected the function name and message")',
			),
		);
	}
}
