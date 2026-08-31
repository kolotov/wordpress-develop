<?php
/**
 * Tests for the WP_Filesystem_Direct::atime() method.
 *
 * @package WordPress
 */

require_once __DIR__ . '/base.php';

/**
 *
 */
#[\PHPUnit\Framework\Attributes\Group( 'admin' )]
#[\PHPUnit\Framework\Attributes\Group( 'filesystem' )]
#[\PHPUnit\Framework\Attributes\Group( 'filesystem-direct' )]
#[\PHPUnit\Framework\Attributes\CoversMethod( WP_Filesystem_Direct::class, 'atime' )]
class Tests_Filesystem_WpFilesystemDirect_Atime extends WP_Filesystem_Direct_UnitTestCase {

	/**
	 * Tests that `WP_Filesystem_Direct::atime()`
	 * returns an integer for a path that exists.
	 *
	 *
	 *
	 * @param string $path The path.
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '57774' )]
	#[\PHPUnit\Framework\Attributes\DataProvider( 'data_paths_that_exist' )]
	public function test_should_determine_accessed_time( $path ) {
		$path = self::$file_structure['test_dir']['path'] . $path;

		$this->assertIsInt( self::$filesystem->atime( $path ) );
	}

	/**
	 * Tests that `WP_Filesystem_Direct::atime()`
	 * returns false for a path that does not exist.
	 *
	 *
	 *
	 * @param string $path The path.
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '57774' )]
	#[\PHPUnit\Framework\Attributes\DataProvider( 'data_paths_that_do_not_exist' )]
	public function test_should_return_false_for_a_path_that_does_not_exist( $path ) {
		$path = self::$file_structure['test_dir']['path'] . $path;

		$this->assertFalse( self::$filesystem->atime( $path ) );
	}
}
