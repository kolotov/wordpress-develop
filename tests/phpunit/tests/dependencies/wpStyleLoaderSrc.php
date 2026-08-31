<?php

/**
 * Test wp_style_loader_src().
 *
 *
 */
#[\PHPUnit\Framework\Attributes\Group( 'dependencies' )]
#[\PHPUnit\Framework\Attributes\Group( 'scripts' )]
#[\PHPUnit\Framework\Attributes\CoversFunction( 'wp_style_loader_src' )]
class Tests_Dependencies_wpStyleLoaderSrc extends WP_UnitTestCase {

	/**
	 * Tests that PHP warnings are not thrown when wp_style_loader_src() is called
	 * before the `$_wp_admin_css_colors` global is set.
	 *
	 * The warnings that we should not see:
	 * `Warning: Trying to access array offset on null`.
	 * `Warning: Attempt to read property "url" on null`.
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '61302' )]
	public function test_without_wp_admin_css_colors_global() {
		$this->assertFalse( wp_style_loader_src( '', 'colors' ) );
	}
}
