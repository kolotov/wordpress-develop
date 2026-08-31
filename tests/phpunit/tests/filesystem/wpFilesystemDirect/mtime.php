<?php
/**
 * Tests for the WP_Filesystem_Direct::mtime() method.
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
#[\PHPUnit\Framework\Attributes\CoversMethod( WP_Filesystem_Direct::class, 'mtime' )]
class Tests_Filesystem_WpFilesystemDirect_Mtime extends WP_Filesystem_Direct_UnitTestCase {

	/**
	 * Tests that `WP_Filesystem_Direct::mtime()` determines
	 * the mtime of a path.
	 *
	 *
	 *
	 * @param string $path The path.
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '57774' )]
	#[\PHPUnit\Framework\Attributes\DataProvider( 'data_paths_that_exist' )]
	public function test_should_determine_file_modified_time( $path ) {
		$result    = self::$filesystem->mtime( self::$file_structure['test_dir']['path'] . $path );
		$has_mtime = false !== $result;

		$this->assertTrue(
			$has_mtime,
			'The mtime was not determined.'
		);

		$this->assertIsInt(
			$result,
			'The mtime is not an integer.'
		);
	}

	/**
	 * Tests that `WP_Filesystem_Direct::mtime()` does not determine
	 * the mtime of a path.
	 *
	 *
	 *
	 * @param string $path The path.
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '57774' )]
	#[\PHPUnit\Framework\Attributes\DataProvider( 'data_paths_that_do_not_exist' )]
	public function test_should_not_determine_file_modified_time( $path ) {
		$result    = self::$filesystem->mtime( self::$file_structure['test_dir']['path'] . $path );
		$has_mtime = false !== $result;

		$this->assertFalse(
			$has_mtime,
			'An mtime was determined.'
		);

		$this->assertIsNotInt(
			$result,
			'The mtime is an integer.'
		);
	}
}
