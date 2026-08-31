<?php

/**
 * Tests for WP_Block_Metadata_Registry class.
 *
 */
#[\PHPUnit\Framework\Attributes\Group( 'blocks' )]




class Tests_Blocks_WpBlockMetadataRegistry extends WP_UnitTestCase {

	private $temp_manifest_file;

	private $registry_state;

	public function set_up() {
		parent::set_up();
		$this->registry_state     = $this->get_registry_state();
		$this->temp_manifest_file = wp_tempnam( 'block-metadata-manifest' );
	}

	public function tear_down() {
		$this->restore_registry_state( $this->registry_state );
		if ( file_exists( $this->temp_manifest_file ) ) {
			unlink( $this->temp_manifest_file );
		}
		parent::tear_down();
	}

	private function get_registry_state() {
		$state = array();
		foreach ( array( 'collections', 'last_matched_collection', 'default_collection_roots' ) as $property_name ) {
			$property                = new ReflectionProperty( WP_Block_Metadata_Registry::class, $property_name );
			$state[ $property_name ] = $property->getValue();
		}

		return $state;
	}

	private function restore_registry_state( $state ) {
		foreach ( $state as $property_name => $value ) {
			$property = new ReflectionProperty( WP_Block_Metadata_Registry::class, $property_name );
			$property->setValue( null, $value );
		}
	}

	public function test_register_collection_and_get_metadata() {
		$path          = WP_PLUGIN_DIR . '/test/path';
		$manifest_data = array(
			'test-block' => array(
				'name'  => 'test-block',
				'title' => 'Test Block',
			),
		);

		file_put_contents( $this->temp_manifest_file, '<?php return ' . var_export( $manifest_data, true ) . ';' );

		WP_Block_Metadata_Registry::register_collection( $path, $this->temp_manifest_file );

		$retrieved_metadata = WP_Block_Metadata_Registry::get_metadata( $path . '/test-block' );
		$this->assertSame( $manifest_data['test-block'], $retrieved_metadata );
	}

	public function test_get_nonexistent_metadata() {
		$path               = WP_PLUGIN_DIR . '/nonexistent/path';
		$retrieved_metadata = WP_Block_Metadata_Registry::get_metadata( $path . '/nonexistent-block' );
		$this->assertNull( $retrieved_metadata );
	}

	public function test_has_metadata() {
			$path          = WP_PLUGIN_DIR . '/another/test/path';
			$manifest_data = array(
				'existing-block' => array(
					'name'  => 'existing-block',
					'title' => 'Existing Block',
				),
			);

			file_put_contents( $this->temp_manifest_file, '<?php return ' . var_export( $manifest_data, true ) . ';' );

			WP_Block_Metadata_Registry::register_collection( $path, $this->temp_manifest_file );

			$this->assertTrue( WP_Block_Metadata_Registry::has_metadata( $path . '/existing-block' ) );
			$this->assertFalse( WP_Block_Metadata_Registry::has_metadata( $path . '/non-existing-block' ) );
	}

	public function test_register_collection_with_core_path() {
		$core_path = ABSPATH . WPINC . '/blocks';
		$result    = WP_Block_Metadata_Registry::register_collection( $core_path, $this->temp_manifest_file );
		$this->assertTrue( $result, 'Core path should be registered successfully' );
	}

	public function test_register_collection_with_valid_plugin_path() {
		$plugin_path = WP_PLUGIN_DIR . '/my-plugin/blocks';
		$result      = WP_Block_Metadata_Registry::register_collection( $plugin_path, $this->temp_manifest_file );
		$this->assertTrue( $result, 'Valid plugin path should be registered successfully' );
	}

	public function test_register_collection_with_invalid_plugin_path() {
		$invalid_plugin_path = WP_PLUGIN_DIR;

		$this->setExpectedIncorrectUsage( 'WP_Block_Metadata_Registry::register_collection' );

		$result = WP_Block_Metadata_Registry::register_collection( $invalid_plugin_path, $this->temp_manifest_file );
		$this->assertFalse( $result, 'Invalid plugin path should not be registered' );
	}

