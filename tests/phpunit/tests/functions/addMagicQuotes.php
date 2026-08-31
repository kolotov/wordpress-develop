<?php

/**
 *
 */
#[\PHPUnit\Framework\Attributes\Group( 'formatting' )]
#[\PHPUnit\Framework\Attributes\Group( 'functions' )]
#[\PHPUnit\Framework\Attributes\CoversFunction( 'add_magic_quotes' )]
class Tests_Functions_AddMagicQuotes extends WP_UnitTestCase {

	/**
	 *
	 *
	 * @param array $test_array Test value.
	 * @param array $expected   Expected return value.
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '48605' )]
	#[\PHPUnit\Framework\Attributes\DataProvider( 'data_add_magic_quotes' )]
	public function test_add_magic_quotes( $test_array, $expected ) {
		$this->assertSame( $expected, add_magic_quotes( $test_array ) );
	}

	/**
	 * Data provider for test_add_magic_quotes().
	 *
	 * @return array[] Test parameters {
	 *     @type array $test_array Test value.
	 *     @type array $expected   Expected return value.
	 * }
	 */
	public static function data_add_magic_quotes() {
		return array(
			array(
				array(
					'sample string',
					52,
					true,
					false,
					null,
					"This is a 'string'",
					array(
						1,
						false,
						true,
						'This is "another" string',
					),
				),
				array(
					'sample string',
					52,
					true,
					false,
					null,
					"This is a \'string\'",
					array(
						1,
						false,
						true,
						'This is \"another\" string',
					),
				),
			),
		);
	}
}
