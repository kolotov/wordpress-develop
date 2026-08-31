<?php

/**
 *
 */
#[\PHPUnit\Framework\Attributes\Group( 'compat' )]
#[\PHPUnit\Framework\Attributes\CoversFunction( 'array_last' )]
class Tests_Compat_arrayLast extends WP_UnitTestCase {

	/**
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '63853' )]
	public function test_array_last_availability(): void {
		$this->assertTrue( function_exists( 'array_last' ) );
	}

	/**
	 *
	 *
	 * @param mixed $expected The expected last value.
	 * @param array $arr      The array to get the last value from.
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '63853' )]
	#[\PHPUnit\Framework\Attributes\DataProvider( 'data_array_last' )]
	public function test_array_last( $expected, $arr ): void {
		$this->assertSame( $expected, array_last( $arr ) );
	}

	/**
	 * Data provider for array_last().
	 *
	 * @return array[]
	 */
	public static function data_array_last(): array {
		$obj = new \stdClass();
		return array(
			'string values'          => array(
				'expected' => 'c',
				'arr'      => array( 'a', 'b', 'c' ),
			),
			'associative array'      => array(
				'expected' => 20,
				'arr'      => array(
					'foo' => 10,
					'bar' => 20,
				),
			),
			'empty array'            => array(
				'expected' => null,
				'arr'      => array(),
			),
			'single element array'   => array(
				'expected' => 42,
				'arr'      => array( 42 ),
			),
			'null values'            => array(
				'expected' => null,
				'arr'      => array( 'a', 'b', null ),
			),
			'objects'                => array(
				'expected' => $obj,
				'arr'      => array(
					1,
					2,
					$obj,
				),
			),
			'boolean values'         => array(
				'expected' => false,
				'arr'      => array( true, false ),
			),
			'null values in between' => array(
				'expected' => 'c',
				'arr'      => array( 'a', null, 'b', 'c' ),
			),
			'empty string values'    => array(
				'expected' => '',
				'arr'      => array( 'a', 'b', '' ),
			),
		);
	}
}
