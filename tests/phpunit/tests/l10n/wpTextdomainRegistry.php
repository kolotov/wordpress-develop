<?php

/**
 *
 */
#[\PHPUnit\Framework\Attributes\Group( 'l10n' )]
#[\PHPUnit\Framework\Attributes\Group( 'i18n' )]






class Tests_L10n_wpTextdomainRegistry extends WP_UnitTestCase {
	/**
	 * @var WP_Textdomain_Registry
	 */
	protected $instance;

	public function set_up() {
		parent::set_up();

		$this->instance = new WP_Textdomain_Registry();
	}

	public function tear_down() {
		wp_cache_delete( md5( WP_LANG_DIR . '/foobar/' ), 'translation_files' );
		wp_cache_delete( md5( WP_LANG_DIR . '/plugins/' ), 'translation_files' );
		wp_cache_delete( md5( WP_LANG_DIR . '/themes/' ), 'translation_files' );
		wp_cache_delete( md5( WP_LANG_DIR . '/' ), 'translation_files' );

		parent::tear_down();
	}

	/**
	 */
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Textdomain_Registry', 'has' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Textdomain_Registry', 'get' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Textdomain_Registry', 'set_custom_path' )]
	public function test_set_custom_path() {
		$this->instance->set_custom_path( 'foo', WP_LANG_DIR . '/bar' );

		$this->assertTrue(
			$this->instance->has( 'foo' ),
			'Incorrect availability status for textdomain with custom path'
		);
		$this->assertSame(
			WP_LANG_DIR . '/bar/',
			$this->instance->get( 'foo', 'en_US' ),
			'Should return custom path for textdomain and en_US locale'
		);
		$this->assertSame(
			WP_LANG_DIR . '/bar/',
			$this->instance->get( 'foo', 'de_DE' ),
			'Custom path for textdomain not returned'
		);
		$this->assertNotFalse(
			wp_cache_get( md5( WP_LANG_DIR . '/bar/' ), 'translation_files' ),
			'List of files in custom path not cached'
		);
	}

	/**
	 */
	#[\PHPUnit\Framework\Attributes\DataProvider( 'data_domains_locales' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Textdomain_Registry', 'get' )]
	public function test_get( $domain, $locale, $expected ) {
		$actual = $this->instance->get( $domain, $locale );
		$this->assertSame(
			$expected,
			$actual,
			'Expected languages directory path not matching actual one'
		);
	}

	/**
	 */
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Textdomain_Registry', 'set' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Textdomain_Registry', 'get' )]
	public function test_set_populates_cache() {
		$this->instance->set( 'foo-plugin', 'de_DE', '/foo/bar' );

		$this->assertSame(
			'/foo/bar/',
			$this->instance->get( 'foo-plugin', 'de_DE' )
		);
	}

	/**
	 */
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Textdomain_Registry', 'get_language_files_from_path' )]
	public function test_get_language_files_from_path_caches_results() {
		$this->instance->get_language_files_from_path( WP_LANG_DIR . '/foobar/' );
		$this->instance->get_language_files_from_path( WP_LANG_DIR . '/plugins/' );
		$this->instance->get_language_files_from_path( WP_LANG_DIR . '/themes/' );
		$this->instance->get_language_files_from_path( WP_LANG_DIR . '/' );

		$this->assertNotFalse( wp_cache_get( md5( WP_LANG_DIR . '/plugins/' ), 'translation_files' ) );
		$this->assertNotFalse( wp_cache_get( md5( WP_LANG_DIR . '/themes/' ), 'translation_files' ) );
		$this->assertNotFalse( wp_cache_get( md5( WP_LANG_DIR . '/foobar/' ), 'translation_files' ) );
		$this->assertNotFalse( wp_cache_get( md5( WP_LANG_DIR . '/' ), 'translation_files' ) );
	}

	/**
	 */
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Textdomain_Registry', 'get_language_files_from_path' )]
	public function test_get_language_files_from_path_short_circuit() {
		add_filter( 'pre_get_language_files_from_path', '__return_empty_array' );
		$result = $this->instance->get_language_files_from_path( WP_LANG_DIR . '/plugins/' );
		remove_filter( 'pre_get_language_files_from_path', '__return_empty_array' );

		$cache = wp_cache_get( md5( WP_LANG_DIR . '/plugins/' ), 'translation_files' );

		$this->assertEmpty( $result );
		$this->assertFalse( $cache );
	}

	/**
	 */
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Textdomain_Registry', 'invalidate_mo_files_cache' )]
	public function test_invalidate_mo_files_cache() {
		$this->instance->get_language_files_from_path( WP_LANG_DIR . '/plugins/' );
		$this->instance->get_language_files_from_path( WP_LANG_DIR . '/themes/' );
		$this->instance->get_language_files_from_path( WP_LANG_DIR . '/' );

		$this->instance->invalidate_mo_files_cache(
			null,
			array(
				'type'         => 'translation',
				'translations' => array(
					(object) array(
						'type'     => 'plugin',
						'slug'     => 'internationalized-plugin',
						'language' => 'de_DE',
						'version'  => '99.9.9',
					),
					(object) array(
						'type'     => 'theme',
						'slug'     => 'internationalized-theme',
						'language' => 'de_DE',
						'version'  => '99.9.9',
					),
					(object) array(
						'type'     => 'core',
						'slug'     => 'default',
						'language' => 'es_ES',
						'version'  => '99.9.9',
					),
				),
			)
		);

		$this->assertFalse( wp_cache_get( md5( WP_LANG_DIR . '/plugins/' ), 'translation_files' ) );
		$this->assertFalse( wp_cache_get( md5( WP_LANG_DIR . '/themes/' ), 'translation_files' ) );
		$this->assertFalse( wp_cache_get( md5( WP_LANG_DIR . '/' ), 'translation_files' ) );
	}

	public static function data_domains_locales() {
		return array(
			'Non-existent plugin'                      => array(
				'unknown-plugin',
				'en_US',
				false,
			),
			'Non-existent plugin with de_DE'           => array(
				'unknown-plugin',
				'de_DE',
				false,
			),
			'Available de_DE translations'             => array(
				'internationalized-plugin',
				'de_DE',
				WP_LANG_DIR . '/plugins/',
			),
			'Available es_ES translations'             => array(
				'internationalized-plugin',
				'es_ES',
				WP_LANG_DIR . '/plugins/',
			),
			'Unavailable fr_FR translations'           => array(
				'internationalized-plugin',
				'fr_FR',
				false,
			),
			'Unavailable en_US translations'           => array(
				'internationalized-plugin',
				'en_US',
				false,
			),
			'Available de_DE translations (.l10n.php)' => array(
				'internationalized-plugin-2',
				'de_DE',
				WP_LANG_DIR . '/plugins/',
			),
			'Available es_ES translations (.l10n.php)' => array(
				'internationalized-plugin-2',
				'es_ES',
				WP_LANG_DIR . '/plugins/',
			),
		);
	}
}
