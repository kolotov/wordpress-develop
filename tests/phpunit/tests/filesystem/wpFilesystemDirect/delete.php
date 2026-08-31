<?php
/**
 * Tests for the WP_Filesystem_Direct::delete() method.
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
#[\PHPUnit\Framework\Attributes\CoversMethod( WP_Filesystem_Direct::class, 'delete' )]
class Tests_Filesystem_WpFilesystemDirect_Delete extends WP_Filesystem_Direct_UnitTestCase {

	/**
	 * Tests that `WP_Filesystem_Direct::delete()` returns false
	 * for an empty path.
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '57774' )]
	public function test_should_return_false_for_empty_path() {
		$this->assertFalse( self::$filesystem->delete( '' ) );
	}

	/**
	 * Tests that `WP_Filesystem_Direct::delete()` deletes an empty directory.
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '57774' )]
	public function test_should_delete_an_empty_directory() {
		$dir = self::$file_structure['test_dir']['path'] . 'directory-to-delete';

		$this->assertTrue(
			mkdir( $dir ),
			'The directory was not created.'
		);

		$this->assertTrue(
			self::$filesystem->delete( $dir ),
			'The directory was not deleted.'
		);
	}

	/**
	 * Tests that `WP_Filesystem_Direct::delete()` deletes a directory with contents.
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '57774' )]
	public function test_should_delete_a_directory_with_contents() {
		$this->assertTrue(
			self::$filesystem->delete( self::$file_structure['test_dir']['path'], true ),
			'Directory deletion failed.'
		);

		$this->assertDirectoryDoesNotExist(
			self::$file_structure['test_dir']['path'],
			'The directory was not deleted.'
		);
	}

	/**
	 * Tests that `WP_Filesystem_Direct::delete()` deletes a file.
	 *
	 *
	 *
	 * @param string $file The key for the file in `self::$file_structure`.
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '57774' )]
	#[\PHPUnit\Framework\Attributes\DataProvider( 'data_should_delete_a_file' )]
	public function test_should_delete_a_file( $key ) {
		$file = self::$file_structure[ $key ]['path'] . $key;

		$this->assertTrue( self::$filesystem->delete( $file ), 'File deletion failed.' );
		$this->assertFileDoesNotExist( $file, 'The file was not deleted.' );
	}

	/**
	 * Data provider.
	 *
	 * @return array[]
	 */
	public static function data_should_delete_a_file() {
		return array(
			'A visible file' => array(
				'key' => 'visible_file',
			),
			'A hidden file'  => array(
				'key' => 'hidden_file',
			),
		);
	}

	/**
	 * Tests that `WP_Filesystem_Direct::delete()`
	 * returns true when deleting a path that does not exist.
	 *
	 *
	 *
	 * @param string $path The path.
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '57774' )]
	#[\PHPUnit\Framework\Attributes\DataProvider( 'data_paths_that_do_not_exist' )]
	public function test_should_return_true_when_deleting_path_that_does_not_exist( $path ) {
		$path = self::$file_structure['test_dir']['path'] . $path;

		/*
		 * Verify that the path doesn't exist before testing.
		 *
		 * assertFileDoesNotExist() uses file_exists(), which returns the same result for both
		 * files and directories.
		 * assertDirectoryDoesNotExist() uses is_dir(), which tests strictly for a directory.
		 *
		 * For more useful debugging in the event of a failure, test for a directory first.
		 */
		$this->assertDirectoryDoesNotExist( $path, "$path already existed as a directory before testing." );
		$this->assertFileDoesNotExist( $path, "$path already existed as a file before testing." );

		$this->assertTrue( self::$filesystem->delete( $path ), 'Attempting to delete a non-existent path should return true.' );
	}

	/**
	 * Tests that `WP_Filesystem_Direct::delete()`
	 * returns false when a directory's contents cannot be deleted.
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '57774' )]
	public function test_should_return_false_when_contents_cannot_be_deleted() {
		global $wp_filesystem;

		$wp_filesystem = new WP_Filesystem_Direct( array() );

		$path = self::$file_structure['test_dir']['path'] . 'dir-to-delete/';

		if ( ! is_dir( $path ) ) {
			mkdir( $path );
		}

		// Set up mock filesystem.
		$filesystem_mock = $this->getMockBuilder( 'WP_Filesystem_Direct' )
								->setConstructorArgs( array( null ) )
								->onlyMethods( array( 'dirlist' ) )
								->getMock();

		$filesystem_mock->expects( $this->once() )
						->method( 'dirlist' )
						->willReturn(
							array( 'a_file_that_does_not_exist.txt' => array( 'type' => 'f' ) )
						);

		$wp_filesystem_backup = $wp_filesystem;
		$wp_filesystem        = $filesystem_mock;

		$actual = $filesystem_mock->delete( $path, true );

		if ( $actual ) {
			rmdir( $path );
		}

		$wp_filesystem = $wp_filesystem_backup;

		$this->assertFalse( $actual );
	}

	/**
	 * Tests that `WP_Filesystem_Direct::delete()`
	 * returns false when the path is not a file or directory, but exists.
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '57774' )]
	public function test_should_return_false_when_path_exists_but_is_not_a_file_or_directory() {
		global $wp_filesystem;

		$wp_filesystem = new WP_Filesystem_Direct( array() );

		// Set up mock filesystem.
		$filesystem_mock = $this->getMockBuilder( 'WP_Filesystem_Direct' )
								->setConstructorArgs( array( null ) )
								->onlyMethods( array( 'is_file', 'dirlist' ) )
								->getMock();

		$filesystem_mock->expects( $this->once() )
						->method( 'is_file' )
						->willReturn( false );

		$filesystem_mock->expects( $this->once() )
						->method( 'dirlist' )
						->willReturn( false );

		$wp_filesystem_backup = $wp_filesystem;
		$wp_filesystem        = $filesystem_mock;

		$actual = $filesystem_mock->delete( self::$file_structure['subdir']['path'], true );

		$wp_filesystem = $wp_filesystem_backup;

		$this->assertFalse( $actual );
	}
}
