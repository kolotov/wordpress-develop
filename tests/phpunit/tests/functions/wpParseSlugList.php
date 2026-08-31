<?php

/**
 * Tests for the wp_parse_slug_list() function.
 *
 *
 */
#[\PHPUnit\Framework\Attributes\CoversFunction( 'wp_parse_slug_list' )]
#[\PHPUnit\Framework\Attributes\Group( 'functions' )]
class Tests_Functions_WpParseSlugList extends WP_UnitTestCase {

	/**
	 *
	 *
	 * @param mixed[]|string $input_list
	 * @param array<string> $expected
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '35582' )]
	#[\PHPUnit\Framework\Attributes\Ticket( '60217' )]
	#[\PHPUnit\Framework\Attributes\DataProvider( 'data_wp_parse_slug_list' )]
	#[\PHPUnit\Framework\Attributes\DataProvider( 'data_unexpected_input' )]
	public function test_wp_parse_slug_list( $input_list, array $expected ): void {
		$parsed_list = wp_parse_slug_list( $input_list );
		$this->assertThat(
			$parsed_list,
			$this->callback(
				static fn ( array $arr ) => array_all(
					$arr,
					static fn ( $v ) => is_string( $v )
				)
			),
			'Array should contain only strings.'
		);
		$this->assertSame( $expected, $parsed_list );
	}

	/**
	 * Data provider.
	 *
	 * @return array<string, array{ input_list: mixed[]|string, expected: array<string> }>
	 */
	public static function data_wp_parse_slug_list(): array {
		return array(
			'regular'                    => array(
				'input_list' => 'apple,banana,carrot,dog',
				'expected'   => array( 'apple', 'banana', 'carrot', 'dog' ),
			),
			'double comma'               => array(
				'input_list' => 'apple, banana,,carrot,dog',
				'expected'   => array( 'apple', 'banana', 'carrot', 'dog' ),
			),
			'duplicate slug in a string' => array(
				'input_list' => 'apple,banana,carrot,carrot,dog',
				'expected'   => array(
					0 => 'apple',
					1 => 'banana',
					2 => 'carrot',
					4 => 'dog',
				),
			),
			'duplicate slug in an array' => array(
				'input_list' => array( 'apple', 'banana', 'carrot', 'carrot', 'dog' ),
				'expected'   => array(
					0 => 'apple',
					1 => 'banana',
					2 => 'carrot',
					4 => 'dog',
				),
			),
			'string with spaces'         => array(
				'input_list' => 'apple banana carrot dog',
				'expected'   => array( 'apple', 'banana', 'carrot', 'dog' ),
			),
			'array with spaces'          => array(
				'input_list' => array( 'apple ', 'banana carrot', 'd o g' ),
				'expected'   => array( 'apple', 'banana-carrot', 'd-o-g' ),
			),
			'passed assoc array'         => array(
				'input_list' => array(
					'one'   => 'foo',
					'two'   => 'bar',
					'three' => 'baz',
				),
				'expected'   => array(
					'one'   => 'foo',
					'two'   => 'bar',
					'three' => 'baz',
				),
			),
		);
	}

	/**
	 * Data provider.
	 *
	 * @return array<string, array{ input_list: mixed[]|string, expected: array<string> }>
	 */
	public static function data_unexpected_input(): array {
		return array(
			'string with commas' => array(
				'input_list' => '1,2,string with spaces',
				'expected'   => array( '1', '2', 'string', 'with', 'spaces' ),
			),
			'array'              => array(
				'input_list' => array( '1', 2, 'string with spaces' ),
				'expected'   => array( '1', '2', 'string-with-spaces' ),
			),
			'unexpected string with spaces' => array(
				'input_list' => '1 2 string with spaces',
				'expected'   => array( '1', '2', 'string', 'with', 'spaces' ),
			),
			'unexpected array with spaces'  => array(
				'input_list' => array( '1 2 string with spaces' ),
				'expected'   => array( '1-2-string-with-spaces' ),
			),
			'string with html'   => array(
				'input_list' => '1 2 string <strong>with</strong> <h1>HEADING</h1>',
				'expected'   => array( '1', '2', 'string', 'with', 'heading' ),
			),
			'array with html'    => array(
				'input_list' => array( '1', 2, 'string <strong>with</strong> <h1>HEADING</h1>' ),
				'expected'   => array( '1', '2', 'string-with-heading' ),
			),
			'array with null'    => array(
				'input_list' => array( 1, 2, null ),
				'expected'   => array( '1', '2' ),
			),
			'array with false'   => array(
				'input_list' => array( 1, 2, false ),
				'expected'   => array( '1', '2', '' ),
			),
			'array with array'   => array(
				'input_list' => array( 1, array(), 2 ),
				'expected'   => array(
					0 => '1',
					2 => '2',
				),
			),
			'array with tag'     => array(
				'input_list' => array( 1, '<br>', 2 ),
				'expected'   => array( '1', '', '2' ),
			),
		);
	}
}
