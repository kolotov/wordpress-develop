<?php
/**
 * Tests for the WP_Filesystem_Direct::is_file() method.
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
#[\PHPUnit\Framework\Attributes\CoversMethod( WP_Filesystem_Direct::class, 'is_file' )]
class Tests_Filesystem_WpFilesystemDirect_IsFile extends WP_Filesystem_Direct_UnitTestCase {

	/**
	 * Tests that `WP_Filesystem_Direct::is_file()` determies that
	 * a path is a file.
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '57774' )]
	public function test_should_determine_that_a_path_is_a_file() {
		$this->assertTrue( self::$filesystem->is_file( self::$file_structure['test_dir']['path'] . 'a_file_that_exists.txt' ) );
	}

	/**
	 * Tests that `WP_Filesystem_Direct::is_file()` determies that
	 * a path is not a file.
	 *
	 *
	 *
	 * @param string $path The path to check.
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '57774' )]
	#[\PHPUnit\Framework\Attributes\DataProvider( 'data_should_determine_if_a_path_is_not_a_file' )]
	public function test_should_determine_that_a_path_is_not_a_file( $path ) {
		$this->assertFalse( self::$filesystem->is_file( self::$file_structure['test_dir']['path'] . $path ) );
	}

	/**
	 * Data provider.
	 *
	 * @return array[]
	 */
	public static function data_should_determine_if_a_path_is_not_a_file() {
		return array(
			'a file that does not exist'      => array(
				'path' => 'a_file_that_does_not_exist.txt',
			),
			'a directory that exists'         => array(
				'path' => '',
			),
			'a directory that does not exist' => array(
				'path' => 'a_directory_that_does_not_exist',
			),
		);
	}
}
