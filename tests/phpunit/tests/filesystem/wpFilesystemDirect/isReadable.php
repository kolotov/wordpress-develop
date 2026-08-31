<?php
/**
 * Tests for the WP_Filesystem_Direct::is_readable() method.
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
#[\PHPUnit\Framework\Attributes\CoversMethod( WP_Filesystem_Direct::class, 'is_readable' )]
class Tests_Filesystem_WpFilesystemDirect_IsReadable extends WP_Filesystem_Direct_UnitTestCase {

	/**
	 * Tests that `WP_Filesystem_Direct::is_readable()` determines that
	 * a path is readable.
	 *
	 *
	 *
	 * @param string $path The path.
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '57774' )]
	#[\PHPUnit\Framework\Attributes\DataProvider( 'data_paths_that_exist' )]
	public function test_should_determine_that_a_path_is_readable( $path ) {
		$this->assertTrue( self::$filesystem->is_readable( self::$file_structure['test_dir']['path'] . $path ) );
	}

	/**
	 * Tests that `WP_Filesystem_Direct::is_readable()` determines that
	 * a path is not readable.
	 *
	 *
	 *
	 * @param string $path The path.
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '57774' )]
	#[\PHPUnit\Framework\Attributes\DataProvider( 'data_paths_that_do_not_exist' )]
	public function test_should_determine_that_a_path_is_not_readable( $path ) {
		$this->assertFalse( self::$filesystem->is_readable( self::$file_structure['test_dir']['path'] . $path ) );
	}
}
