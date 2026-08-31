<?php
/**
 * Tests for the WP_Filesystem_Direct::is_writable() method.
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
#[\PHPUnit\Framework\Attributes\CoversMethod( WP_Filesystem_Direct::class, 'is_writable' )]
class Tests_Filesystem_WpFilesystemDirect_IsWritable extends WP_Filesystem_Direct_UnitTestCase {

	/**
	 * Tests that `WP_Filesystem_Direct::is_writable()` determines that
	 * a path is writable.
	 *
	 *
	 *
	 * @param string $path The path.
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '57774' )]
	#[\PHPUnit\Framework\Attributes\DataProvider( 'data_paths_that_exist' )]
	public function test_should_determine_that_a_path_is_writable( $path ) {
		$this->assertTrue( self::$filesystem->is_writable( self::$file_structure['test_dir']['path'] . $path ) );
	}

	/**
	 * Tests that `WP_Filesystem_Direct::is_writable()` determines that
	 * a path is not writable.
	 *
	 *
	 *
	 * @param string $path The path.
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '57774' )]
	#[\PHPUnit\Framework\Attributes\DataProvider( 'data_paths_that_do_not_exist' )]
	public function test_should_determine_that_a_path_is_not_writable( $path ) {
		$this->assertFalse( self::$filesystem->is_writable( self::$file_structure['test_dir']['path'] . $path ) );
	}
}
