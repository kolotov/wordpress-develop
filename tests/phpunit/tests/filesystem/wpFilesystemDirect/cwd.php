<?php
/**
 * Tests for the WP_Filesystem_Direct::cwd() method.
 *
 * @package WordPress
 */

/**
 *
 */
#[\PHPUnit\Framework\Attributes\Group( 'admin' )]
#[\PHPUnit\Framework\Attributes\Group( 'filesystem' )]
#[\PHPUnit\Framework\Attributes\Group( 'filesystem-direct' )]
#[\PHPUnit\Framework\Attributes\CoversMethod( WP_Filesystem_Direct::class, 'cwd' )]
class Tests_Filesystem_WpFilesystemDirect_Cwd extends WP_Filesystem_Direct_UnitTestCase {

	/**
	 * Tests that `WP_Filesystem_Direct::cwd()` returns the current
	 * working directory.
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '57774' )]
	public function test_should_get_current_working_directory() {
		$this->assertSame( wp_normalize_path( dirname( ABSPATH ) ), wp_normalize_path( self::$filesystem->cwd() ) );
	}
}
