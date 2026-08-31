<?php
/**
 * Tests for the wp_enqueue_view_transitions_admin_css() function.
 *
 * @package WordPress
 * @subpackage View Transitions
 */

/**
 */
#[\PHPUnit\Framework\Attributes\Group( 'view-transitions' )]
#[\PHPUnit\Framework\Attributes\CoversFunction( 'wp_enqueue_view_transitions_admin_css' )]
class Tests_View_Transitions_wpEnqueueViewTransitionsAdminCss extends WP_UnitTestCase {

	private ?WP_Styles $original_wp_styles = null;

	public function set_up() {
		global $wp_styles;

		parent::set_up();
		$this->original_wp_styles = $wp_styles;
	}

	public function tear_down() {
		global $wp_styles;

		$wp_styles = $this->original_wp_styles;
		parent::tear_down();
	}

	/**
	 * Tests that the hook for enqueuing admin view transitions CSS is set up.
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '64470' )]
	public function test_hook() {
		$this->assertSame( 10, has_action( 'admin_enqueue_scripts', 'wp_enqueue_view_transitions_admin_css' ) );
	}

	/**
	 * Tests that the admin view transitions style handle includes the inline CSS.
	 *
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '64470' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'wp_get_view_transitions_admin_css' )]
	public function test_inline_css_included() {
		$after_data = wp_styles()->get_data( 'wp-view-transitions-admin', 'after' );
		$this->assertIsArray( $after_data, 'Expected `after` data to be an array.' );
		$css = wp_get_view_transitions_admin_css();
		$this->assertStringContainsString( '@view-transition', $css );
		$this->assertContains( $css, $after_data );
	}

	/**
	 * Tests enqueuing admin view transitions CSS.
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '64470' )]
	public function test_wp_enqueue_view_transitions_admin_css() {
		$this->assertFalse( wp_style_is( 'wp-view-transitions-admin' ) );

		wp_enqueue_view_transitions_admin_css();
		$this->assertTrue( wp_style_is( 'wp-view-transitions-admin' ) );
	}
}
