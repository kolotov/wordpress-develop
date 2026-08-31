<?php
/**
 * Tests for Block Bindings API helper functions.
 *
 * @package WordPress
 * @subpackage Blocks
 * @since 6.5.0
 *
 */
#[\PHPUnit\Framework\Attributes\Group( 'blocks' )]
#[\PHPUnit\Framework\Attributes\Group( 'block-bindings' )]





class Tests_Block_Bindings_Register extends WP_UnitTestCase {

	public static $test_source_name       = 'test/source';
	public static $test_source_properties = array();

	/**
	 * Set up before each test.
	 *
	 * @since 6.5.0
	 */
	public function set_up() {
		parent::set_up();

		self::$test_source_properties = array(
			'label'              => 'Test source',
			'get_value_callback' => function () {
				return 'test-value';
			},
		);
	}

	/**
	 * Tear down after each test.
	 *
	 * @since 6.5.0
	 */
	public function tear_down() {
		foreach ( get_all_registered_block_bindings_sources() as $source_name => $source_properties ) {
			if ( str_starts_with( $source_name, 'test/' ) ) {
				unregister_block_bindings_source( $source_name );
			}
		}

		parent::tear_down();
	}

	private function assert_test_source( $expected_name, $source ) {
		$this->assertInstanceOf( WP_Block_Bindings_Source::class, $source );
		$this->assertSame( $expected_name, $source->name );
		$this->assertSame( 'Test source', $source->label );
		$this->assertNull( $source->uses_context );

		$callback_property = new ReflectionProperty( WP_Block_Bindings_Source::class, 'get_value_callback' );
		$this->assertSame( self::$test_source_properties['get_value_callback'], $callback_property->getValue( $source ) );
		$this->assertSame( 'test-value', $source->get_value( array(), null, 'content' ) );
	}

	/**
	 * Should find all registered sources.
	 *
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '60282' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'register_block_bindings_source' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'get_all_registered_block_bindings_sources' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'get_block_bindings_source' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Block_Bindings_Source', '__construct' )]
	public function test_get_all_registered() {
		$source_one_name       = 'test/source-one';
		$source_one_properties = self::$test_source_properties;
		register_block_bindings_source( $source_one_name, $source_one_properties );

		$source_two_name       = 'test/source-two';
		$source_two_properties = self::$test_source_properties;
		register_block_bindings_source( $source_two_name, $source_two_properties );

		$source_three_name       = 'test/source-three';
		$source_three_properties = self::$test_source_properties;
		register_block_bindings_source( $source_three_name, $source_three_properties );

		$expected = array(
			$source_one_name         => new WP_Block_Bindings_Source( $source_one_name, $source_one_properties ),
			$source_two_name         => new WP_Block_Bindings_Source( $source_two_name, $source_two_properties ),
			$source_three_name       => new WP_Block_Bindings_Source( $source_three_name, $source_three_properties ),
			'core/post-data'         => get_block_bindings_source( 'core/post-data' ),
			'core/post-meta'         => get_block_bindings_source( 'core/post-meta' ),
			'core/pattern-overrides' => get_block_bindings_source( 'core/pattern-overrides' ),
			'core/term-data'         => get_block_bindings_source( 'core/term-data' ),
		);

		$registered              = get_all_registered_block_bindings_sources();
		$expected_source_names   = array_keys( $expected );
		$registered_source_names = array_keys( $registered );
		sort( $expected_source_names );
		sort( $registered_source_names );
		$this->assertSame( $expected_source_names, $registered_source_names );

		foreach ( array( $source_one_name, $source_two_name, $source_three_name ) as $source_name ) {
			$this->assert_test_source( $source_name, $registered[ $source_name ] );
		}

		foreach ( array( 'core/post-data', 'core/post-meta', 'core/pattern-overrides', 'core/term-data' ) as $source_name ) {
			$this->assertSame( $expected[ $source_name ], $registered[ $source_name ] );
		}
	}

	/**
	 * Should unregister existing block binding source.
	 *
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '60282' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'register_block_bindings_source' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'unregister_block_bindings_source' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Block_Bindings_Source', '__construct' )]
	public function test_unregister_block_source() {
		register_block_bindings_source( self::$test_source_name, self::$test_source_properties );

		$result = unregister_block_bindings_source( self::$test_source_name );
		$this->assert_test_source( self::$test_source_name, $result );
	}
}
