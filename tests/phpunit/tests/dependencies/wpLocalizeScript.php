<?php
#[\PHPUnit\Framework\Attributes\Group( 'dependencies' )]
#[\PHPUnit\Framework\Attributes\Group( 'scripts' )]
#[\PHPUnit\Framework\Attributes\CoversFunction( 'wp_localize_script' )]
class Tests_Dependencies_wpLocalizeScript extends WP_UnitTestCase {
	/**
	 * @var WP_Scripts
	 */
	protected $old_wp_scripts;

	public function set_up() {
		parent::set_up();

		$this->old_wp_scripts  = $GLOBALS['wp_scripts'] ?? null;
		$GLOBALS['wp_scripts'] = null;
	}

	public function tear_down() {
		$GLOBALS['wp_scripts'] = $this->old_wp_scripts;
		parent::tear_down();
	}

	/**
	 * Verifies that wp_localize_script() works if global has not been initialized yet.
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '60862' )]
	public function test_wp_localize_script_works_before_enqueue_script() {
		$this->assertTrue(
			wp_localize_script(
				'wp-util',
				'salcodeExample',
				array(
					'answerToTheUltimateQuestionOfLifeTheUniverseAndEverything' => 42,
				)
			)
		);
	}

	/**
	 * Verifies that wp_localize_script() outputs safe JSON whe harmful data is provided.
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '63851' )]
	public function test_wp_localize_script_outputs_safe_json() {
		$path     = '/test.js';
		$base_url = site_url( $path );

		wp_enqueue_script( 'test-script', $path, array(), null );
		wp_localize_script( 'test-script', 'testData', array( '<!--' => '<script>' ) );

		$output = get_echo( 'wp_print_scripts' );

		$expected  = "<script id=\"test-script-js-extra\">\nvar testData = {\"\\u003C!--\":\"\\u003Cscript\\u003E\"};\n//# sourceURL=test-script-js-extra\n</script>\n";
		$expected .= "<script src=\"{$base_url}\" id=\"test-script-js\"></script>\n";

		$this->assertEqualHTML( $expected, $output );
	}
}
