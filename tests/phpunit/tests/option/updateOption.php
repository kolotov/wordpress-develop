<?php

/**
 */







#[\PHPUnit\Framework\Attributes\Group( 'option' )]
class Tests_Option_UpdateOption extends WP_UnitTestCase {
	/**
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '31047' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'add_filter' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'update_option' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'remove_filter' )]
	public function test_should_respect_default_option_filter_when_option_does_not_yet_exist_in_database() {
		add_filter( 'default_option_doesnotexist', array( $this, '__return_foo' ) );
		$added = update_option( 'doesnotexist', 'bar' );
		remove_filter( 'default_option_doesnotexist', array( $this, '__return_foo' ) );

		$this->assertTrue( $added );
		$this->assertSame( 'bar', get_option( 'doesnotexist' ) );
	}

	/**
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '26394' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'update_option' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'wp_load_alloptions' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'get_option' )]
	public function test_should_set_autoload_yes_for_nonexistent_option_when_autoload_param_is_missing() {
		$this->flush_cache();
		update_option( 'test_update_option_default', 'value' );
		$this->flush_cache();

		// Populate the alloptions cache, which includes autoload=yes options.
		wp_load_alloptions();

		$before = get_num_queries();
		$value  = get_option( 'test_update_option_default' );
		$after  = get_num_queries();

		$this->assertSame( $before, $after );
		$this->assertSame( $value, 'value' );
	}

	/**
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '26394' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'update_option' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'wp_load_alloptions' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'get_option' )]
	public function test_should_set_autoload_yes_for_nonexistent_option_when_autoload_param_is_yes() {
		$this->flush_cache();
		update_option( 'test_update_option_default', 'value', true );
		$this->flush_cache();

		// Populate the alloptions cache, which includes autoload=yes options.
		wp_load_alloptions();

		$before = get_num_queries();
		$value  = get_option( 'test_update_option_default' );
		$after  = get_num_queries();

		$this->assertSame( $before, $after );
		$this->assertSame( $value, 'value' );
	}

	/**
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '26394' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'update_option' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'wp_load_alloptions' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'get_option' )]
	public function test_should_set_autoload_no_for_nonexistent_option_when_autoload_param_is_no() {
		$this->flush_cache();
		update_option( 'test_update_option_default', 'value', false );
		$this->flush_cache();

		// Populate the alloptions cache, which does not include autoload=no options.
		wp_load_alloptions();

		$before = get_num_queries();
		$value  = get_option( 'test_update_option_default' );
		$after  = get_num_queries();

		// Database has been hit.
		$this->assertSame( $before + 1, $after );
		$this->assertSame( $value, 'value' );
	}

	/**
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '26394' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'update_option' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'wp_load_alloptions' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'get_option' )]
	public function test_should_set_autoload_no_for_nonexistent_option_when_autoload_param_is_false() {
		$this->flush_cache();
		update_option( 'test_update_option_default', 'value', false );
		$this->flush_cache();

		// Populate the alloptions cache, which does not include autoload=no options.
		wp_load_alloptions();

		$before = get_num_queries();
		$value  = get_option( 'test_update_option_default' );
		$after  = get_num_queries();

		// Database has been hit.
		$this->assertSame( $before + 1, $after );
		$this->assertSame( $value, 'value' );
	}

	/**
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '26394' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'update_option' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'wp_load_alloptions' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'get_option' )]
	public function test_autoload_should_be_updated_for_existing_option_when_value_is_changed() {
		add_option( 'foo', 'bar', '', false );
		$updated = update_option( 'foo', 'bar2', true );
		$this->assertTrue( $updated );

		$this->flush_cache();

		// Populate the alloptions cache, which includes autoload=yes options.
		wp_load_alloptions();

		$before = get_num_queries();
		$value  = get_option( 'foo' );

		$this->assertSame( $before, get_num_queries() );
		$this->assertSame( $value, 'bar2' );
	}

	/**
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '26394' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'update_option' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'wp_load_alloptions' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'get_option' )]
	public function test_autoload_should_not_be_updated_for_existing_option_when_value_is_unchanged() {
		add_option( 'foo', 'bar', '', true );
		$updated = update_option( 'foo', 'bar', false );
		$this->assertFalse( $updated );

		$this->flush_cache();

		// Populate the alloptions cache, which includes autoload=yes options.
		wp_load_alloptions();

		$before = get_num_queries();
		$value  = get_option( 'foo' );

		// 'foo' should still be autoload=yes, so we should see no additional querios.
		$this->assertSame( $before, get_num_queries() );
		$this->assertSame( $value, 'bar' );
	}

	/**
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '26394' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'update_option' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'wp_load_alloptions' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'get_option' )]
	public function test_autoload_should_not_be_updated_for_existing_option_when_value_is_changed_but_no_value_of_autoload_is_provided() {
		add_option( 'foo', 'bar', '', true );

		// Don't pass a value for `$autoload`.
		$updated = update_option( 'foo', 'bar2' );
		$this->assertTrue( $updated );

		$this->flush_cache();

		// Populate the alloptions cache, which includes autoload=yes options.
		wp_load_alloptions();

		$before = get_num_queries();
		$value  = get_option( 'foo' );

		// 'foo' should still be autoload=yes, so we should see no additional queries.
		$this->assertSame( $before, get_num_queries() );
		$this->assertSame( $value, 'bar2' );
	}

	/**
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '38903' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'add_option' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'get_num_queries' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'update_option' )]
	public function test_update_option_array_with_object() {
		$array_w_object = array(
			'url'       => 'http://src.wordpress-develop.dev/wp-content/uploads/2016/10/cropped-Blurry-Lights.jpg',
			'meta_data' => (object) array(
				'attachment_id' => 292,
				'height'        => 708,
				'width'         => 1260,
			),
		);

		// Add the option, it did not exist before this.
		add_option( 'array_w_object', $array_w_object );

		$num_queries_pre_update = get_num_queries();

		// Update the option using the same array with an object for the value.
		$this->assertFalse( update_option( 'array_w_object', $array_w_object ) );

		// Check that no new database queries were performed.
		$this->assertSame( $num_queries_pre_update, get_num_queries() );
	}

	/**
	 * `add_filter()` callback for test_should_respect_default_option_filter_when_option_does_not_yet_exist_in_database().
	 */
	public function __return_foo() {
		return 'foo';
	}
}
