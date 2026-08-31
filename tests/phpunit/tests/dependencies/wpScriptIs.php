<?php

/**
 * Test wp_script_is().
 *
 *
 */
#[\PHPUnit\Framework\Attributes\Group( 'dependencies' )]
#[\PHPUnit\Framework\Attributes\Group( 'scripts' )]
#[\PHPUnit\Framework\Attributes\CoversFunction( 'wp_script_is' )]
#[\PHPUnit\Framework\Attributes\CoversMethod( WP_Dependencies::class, 'query' )]
class Tests_Dependencies_WpScriptIs extends WP_UnitTestCase {
	private static $wp_scripts;
	private static $wp_scripts_was_set = false;

	public static function set_up_before_class() {
		parent::set_up_before_class();

		// If the global is set, store it for restoring when done testing.
		static::$wp_scripts_was_set = array_key_exists( 'wp_scripts', $GLOBALS );
		if ( static::$wp_scripts_was_set ) {
			static::$wp_scripts = $GLOBALS['wp_scripts'];
			unset( $GLOBALS['wp_scripts'] );
		}
	}

	public static function tear_down_after_class() {
		// Restore the global if it was set before running this set of tests.
		if ( static::$wp_scripts_was_set ) {
			$GLOBALS['wp_scripts'] = static::$wp_scripts;
		}

		parent::tear_down_after_class();
	}

	public function clean_up_global_scope() {
		unset( $GLOBALS['wp_scripts'] );
		parent::clean_up_global_scope();
	}

	public function test_script_is_registered() {
		$handle = 'test-script';
		wp_register_script( $handle, 'https://example.org/script.js' );

		$this->assertTrue( wp_script_is( $handle, 'registered' ) );
	}

	/**
	 *
	 * @param string $handle Script handle to test.
	 */
	#[\PHPUnit\Framework\Attributes\DataProvider( 'data_script_handles' )]
	public function test_script_is_enqueued( $handle ) {
		// Test set up.
		wp_enqueue_script( $handle );

		$this->assertTrue( wp_script_is( $handle ), "Script `{$handle}` should be enqueued after invoking wp_enqueue_script()" );
	}

	/**
	 *
	 * @param string $handle Script handle to test.
	 */
	#[\PHPUnit\Framework\Attributes\DataProvider( 'data_script_handles' )]
	public function test_script_is_not_enqueued( $handle ) {
		$this->assertFalse( wp_script_is( $handle ), "Script `{$handle}` should not be enqueued when test starts" );
	}

	/**
	 * Data provider.
	 *
	 * @return array
	 */
	public static function data_script_handles() {
		return array(
			array( 'heartbeat' ),
			array( 'jquery' ),
			array( 'wp-lists' ),
			array( 'wp-pointer' ),
			array( 'thickbox' ),
		);
	}

	/**
	 *
	 *
	 * @param string   $handle Script handle.
	 * @param string[] $deps   The deps to test for the given script handle.
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '28404' )]
	#[\PHPUnit\Framework\Attributes\DataProvider( 'data_deps_are_enqueued' )]
	public function test_deps_are_enqueued( $handle, $deps ) {
		// Check the deps are not enqueued before enqueuing.
		$this->assertFalse( wp_script_is( $handle ), 'Script `jquery-ui-accordion` should not be enqueued when test starts' );
		foreach ( $deps as $dep_handle ) {
			$this->assertFalse( wp_script_is( $dep_handle ), "Dependency `{$dep_handle}` should not be enqueued when test starts" );
		}

		// Test set up.
		wp_enqueue_script( $handle );

		foreach ( $deps as $dep_handle ) {
			$this->assertTrue( wp_script_is( $dep_handle ), "Dependency `{$dep_handle}` should be enqueued" );
		}

		$this->assertFalse( wp_script_is( 'underscore' ), 'Script "underscore" is not a dependency of "jquery-ui-accordion" and should not be enqueued' );
	}

	/**
	 * Data provider.
	 *
	 * @return array
	 */
	public static function data_deps_are_enqueued() {
		return array(
			'jquery: 1 level of deps'                 => array(
				'handle' => 'jquery',
				'deps'   => array(
					'jquery-core',
					'jquery-migrate',
				),
			),
			'mediaelement: 1 level of deps'           => array(
				'handle' => 'mediaelement',
				'deps'   => array(
					'mediaelement-core',
					'mediaelement-migrate',
				),
			),
			'jquery-effects-core: 2 levels of deps'   => array(
				'handle' => 'jquery-effects-core',
				'deps'   => array(
					// Dep to 'jquery-effects-core'.
					'jquery',
					// Deps to 'jquery'.
					'jquery-core',
					'jquery-migrate',
				),
			),
			'jquery-ui-accordion: 3 levels of deps'   => array(
				'handle' => 'jquery-ui-accordion',
				'deps'   => array(
					// Dep to 'jquery-ui-accordion'.
					'jquery-ui-core',
					// Dep to 'jquery-ui-core'.
					'jquery',
					// Deps to 'jquery'.
					'jquery-core',
					'jquery-migrate',
				),
			),
			'wp-mediaelement: 2 and 3 levels of deps' => array(
				'handle' => 'wp-mediaelement',
				'deps'   => array(
					// Dep to 'wp-mediaelement'.
					'mediaelement',
					// Deps to 'mediaelement'.
					'jquery',
					'mediaelement-core',
					'mediaelement-migrate',
					// Deps to 'jquery'.
					'jquery-core',
					'jquery-migrate',
				),
			),
		);
	}

	/**
	 *
	 *
	 * @param string   $handle   Script handle.
	 * @param string[] $not_deps The handles that are not deps of the given script handle.
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '28404' )]
	#[\PHPUnit\Framework\Attributes\DataProvider( 'data_non_deps_should_not_enqueue' )]
	public function test_non_deps_are_not_enqueued( $handle, $not_deps ) {
		// Check the deps are not enqueued before enqueuing.
		$this->assertFalse( wp_script_is( $handle ), "Script `{$handle}` should not be enqueued when test starts" );
		foreach ( $not_deps as $not_dep_handle ) {
			$this->assertFalse( wp_script_is( $not_dep_handle ), "Dependency `{$not_dep_handle}` should not be enqueued when test starts" );
		}

		// Test set up.
		wp_enqueue_script( $handle );

		foreach ( $not_deps as $not_dep_handle ) {
			$this->assertFalse( wp_script_is( $not_dep_handle ), "Script `{$not_dep_handle}` should not be enqueued as it is not a dependency of `{$handle}`" );
		}
	}

	/**
	 * Data provider.
	 *
	 * @return array
	 */
	public static function data_non_deps_should_not_enqueue() {
		return array(
			'imagesloaded: no dependencies' => array(
				'handle'   => 'imagesloaded',
				'not_deps' => array(
					'jquery',
					'masonry',
				),
			),
			'wp-sanitize: no dependencies'  => array(
				'handle'   => 'wp-sanitize',
				'not_deps' => array(
					'jquery',
					'jquery-core',
					'jquery-migrate',
				),
			),
			'jquery-ui-accordion'           => array(
				'handle'   => 'jquery-ui-accordion',
				'not_deps' => array(
					'underscore',
					'thickbox',
					'jquery-effects-core',
				),
			),
			'jquery-ui-datepicker'          => array(
				'handle'   => 'jquery-ui-datepicker',
				'not_deps' => array(
					'backbone',
					'jquery-effects-core',
					'jquery-effects-highlight',
				),
			),
		);
	}
}
