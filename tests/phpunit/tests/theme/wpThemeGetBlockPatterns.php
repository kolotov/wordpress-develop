<?php
/**
 * Tests for WP_Theme::get_block_patterns.
 *
 * @package WordPress
 * @subpackage Blocks
 * @since 6.4.0
 *
 *
 */
#[\PHPUnit\Framework\Attributes\Group( 'blocks' )]
#[\PHPUnit\Framework\Attributes\Group( 'themes' )]


#[\PHPUnit\Framework\Attributes\CoversMethod( WP_Theme::class, 'get_block_patterns' )]
class Tests_Theme_WPThemeGetBlockPatterns extends WP_UnitTestCase {
	/**
	 * The initial cache object.
	 *
	 * @var object
	 */
	private $initial_cache_object;

	private static $development_mode_existed;

	private static $initial_development_mode;

	public function set_up() {
		parent::set_up();

		$this->initial_cache_object = wp_using_ext_object_cache();
	}

	public function tear_down() {
		wp_using_ext_object_cache( $this->initial_cache_object );
		parent::tear_down();
	}

	public static function wpSetUpBeforeClass() {
		self::$development_mode_existed = array_key_exists( '_wp_tests_development_mode', $GLOBALS );
		self::$initial_development_mode = $GLOBALS['_wp_tests_development_mode'] ?? null;

		// Ensure development mode is reset before running these tests.
		unset( $GLOBALS['_wp_tests_development_mode'] );
	}

	public static function wpTearDownAfterClass() {
		if ( self::$development_mode_existed ) {
			$GLOBALS['_wp_tests_development_mode'] = self::$initial_development_mode;
		} else {
			unset( $GLOBALS['_wp_tests_development_mode'] );
		}
	}

	/**
	 * Test helper to access the private get_pattern_cache method of a theme.
	 *
	 * @param WP_Theme $wp_theme A WP_Theme object.
	 * @return array|false Returns an array of patterns if cache is found, otherwise false.
	 */
	private function get_pattern_cache( $wp_theme ) {
		$reflection = new ReflectionMethod( $wp_theme, 'get_pattern_cache' );
		if ( PHP_VERSION_ID < 80100 ) {
			$reflection->setAccessible( true );
		}

		$pattern_cache = $reflection->invoke( $wp_theme, 'get_pattern_cache' );
		if ( PHP_VERSION_ID < 80100 ) {
			$reflection->setAccessible( false );
		}

		return $pattern_cache;
	}

	/**
	 * Test helper to access the private cache_hash propery of a theme.
	 *
	 * @param WP_Theme $wp_theme A WP_Theme object.
	 * @return array|false Returns an array of patterns if cache is found, otherwise false.
	 */
	private function get_cache_hash( $wp_theme ) {
		$reflection = new ReflectionProperty( get_class( $wp_theme ), 'cache_hash' );
		if ( PHP_VERSION_ID < 80100 ) {
			$reflection->setAccessible( true );
		}
		$cache_hash = $reflection->getValue( $wp_theme );
		if ( PHP_VERSION_ID < 80100 ) {
			$reflection->setAccessible( false );
		}
		return $cache_hash;
	}

