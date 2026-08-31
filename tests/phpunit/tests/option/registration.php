<?php

/**
 */






#[\PHPUnit\Framework\Attributes\Group( 'option' )]
class Tests_Option_Registration extends WP_UnitTestCase {

	/**
	 */
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'register_setting' )]
	public function test_register() {
		register_setting( 'test_group', 'test_option' );

		$registered = get_registered_settings();
		$this->assertArrayHasKey( 'test_option', $registered );

		$args = $registered['test_option'];
		$this->assertSame( 'test_group', $args['group'] );

		// Check defaults.
		$this->assertSame( 'string', $args['type'] );
		$this->assertFalse( $args['show_in_rest'] );
		$this->assertSame( '', $args['description'] );
	}

	/**
	 */
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'register_setting' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'apply_filters' )]
	public function test_register_with_callback() {
		register_setting( 'test_group', 'test_option', array( $this, 'filter_registered_setting' ) );

		$filtered = apply_filters( 'sanitize_option_test_option', 'smart', 'test_option', 'smart' );
		$this->assertSame( 'S-M-R-T', $filtered );
	}

	/**
	 */
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'register_setting' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_CLASS, 'WP_REST_Settings_Controller' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'apply_filters' )]
	public function test_register_with_array() {
		register_setting(
			'test_group',
			'test_option',
			array(
				'sanitize_callback' => array( $this, 'filter_registered_setting' ),
			)
		);

		$filtered = apply_filters( 'sanitize_option_test_option', 'smart', 'test_option', 'smart' );
		$this->assertSame( 'S-M-R-T', $filtered );
	}

	public function filter_registered_setting() {
		return 'S-M-R-T';
	}

	/**
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '38176' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'register_setting' )]
	public function test_register_with_default() {
		register_setting(
			'test_group',
			'test_default',
			array(
				'default' => 'Got that Viper with them rally stripes',
			)
		);

		$this->assertSame( 'Got that Viper with them rally stripes', get_option( 'test_default' ) );
	}

	/**
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '38176' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'register_setting' )]
	public function test_register_with_default_override() {
		register_setting(
			'test_group',
			'test_default',
			array(
				'default' => 'Got that Viper with them rally stripes',
			)
		);

		// This set of tests/references (and a previous version) are in support of Viper007Bond.
		// His Viper doesn't have rally stripes, but for the sake of the Big Tymers, we'll go with it.
		$this->assertSame( 'We the #1 Stunnas', get_option( 'test_default', 'We the #1 Stunnas' ) );
	}

	/**
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '38930' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'register_setting' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'add_option' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'get_option' )]
	public function test_add_option_with_no_options_cache() {
		register_setting(
			'test_group',
			'test_default',
			array(
				'default' => 'My Default :)',
			)
		);
		wp_cache_delete( 'notoptions', 'options' );
		$this->assertTrue( add_option( 'test_default', 'hello' ) );
		$this->assertSame( 'hello', get_option( 'test_default' ) );
	}

	/**
	 * @expectedDeprecated register_setting
	 *
	 */
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'register_setting' )]
	public function test_register_deprecated_group_misc() {
		register_setting( 'misc', 'test_option' );
	}

	/**
	 * @expectedDeprecated register_setting
	 *
	 */
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'register_setting' )]
	public function test_register_deprecated_group_privacy() {
		register_setting( 'privacy', 'test_option' );
	}

	/**
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '43207' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'register_setting' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'unregister_setting' )]
	public function test_unregister_setting_removes_default() {
		register_setting(
			'test_group',
			'test_default',
			array(
				'default' => 'Got that Viper with them rally stripes',
			)
		);

		unregister_setting( 'test_group', 'test_default' );

		$this->assertFalse( has_filter( 'default_option_test_default', 'filter_default_option' ) );
	}

	/**
	 * Ensures that unregister_setting() does not throw a notice or warning for unknown settings.
	 *
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '57674' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'unregister_setting' )]
	public function test_unregister_invalid_setting_does_not_throw_notice_or_warning() {
		$setting = uniqid();
		unregister_setting( $setting, $setting );
		$this->assertFalse( has_filter( 'default_option_' . $setting, 'filter_default_option' ) );
	}
}
