<?php

#[\PHPUnit\Framework\Attributes\Group( 'formatting' )]
#[\PHPUnit\Framework\Attributes\CoversFunction( 'wp_slash' )]
class Tests_Formatting_wpSlash extends WP_UnitTestCase {

	/**
	 *
	 *
	 * @param string $value
	 * @param string $expected
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '42195' )]
	#[\PHPUnit\Framework\Attributes\DataProvider( 'data_wp_slash' )]
	public function test_wp_slash( $value, $expected ) {
		$this->assertSame( $expected, wp_slash( $value ) );
	}

	/**
	 * Data provider for test_wp_slash().
	 *
	 * @return array {
	 *     @type array {
	 *         @type mixed  $value    The value passed to wp_slash().
	 *         @type string $expected The expected output of wp_slash().
	 *     }
	 * }
	 */
	public static function data_wp_slash() {
		return array(
			array( 123, 123 ),
			array( 123.4, 123.4 ),
			array( true, true ),
			array( false, false ),
			array(
				array(
					'hello',
					null,
					'"string"',
					125.41,
				),
				array(
					'hello',
					null,
					'\"string\"',
					125.41,
				),
			),
			array( "first level 'string'", "first level \'string\'" ),
		);
	}

	#[\PHPUnit\Framework\Attributes\Ticket( '24106' )]
	public function test_adds_slashes() {
		$old = "I can't see, isn't that it?";
		$new = "I can\'t see, isn\'t that it?";
		$this->assertSame( $new, wp_slash( $old ) );
		$this->assertSame( "I can\\\\\'t see, isn\\\\\'t that it?", wp_slash( $new ) );
		$this->assertSame( array( 'a' => $new ), wp_slash( array( 'a' => $old ) ) ); // Keyed array.
		$this->assertSame( array( $new ), wp_slash( array( $old ) ) ); // Non-keyed.
	}

	#[\PHPUnit\Framework\Attributes\Ticket( '24106' )]
	public function test_preserves_original_datatype() {

		$this->assertTrue( wp_slash( true ) );
		$this->assertFalse( wp_slash( false ) );
		$this->assertSame( 4, wp_slash( 4 ) );
		$this->assertSame( 'foo', wp_slash( 'foo' ) );
		$arr      = array(
			'a' => true,
			'b' => false,
			'c' => 4,
			'd' => 'foo',
		);
		$arr['e'] = $arr; // Add a sub-array.
		$this->assertSame( $arr, wp_slash( $arr ) ); // Keyed array.
		$this->assertSame( array_values( $arr ), wp_slash( array_values( $arr ) ) ); // Non-keyed.

		$obj = new stdClass();
		foreach ( $arr as $k => $v ) {
			$obj->$k = $v;
		}
		$this->assertSame( $obj, wp_slash( $obj ) );
	}

	#[\PHPUnit\Framework\Attributes\Ticket( '24106' )]
	public function test_add_even_more_slashes() {
		$old = 'single\\slash double\\\\slash triple\\\\\\slash';
		$new = 'single\\\\slash double\\\\\\\\slash triple\\\\\\\\\\\\slash';
		$this->assertSame( $new, wp_slash( $old ) );
		$this->assertSame( array( 'a' => $new ), wp_slash( array( 'a' => $old ) ) ); // Keyed array.
		$this->assertSame( array( $new ), wp_slash( array( $old ) ) ); // Non-keyed.
	}

	/**
	 * Tests that addslashes_gpc() returns the same result as wp_slash() for strings.
	 *
	 * @expectedDeprecated addslashes_gpc
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '64539' )]
	#[\PHPUnit\Framework\Attributes\CoversNothing]
	public function test_addslashes_gpc_matches_wp_slash_for_strings() {
		$input = "String with 'quotes' and \"double quotes\"";
		$this->assertSame( wp_slash( $input ), addslashes_gpc( $input ) );
	}

	/**
	 * Tests that addslashes_gpc() returns the same result as wp_slash() for arrays.
	 *
	 * @expectedDeprecated addslashes_gpc
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '64539' )]
	#[\PHPUnit\Framework\Attributes\CoversNothing]
	public function test_addslashes_gpc_matches_wp_slash_for_arrays() {
		$input = array(
			'field1' => "Value with 'apostrophe'",
			'field2' => 'Value with "quotes"',
			'field3' => 'user@example.com',
			'nested' => array(
				'key1' => 'Nested value with \\ backslash',
				'key2' => array( 'deeply', 'nested', 'array' ),
			),
		);

		$this->assertSame( wp_slash( $input ), addslashes_gpc( $input ) );
	}
}
