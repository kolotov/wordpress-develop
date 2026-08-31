<?php
/**
 * Tests for the WP_Filesystem_Direct::chgrp() method.
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
#[\PHPUnit\Framework\Attributes\CoversMethod( WP_Filesystem_Direct::class, 'chgrp' )]
class Tests_Filesystem_WpFilesystemDirect_Chgrp extends WP_Filesystem_Direct_UnitTestCase {

	/**
	 * Tests that `WP_Filesystem_Direct::chgrp()`
	 * returns false for a path that does not exist.
	 *
	 *
	 *
	 * @param string $path The path.
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '57774' )]
	#[\PHPUnit\Framework\Attributes\DataProvider( 'data_paths_that_do_not_exist' )]
	public function test_should_fail_to_change_file_group( $path ) {
		$this->assertFalse( self::$filesystem->chgrp( self::$file_structure['test_dir']['path'] . $path, 0 ) );
	}

	/**
	 * Tests that recursive {@see WP_Filesystem_Direct::chgrp()} descends into subdirectories.
	 *
	 * The resulting group cannot be asserted without elevated privileges, so recursion is
	 * verified by recording the paths passed to {@see WP_Filesystem_Direct::chgrp()}. Changing each item to its current
	 * group is a permitted no-op that avoids requiring root and its "Operation not permitted" warning.
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '65584' )]
	public function test_should_recurse_into_subdirectories(): void {
		$directory   = untrailingslashit( self::$file_structure['test_dir']['path'] );
		$nested_file = self::$file_structure['subfile']['path'];

		$spy = new class( null ) extends WP_Filesystem_Direct {
			/** @var string[] */
			public array $visited = array();

			public function chgrp( $file, $group, $recursive = false ) {
				$this->visited[] = $file;
				return parent::chgrp( $file, $group, $recursive );
			}
		};

		$spy->chgrp( $directory, (int) filegroup( $directory ), true );

		$this->assertContains(
			$nested_file,
			$spy->visited,
			'chgrp() did not recurse into the nested subdirectory.'
		);
	}
}
