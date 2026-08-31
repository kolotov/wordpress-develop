<?php

/**
 *
 */
#[\PHPUnit\Framework\Attributes\Group( 'formatting' )]
#[\PHPUnit\Framework\Attributes\CoversNothing]
class Tests_Formatting_LikeEscape extends WP_UnitTestCase {
	/**
	 * @expectedDeprecated like_escape
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '10041' )]
	public function test_like_escape() {

		$inputs   = array(
			'howdy%',              // Single percent.
			'howdy_',              // Single underscore.
			'howdy\\',             // Single slash.
			'howdy\\howdy%howdy_', // The works.
		);
		$expected = array(
			'howdy\\%',
			'howdy\\_',
			'howdy\\',
			'howdy\\howdy\\%howdy\\_',
		);

		foreach ( $inputs as $key => $input ) {
			$this->assertSame( $expected[ $key ], like_escape( $input ) );
		}
	}
}
