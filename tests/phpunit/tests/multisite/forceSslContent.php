<?php
/**
 * Test cases for the `force_ssl_content()` function.
 *
 * @since 6.9.0
 *
 *
 */
#[\PHPUnit\Framework\Attributes\Group( 'functions' )]
#[\PHPUnit\Framework\Attributes\Group( 'ms-required' )]
#[\PHPUnit\Framework\Attributes\Group( 'multisite' )]
#[\PHPUnit\Framework\Attributes\CoversFunction( 'force_ssl_content' )]
class Tests_Functions_ForceSslContent extends WP_UnitTestCase {

	public function set_up() {
		parent::set_up();
		// Reset the `$forced_content` static variable before each test.
		force_ssl_content( false );
	}

	/**
	 * Tests that force_ssl_content() returns expected values based on various inputs.
	 *
	 *
	 * @param mixed $input    The input value to test.
	 * @param bool  $expected The expected result for subsequent calls.
	 */
	#[\PHPUnit\Framework\Attributes\DataProvider( 'data_force_ssl_content' )]
	public function test_force_ssl_content( $input, $expected ) {
		// The first call always returns the previous value.
		$this->assertFalse( force_ssl_content( $input ), 'First call did not return the expected value' );

		// Call again to check subsequent behavior.
		$this->assertSame( $expected, force_ssl_content( $input ), 'Subsequent call did not return the expected value' );
	}

	/**
	 * Data provider for testing force_ssl_content().
	 *
	 * @return array[]
	 */
	public static function data_force_ssl_content() {
		return array(
			'default'          => array( null, false ),
			'true'             => array( true, true ),
			'false'            => array( false, false ),
			'non-empty string' => array( 'some string', true ),
			'empty string'     => array( '', false ),
			'integer 1'        => array( 1, true ),
			'integer 0'        => array( 0, false ),
		);
	}
}
