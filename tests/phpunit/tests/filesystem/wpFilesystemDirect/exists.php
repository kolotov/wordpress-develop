<?php
/**
 * Tests for the WP_Filesystem_Direct::exists() method.
 *
 * @package WordPress
 */

/**
 *
 */
#[\PHPUnit\Framework\Attributes\Group( 'admin' )]
#[\PHPUnit\Framework\Attributes\Group( 'filesystem' )]
#[\PHPUnit\Framework\Attributes\Group( 'filesystem-direct' )]
#[\PHPUnit\Framework\Attributes\CoversMethod( WP_Filesystem_Direct::class, 'exists' )]
class Tests_Filesystem_WpFilesystemDirect_Exists extends WP_Filesystem_Direct_UnitTestCase {

	/**
	 * Tests that `WP_Filesystem_Direct::exists()` determines that
	 * a path exists.
	 *
	 *
	 *
	 * @param string $path The path to check.
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '57774' )]
	#[\PHPUnit\Framework\Attributes\DataProvider( 'data_paths_that_exist' )]
	public function test_should_determine_that_a_path_exists( $path ) {
		$this->assertTrue( self::$filesystem->exists( self::$file_structure['test_dir']['path'] . $path ) );
	}

	/**
	 * Tests that `WP_Filesystem_Direct::exists()` determines that
	 * a path does not exist.
	 *
	 *
	 *
	 * @param string $path The path to check.
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '57774' )]
	#[\PHPUnit\Framework\Attributes\DataProvider( 'data_paths_that_do_not_exist' )]
	public function test_should_determine_that_a_path_does_not_exist( $path ) {
		$this->assertFalse( self::$filesystem->exists( self::$file_structure['test_dir']['path'] . $path ) );
	}
}
