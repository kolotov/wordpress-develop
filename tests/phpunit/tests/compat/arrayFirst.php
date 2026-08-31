<?php

/**
 *
 */
#[\PHPUnit\Framework\Attributes\Group( 'compat' )]
#[\PHPUnit\Framework\Attributes\CoversFunction( 'array_first' )]
class Tests_Compat_arrayFirst extends WP_UnitTestCase {

	/**
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '63853' )]
	public function test_array_first_availability(): void {
		$this->assertTrue( function_exists( 'array_first' ) );
	}

	/**
	 *
	 *
	 * @param mixed $expected The value extracted from the given array.
	 * @param array $arr      The array to get the first value from.
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '63853' )]
	#[\PHPUnit\Framework\Attributes\DataProvider( 'data_array_first' )]
	public function test_array_first( $expected, $arr ): void {
		$this->assertSame( $expected, array_first( $arr ) );
	}


	/**
	 * Data provider.
	 *
	 * @return array[]
	 */
	public static function data_array_first(): array {
		$obj = new \stdClass();
		return array(
			'string values'        => array(
				'expected' => 'a',
				'arr'      => array( 'a', 'b', 'c' ),
			),
			'associative array'    => array(
				'expected' => 10,
				'arr'      => array(
					'foo' => 10,
					'bar' => 20,
				),
			),
			'empty array'          => array(
				'expected' => null,
				'arr'      => array(),
			),
			'single element array' => array(
				'expected' => 42,
				'arr'      => array( 42 ),
			),
			'null values'          => array(
				'expected' => null,
				'arr'      => array( null, 'b', 'c' ),
			),
			'objects'              => array(
				'expected' => $obj,
				'arr'      => array(
					$obj,
					1,
					2,
				),
			),
			'boolean values'       => array(
				'expected' => false,
				'arr'      => array( false, true, 1, 2, 3 ),
			),
		);
	}

	/**
	 * Test that array_first() returns the pointer is not the first element.
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '63853' )]
	public function test_array_first_with_end_pointer() {
		$arr = array(
			'key1' => 'val1',
			'key2' => 'val2',
		);
		// change the pointer to the last element
		end( $arr );

		$val = array_first( $arr );
		$this->assertSame( 'val2', current( $arr ) );
		$this->assertSame( 'val1', $val );
	}
}
