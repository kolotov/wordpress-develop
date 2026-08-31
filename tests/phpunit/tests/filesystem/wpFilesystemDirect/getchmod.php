<?php
/**
 * Tests for the WP_Filesystem_Direct::getchmod() method.
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
#[\PHPUnit\Framework\Attributes\CoversMethod( WP_Filesystem_Direct::class, 'getchmod' )]
class Tests_Filesystem_WpFilesystemDirect_Getchmod extends WP_Filesystem_Direct_UnitTestCase {

	/**
	 * Tests that `WP_Filesystem_Direct::getchmod()` returns
	 * the permissions for a path that exists.
	 *
	 *
	 *
	 * @param string $path The path.
	 */
	#[\PHPUnit\Framework\Attributes\DataProvider( 'data_paths_that_exist' )]
	#[\PHPUnit\Framework\Attributes\Ticket( '57774' )]
	public function test_should_get_chmod_for_a_path_that_exists( $path ) {
		$actual = self::$filesystem->getchmod( self::$file_structure['test_dir']['path'] . $path );
		$this->assertNotSame( '', $actual );
	}

	/**
	 * Tests that `WP_Filesystem_Direct::getchmod()` returns
	 * "0" for a path that does not exist.
	 *
	 *
	 *
	 * @param string $path The path.
	 */
	#[\PHPUnit\Framework\Attributes\DataProvider( 'data_paths_that_do_not_exist' )]
	#[\PHPUnit\Framework\Attributes\Ticket( '57774' )]
	#[\PHPUnit\Framework\Attributes\Ticket( '64426' )]
	public function test_should_return_zero_for_a_path_that_does_not_exist( $path ) {
		$actual = self::$filesystem->getchmod( self::$file_structure['test_dir']['path'] . $path );
		$this->assertSame( '0', $actual );
	}
}
