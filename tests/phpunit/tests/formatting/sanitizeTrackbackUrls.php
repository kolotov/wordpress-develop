<?php

/**
 *
 */
#[\PHPUnit\Framework\Attributes\Group( 'formatting' )]
#[\PHPUnit\Framework\Attributes\CoversFunction( 'sanitize_trackback_urls' )]
class Tests_Formatting_SanitizeTrackbackUrls extends WP_UnitTestCase {
	/**
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '21624' )]
	#[\PHPUnit\Framework\Attributes\DataProvider( 'data_sanitize_trackback_urls_with_multiple_urls' )]
	public function test_sanitize_trackback_urls_with_multiple_urls( $separator ) {
		$this->assertSame(
			"http://example.com\nhttp://example.org",
			sanitize_trackback_urls( "http://example.com{$separator}http://example.org" )
		);
	}

	public static function data_sanitize_trackback_urls_with_multiple_urls() {
		return array(
			array( "\r\n\t " ),
			array( "\r" ),
			array( "\n" ),
			array( "\t" ),
			array( ' ' ),
			array( '  ' ),
			array( "\n  " ),
			array( "\r\n" ),
		);
	}
}
