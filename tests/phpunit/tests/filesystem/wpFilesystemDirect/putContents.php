<?php
/**
 * Tests for the WP_Filesystem_Direct::put_contents() method.
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
#[\PHPUnit\Framework\Attributes\CoversMethod( WP_Filesystem_Direct::class, 'put_contents' )]
class Tests_Filesystem_WpFilesystemDirect_PutContents extends WP_Filesystem_Direct_UnitTestCase {

	/**
	 * Tests that `WP_Filesystem_Direct::put_contents()`
	 * returns false for a directory.
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '57774' )]
	public function test_should_return_false_for_a_directory() {
		$this->assertFalse( self::$filesystem->put_contents( self::$file_structure['test_dir']['path'], 'New content.' ) );
	}

	/**
	 * Tests that `WP_Filesystem_Direct::put_contents()` inserts
	 * content into the provided file.
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '57774' )]
	public function test_should_insert_contents_into_file() {
		$file   = self::$file_structure['test_dir']['path'] . 'file-to-create.txt';
		$actual = self::$filesystem->put_contents( $file, 'New content.', 0644 );
		unlink( $file );

		$this->assertTrue( $actual, 'The contents were not inserted.' );
	}
}
