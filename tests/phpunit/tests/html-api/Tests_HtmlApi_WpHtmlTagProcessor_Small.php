<?php
/**
 * Small regression tests covering WP_HTML_Tag_Processor functionality.
 *
 * @package WordPress
 * @subpackage HTML-API
 */
#[\PHPUnit\Framework\Attributes\Group( 'html-api' )]
#[\PHPUnit\Framework\Attributes\Small]
class Tests_HtmlApi_WpHtmlTagProcessor_Small extends WP_UnitTestCase {
	/**
	 * Test an infinite loop bugfix in incomplete script tag parsing.
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '61810' )]
	public function test_script_tag_processing_no_infinite_loop_final_dash() {
		$processor = new WP_HTML_Tag_Processor( '<script>-' );

		$this->assertFalse( $processor->next_tag() );
		$this->assertTrue( $processor->paused_at_incomplete_token() );
	}

	/**
	 * Test an infinite loop bugfix in incomplete script tag parsing.
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '61810' )]
	public function test_script_tag_processing_no_infinite_loop_final_left_angle_bracket() {
		$processor = new WP_HTML_Tag_Processor( '<script><' );

		$this->assertFalse( $processor->next_tag() );
		$this->assertTrue( $processor->paused_at_incomplete_token() );
	}
}