	/**
	 *
	 *
	 * @param string $theme_slug The theme's slug.
	 * @param array  $expected   The expected pattern data.
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '59490' )]
	#[\PHPUnit\Framework\Attributes\DataProvider( 'data_get_block_patterns' )]
	public function test_should_return_block_patterns( $theme_slug, $expected ) {
		$theme    = wp_get_theme( $theme_slug );
		$patterns = $theme->get_block_patterns();
		$this->assertSameSets( $expected, $patterns );
	}

	/**
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '59490' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Theme', 'delete_pattern_cache' )]
	public function test_delete_pattern_cache() {
		$theme = wp_get_theme( 'block-theme-patterns' );

		$this->assertTrue( $theme->exists(), 'The test theme could not be found.' );

		$theme->get_block_patterns();

		$this->assertSameSets(
			array(
				'cta.php' => array(
					'title'       => 'Centered Call To Action',
					'slug'        => 'block-theme-patterns/cta',
					'description' => '',
					'categories'  => array( 'call-to-action' ),
				),
			),
			$this->get_pattern_cache( $theme ),
			'The cache for block theme patterns should match the expected.'
		);
		$theme->delete_pattern_cache();
		$this->assertFalse(
			$this->get_pattern_cache( $theme ),
			'The cache for block theme patterns should have been cleared.'
		);
	}

	/**
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '59490' )]
	#[\PHPUnit\Framework\Attributes\Group( 'ms-excluded' )]
	public function test_should_clear_cache_after_switching_theme() {
		switch_theme( 'block-theme' );
		$theme1 = wp_get_theme();

		$this->assertTrue( $theme1->exists(), 'The block-theme test theme could not be found.' );

		$theme1->get_block_patterns();
		$this->assertSameSets(
			array(),
			$this->get_pattern_cache( $theme1 ),
			'The cache for block theme should be empty.'
		);

		switch_theme( 'block-theme-patterns' );

		$theme2 = wp_get_theme();
		$this->assertTrue( $theme2->exists(), 'The block-theme-patterns test theme could not be found.' );

		$this->assertFalse( $this->get_pattern_cache( $theme1 ), 'Cache should not be set for block theme after switch theme.' );
		$this->assertFalse( $this->get_pattern_cache( $theme2 ), 'Cache should not be set for block theme patterns before being requested.' );

		$theme2->get_block_patterns( $theme2 );
		$this->assertSameSets(
			array(
				'cta.php' => array(
					'title'       => 'Centered Call To Action',
					'slug'        => 'block-theme-patterns/cta',
					'description' => '',
					'categories'  => array( 'call-to-action' ),
				),

			),
			$this->get_pattern_cache( $theme2 ),
			'The cache for block theme patterns should match the expected.'
		);
	}

	/**
	 * Data provider.
	 *
	 * @return array[]
	 */
	public static function data_get_block_patterns() {
		return array(
			array(
				'theme_slug' => 'block-theme',
				'expected'   => array(),
			),
			array(
				'theme_slug' => 'block-theme-child',
				'expected'   => array(),
			),
			array(
				'theme_slug' => 'block-theme-patterns',
				'expected'   => array(
					'cta.php' => array(
						'title'       => 'Centered Call To Action',
						'slug'        => 'block-theme-patterns/cta',
						'description' => '',
						'categories'  => array( 'call-to-action' ),
					),
				),
			),
			array(
				'theme_slug' => 'broken-theme',
				'expected'   => array(),
			),
			array(
				'theme_slug' => 'invalid',
				'expected'   => array(),
			),
		);
	}

	/**
	 * Tests that WP_Theme::get_block_patterns() clears existing cache when in theme development mode.
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '59591' )]
	public function test_should_clear_existing_cache_when_in_development_mode() {
		$theme = wp_get_theme( 'block-theme-patterns' );

		$this->assertTrue( $theme->exists(), 'The test theme could not be found.' );

		// Calling the function should set the cache.
		$theme->get_block_patterns();
		$this->assertSameSets(
			array(
				'cta.php' => array(
					'title'       => 'Centered Call To Action',
					'slug'        => 'block-theme-patterns/cta',
					'description' => '',
					'categories'  => array( 'call-to-action' ),
				),
			),
			$this->get_pattern_cache( $theme ),
			'The cache for block theme patterns should be set.'
		);

		// Calling the function while in theme development mode should clear the cache.
		$GLOBALS['_wp_tests_development_mode'] = 'theme';
		$theme->get_block_patterns( $theme );
		unset( $GLOBALS['_wp_tests_development_mode'] ); // Reset to not pollute other tests.
		$this->assertFalse(
			$this->get_pattern_cache( $theme ),
			'The cache for block theme patterns should have been cleared due to theme development mode.'
		);
	}

	/**
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '59600' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Theme', 'delete_pattern_cache' )]
	public function test_delete_pattern_cache_non_obj_cache() {
		// Ensure object cache is disabled.
		wp_using_ext_object_cache( false );

		$theme = wp_get_theme( 'block-theme-patterns' );

		$this->assertTrue( $theme->exists(), 'The test theme could not be found.' );

		$theme->get_block_patterns();

		$this->assertSameSets(
			array(
				'cta.php' => array(
					'title'       => 'Centered Call To Action',
					'slug'        => 'block-theme-patterns/cta',
					'description' => '',
					'categories'  => array( 'call-to-action' ),
				),
			),
			$this->get_pattern_cache( $theme ),
			'The cache for block theme patterns should match the expected.'
		);
		$theme->delete_pattern_cache();
		$this->assertFalse(
			$this->get_pattern_cache( $theme ),
			'The cache for block theme patterns should have been cleared.'
		);
	}

	/**
	 * Check if the pattern cache is stored in transient if object cache is not present.
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '59600' )]
	public function test_pattern_transient_cache_for_non_cache_site() {
		// Ensure object cache is disabled.
		wp_using_ext_object_cache( false );

		$theme = wp_get_theme( 'block-theme-patterns' );
		$theme->get_block_patterns();

		$transient_key   = 'wp_theme_files_patterns-' . $this->get_cache_hash( $theme );
		$transient_value = get_site_transient( $transient_key );

		$this->assertSameSets(
			array(
				'cta.php' => array(
					'title'       => 'Centered Call To Action',
					'slug'        => 'block-theme-patterns/cta',
					'description' => '',
					'categories'  => array( 'call-to-action' ),
				),
			),
			$transient_value['patterns'],
			'The transient value should match the expected.'
		);

		$this->assertNotEmpty(
			$this->get_pattern_cache( $theme ),
			'The cache for block theme patterns is empty.'
		);
	}
}
