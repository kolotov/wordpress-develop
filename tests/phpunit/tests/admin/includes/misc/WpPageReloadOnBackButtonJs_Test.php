<?php

/**
 *
 */
#[\PHPUnit\Framework\Attributes\Group( 'admin' )]
#[\PHPUnit\Framework\Attributes\CoversFunction( 'wp_page_reload_on_back_button_js' )]
class Tests_Admin_Includes_Misc_WpPageReloadOnBackButtonJs_Test extends WP_UnitTestCase {

	/**
	 * Tests wp_page_reload_on_back_button_js().
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '65193' )]
	public function test_wp_page_reload_on_back_button_js() {
		ob_start();
		wp_page_reload_on_back_button_js();
		$output = ob_get_clean();

		$this->assertStringContainsString( '<script>', $output );
		$this->assertStringContainsString( 'performance.navigation.type === 2', $output );
		$this->assertStringContainsString( 'document.location.reload( true )', $output );
		$this->assertStringContainsString( '</script>', $output );
	}
}
