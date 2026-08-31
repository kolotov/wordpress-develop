<?php

/**
 * Tests specific to the directory size caching.
 *
 *
 */

#[\PHPUnit\Framework\Attributes\Group( 'functions' )]

#[\PHPUnit\Framework\Attributes\CoversFunction( 'clean_dirsize_cache' )]
class Tests_Functions_CleanDirsizeCache extends WP_UnitTestCase {

	/**
	 * Tests the handling of invalid data passed as the $path parameter.
	 *
	 *
	 *
	 * @param mixed  $path             Path input to use in the test.
	 * @param string $expected_message Expected notice message.
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '52241' )]
	#[\PHPUnit\Framework\Attributes\DataProvider( 'data_clean_dirsize_cache_with_invalid_inputs' )]
	public function test_clean_dirsize_cache_with_invalid_inputs( $path, $expected_message ) {
		$notices = array();
		set_error_handler(
			static function ( int $severity, string $message ) use ( &$notices ): bool {
				$notices[] = compact( 'severity', 'message' );
				return true;
			},
			E_USER_NOTICE
		);

		try {
			clean_dirsize_cache( $path );
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
	 * Data provider.
	 *
	 * @return array
	 */
	public static function data_clean_dirsize_cache_with_invalid_inputs() {
		return array(
			'null'         => array(
				'path'             => null,
				'expected_message' => '<code>clean_dirsize_cache()</code> only accepts a non-empty path string, received <code>NULL</code>.',
			),
			'bool false'   => array(
				'path'             => false,
				'expected_message' => '<code>clean_dirsize_cache()</code> only accepts a non-empty path string, received <code>boolean</code>.',
			),
			'empty string' => array(
				'path'             => '',
				'expected_message' => '<code>clean_dirsize_cache()</code> only accepts a non-empty path string, received <code>string</code>.',
			),
			'array'        => array(
				'path'             => array( '.', './second/path/' ),
				'expected_message' => '<code>clean_dirsize_cache()</code> only accepts a non-empty path string, received <code>array</code>.',
			),
		);
	}

	/**
	 * Tests the handling of a non-path text string passed as the $path parameter.
	 *
	 *
	 *
	 * @param string $path           Path input to use in the test.
	 * @param int    $expected_count Expected number of paths in the cache after cleaning.
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '52241' )]
	#[\PHPUnit\Framework\Attributes\DataProvider( 'data_clean_dirsize_cache_with_non_path_string' )]
	public function test_clean_dirsize_cache_with_non_path_string( $path, $expected_count ) {
		// Set the dirsize cache to our mock.
		set_transient( 'dirsize_cache', $this->mock_dirsize_cache_with_non_path_string() );

		clean_dirsize_cache( $path );

		$cache = get_transient( 'dirsize_cache' );
		$this->assertIsArray( $cache );
		$this->assertCount( $expected_count, $cache );
	}

	/**
	 * Data provider.
	 *
	 * @return array
	 */
	public static function data_clean_dirsize_cache_with_non_path_string() {
		return array(
			'single dot'                        => array(
				'path'           => '.',
				'expected_count' => 1,
			),
			'non-path'                          => array(
				'path'           => 'string',
				'expected_count' => 1,
			),
			'non-existent string, but non-path' => array(
				'path'           => 'doesnotexist',
				'expected_count' => 2,
			),
		);
	}

	private function mock_dirsize_cache_with_non_path_string() {
		return array(
			'.'      => array( 'size' => 50 ),
			'string' => array( 'size' => 42 ),
		);
	}

	/**
	 * Tests the behavior of the function when the transient doesn't exist.
	 *
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '52241' )]
	#[\PHPUnit\Framework\Attributes\Ticket( '53635' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'recurse_dirsize' )]
	public function test_recurse_dirsize_without_transient() {
		delete_transient( 'dirsize_cache' );

		$size = recurse_dirsize( DIR_TESTDATA . '/functions' );

		$this->assertGreaterThan( 10, $size );
	}

	/**
	 * Tests the behavior of the function when the transient does exist, but is not an array.
	 *
	 * In particular, this tests that no PHP TypeErrors are being thrown.
	 *
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '52241' )]
	#[\PHPUnit\Framework\Attributes\Ticket( '53635' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'recurse_dirsize' )]
	public function test_recurse_dirsize_with_invalid_transient() {
		set_transient( 'dirsize_cache', 'this is not a valid transient for dirsize cache' );

		$size = recurse_dirsize( DIR_TESTDATA . '/functions' );

		$this->assertGreaterThan( 10, $size );
	}
}