	/**
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '62140' )]
	public function test_register_collection_with_valid_muplugin_path() {
		$plugin_path = WPMU_PLUGIN_DIR . '/my-plugin/blocks';
		$result      = WP_Block_Metadata_Registry::register_collection( $plugin_path, $this->temp_manifest_file );
		$this->assertTrue( $result, 'Valid must-use plugin path should be registered successfully' );
	}

	/**
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '62140' )]
	public function test_register_collection_with_invalid_muplugin_path() {
		$invalid_plugin_path = WPMU_PLUGIN_DIR;

		$this->setExpectedIncorrectUsage( 'WP_Block_Metadata_Registry::register_collection' );

		$result = WP_Block_Metadata_Registry::register_collection( $invalid_plugin_path, $this->temp_manifest_file );
		$this->assertFalse( $result, 'Invalid must-use plugin path should not be registered' );
	}

	/**
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '62140' )]
	public function test_register_collection_with_valid_theme_path() {
		$theme_path = WP_CONTENT_DIR . '/themes/my-theme/blocks';
		$result     = WP_Block_Metadata_Registry::register_collection( $theme_path, $this->temp_manifest_file );
		$this->assertTrue( $result, 'Valid theme path should be registered successfully' );
	}

	/**
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '62140' )]
	public function test_register_collection_with_invalid_theme_path() {
		$invalid_theme_path = WP_CONTENT_DIR . '/themes';

		$this->setExpectedIncorrectUsage( 'WP_Block_Metadata_Registry::register_collection' );

		$result = WP_Block_Metadata_Registry::register_collection( $invalid_theme_path, $this->temp_manifest_file );
		$this->assertFalse( $result, 'Invalid theme path should not be registered' );
	}

	/**
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '62140' )]
	public function test_register_collection_with_arbitrary_path() {
		$arbitrary_path = '/var/arbitrary/path';
		$result         = WP_Block_Metadata_Registry::register_collection( $arbitrary_path, $this->temp_manifest_file );
		$this->assertTrue( $result, 'Arbitrary path should be registered successfully' );
	}

	/**
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '62140' )]
	public function test_register_collection_with_arbitrary_path_and_collection_roots_filter() {
		$arbitrary_path = '/var/arbitrary/path';
		add_filter(
			'wp_allowed_block_metadata_collection_roots',
			static function ( $paths ) use ( $arbitrary_path ) {
				$paths[] = $arbitrary_path;
				return $paths;
			}
		);

		$this->setExpectedIncorrectUsage( 'WP_Block_Metadata_Registry::register_collection' );

		$result = WP_Block_Metadata_Registry::register_collection( $arbitrary_path, $this->temp_manifest_file );
		$this->assertFalse( $result, 'Arbitrary path should not be registered if it matches a collection root' );

		$result = WP_Block_Metadata_Registry::register_collection( dirname( $arbitrary_path ), $this->temp_manifest_file );
		$this->assertFalse( $result, 'Arbitrary path should not be registered if it is a parent directory of a collection root' );

		$result = WP_Block_Metadata_Registry::register_collection( $arbitrary_path . '/my-plugin/blocks', $this->temp_manifest_file );
		$this->assertTrue( $result, 'Arbitrary path should be registered successfully if it is within a collection root' );
	}

	/**
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '62140' )]
	public function test_register_collection_with_wp_content_parent_directory_path() {
		$invalid_path = dirname( WP_CONTENT_DIR );

		$this->setExpectedIncorrectUsage( 'WP_Block_Metadata_Registry::register_collection' );

		$result = WP_Block_Metadata_Registry::register_collection( $invalid_path, $this->temp_manifest_file );
		$this->assertFalse( $result, 'Invalid path (parent directory of "wp-content") should not be registered' );
	}

	/**
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '62140' )]
	public function test_register_collection_with_wp_includes_parent_directory_path() {
		$invalid_path = ABSPATH;

		$this->setExpectedIncorrectUsage( 'WP_Block_Metadata_Registry::register_collection' );

		$result = WP_Block_Metadata_Registry::register_collection( $invalid_path, $this->temp_manifest_file );
		$this->assertFalse( $result, 'Invalid path (parent directory of "wp-includes") should not be registered' );
	}

	public function test_register_collection_with_non_existent_manifest() {
		$non_existent_manifest = '/path/that/does/not/exist/block-manifest.php';

		$this->setExpectedIncorrectUsage( 'WP_Block_Metadata_Registry::register_collection' );

		$result = WP_Block_Metadata_Registry::register_collection( '/var/arbitrary/path', $non_existent_manifest );
		$this->assertFalse( $result, 'Non-existent manifest should not be registered' );
	}

	/**
	 * Tests that the `get_collection_block_metadata_files()` method returns the expected list of block metadata files.
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '62267' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Block_Metadata_Registry', 'get_collection_block_metadata_files' )]
	public function test_get_collection_block_metadata_files() {
		$path          = WP_PLUGIN_DIR . '/test-plugin/data/block-types';
		$manifest_data = array(
			'a-block'       => array(
				'name'  => 'a-block',
				'title' => 'A Block',
			),
			'another-block' => array(
				'name'  => 'another-block',
				'title' => 'Another Block',
			),
		);

		file_put_contents( $this->temp_manifest_file, '<?php return ' . var_export( $manifest_data, true ) . ';' );

		$this->assertTrue( WP_Block_Metadata_Registry::register_collection( $path, $this->temp_manifest_file ) );
		$this->assertSame(
			array(
				WP_PLUGIN_DIR . '/test-plugin/data/block-types/a-block/block.json',
				WP_PLUGIN_DIR . '/test-plugin/data/block-types/another-block/block.json',
			),
			WP_Block_Metadata_Registry::get_collection_block_metadata_files( $path )
		);
	}

	/**
	 * Tests that `register_collection()`, `get_metadata()`, and `get_collection_metadata_files()` handle Windows paths.
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '63027' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Block_Metadata_Registry', 'register_collection' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Block_Metadata_Registry', 'get_metadata' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Block_Metadata_Registry', 'get_collection_block_metadata_files' )]
	public function test_with_windows_paths() {
		// Set up a mock manifest file.
		$manifest_data = array(
			'test-block' => array(
				'name'  => 'test-block',
				'title' => 'Test Block',
			),
		);
		file_put_contents( $this->temp_manifest_file, '<?php return ' . var_export( $manifest_data, true ) . ';' );

		$plugins_path = 'C:\\Site\\wp-content\\plugins';
		$plugin_path  = $plugins_path . '\\my-plugin\\blocks';
		$block_path   = $plugin_path . '\\test-block\\block.json';

		// Register the mock plugins directory as an allowed root.
		add_filter(
			'wp_allowed_block_metadata_collection_roots',
			static function ( $paths ) use ( $plugins_path ) {
				$paths[] = $plugins_path;
				return $paths;
			}
		);

		$this->assertTrue( WP_Block_Metadata_Registry::register_collection( $plugin_path, $this->temp_manifest_file ), 'Could not register block metadata collection.' );
		$this->assertSame( $manifest_data['test-block'], WP_Block_Metadata_Registry::get_metadata( $block_path ), 'Could not find collection for provided block.json path.' );
		$this->assertSame( array( wp_normalize_path( $block_path ) ), WP_Block_Metadata_Registry::get_collection_block_metadata_files( $plugin_path ), 'Could not get correct list of block.json paths for collection.' );
	}

	/**
	 * Tests that `register_collection()` handles Windows paths correctly for verifying allowed roots.
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '63027' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Block_Metadata_Registry', 'register_collection' )]
	public function test_with_windows_paths_and_disallowed_location() {
		$parent_path  = 'C:\\Site\\wp-content';
		$plugins_path = $parent_path . '\\plugins';
		$plugin_path  = $plugins_path . '\\my-plugin\\blocks';

		// Register the mock plugins directory as an allowed root.
		add_filter(
			'wp_allowed_block_metadata_collection_roots',
			static function ( $paths ) use ( $plugins_path ) {
				$paths[] = $plugins_path;
				return $paths;
			}
		);

		$this->setExpectedIncorrectUsage( 'WP_Block_Metadata_Registry::register_collection' );

		$result = WP_Block_Metadata_Registry::register_collection( $plugins_path, $this->temp_manifest_file );
		$this->assertFalse( $result, 'Arbitrary Windows path should not be registered if it matches a collection root' );

		$result = WP_Block_Metadata_Registry::register_collection( $parent_path, $this->temp_manifest_file );
		$this->assertFalse( $result, 'Arbitrary Windows path should not be registered if it is a parent directory of a collection root' );

		$result = WP_Block_Metadata_Registry::register_collection( $plugin_path, $this->temp_manifest_file );
		$this->assertTrue( $result, 'Arbitrary Windows path should be registered successfully if it is within a collection root' );
	}
}
