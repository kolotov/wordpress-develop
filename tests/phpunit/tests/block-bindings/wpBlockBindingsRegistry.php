<?php
/**
 * Tests for WP_Block_Bindings_Registry.
 *
 * @package WordPress
 * @subpackage Blocks
 * @since 6.5.0
 *
 */
#[\PHPUnit\Framework\Attributes\Group( 'blocks' )]
#[\PHPUnit\Framework\Attributes\Group( 'block-bindings' )]






class Tests_Blocks_wpBlockBindingsRegistry extends WP_UnitTestCase {

	public static $test_source_name       = 'test/source';
	public static $test_source_properties = array();

	/**
	 * Fake block bindings registry.
	 *
	 * @since 6.5.0
	 * @var WP_Block_Bindings_Registry
	 */
	private $registry = null;

	/**
	 * Set up each test method.
	 *
	 * @since 6.5.0
	 */
	public function set_up() {
		parent::set_up();

		$this->registry = new WP_Block_Bindings_Registry();

		self::$test_source_properties = array(
			'label'              => 'Test source',
			'get_value_callback' => function () {
				return 'test-value';
			},
			'uses_context'       => array( 'sourceContext' ),
		);
	}

	/**
	 * Tear down each test method.
	 *
	 * @since 6.5.0
	 */
	public function tear_down() {
		$this->registry = null;

		parent::tear_down();
	}

	/**
	 * Asserts that a registered source retains its public configuration and behavior.
	 *
	 * @param string                   $expected_name Expected source name.
	 * @param WP_Block_Bindings_Source $source        Registered source.
	 */
	private function assert_test_source( $expected_name, $source ) {
		$this->assertSame( $expected_name, $source->name );
		$this->assertSame( 'Test source', $source->label );
		$this->assertSame( array( 'sourceContext' ), $source->uses_context );

		$callback_property = new ReflectionProperty( WP_Block_Bindings_Source::class, 'get_value_callback' );
		$this->assertSame( self::$test_source_properties['get_value_callback'], $callback_property->getValue( $source ) );
		$this->assertSame( 'test-value', $source->get_value( array(), null, '' ) );
	}

	/**
	 * Should reject numbers as block binding source name.
	 *
	 *
	 *
	 * @expectedIncorrectUsage WP_Block_Bindings_Registry::register
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '60282' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Block_Bindings_Registry', 'register' )]
	public function test_register_invalid_non_string_names() {
		$result = $this->registry->register( 1, self::$test_source_properties );
		$this->assertFalse( $result );
	}

	/**
	 * Should reject block binding source name without a namespace.
	 *
	 *
	 *
	 * @expectedIncorrectUsage WP_Block_Bindings_Registry::register
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '60282' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Block_Bindings_Registry', 'register' )]
	public function test_register_invalid_names_without_namespace() {
		$result = $this->registry->register( 'post-meta', self::$test_source_properties );
		$this->assertFalse( $result );
	}

	/**
	 * Should reject block binding source name with invalid characters.
	 *
	 *
	 *
	 * @expectedIncorrectUsage WP_Block_Bindings_Registry::register
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '60282' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Block_Bindings_Registry', 'register' )]
	public function test_register_invalid_characters() {
		$result = $this->registry->register( 'still/_doing_it_wrong', array() );
		$this->assertFalse( $result );
	}

	/**
	 * Should reject block binding source name with uppercase characters.
	 *
	 *
	 *
	 * @expectedIncorrectUsage WP_Block_Bindings_Registry::register
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '60282' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Block_Bindings_Registry', 'register' )]
	public function test_register_invalid_uppercase_characters() {
		$result = $this->registry->register( 'Core/PostMeta', self::$test_source_properties );
		$this->assertFalse( $result );
	}

	/**
	 * Should reject block bindings registration without a label.
	 *
	 *
	 *
	 * @expectedIncorrectUsage WP_Block_Bindings_Registry::register
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '60282' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Block_Bindings_Registry', 'register' )]
	public function test_register_invalid_missing_label() {

		// Remove the label from the properties.
		unset( self::$test_source_properties['label'] );

		$result = $this->registry->register( self::$test_source_name, self::$test_source_properties );
		$this->assertFalse( $result );
	}

	/**
	 * Should reject block bindings registration without a get_value_callback.
	 *
	 *
	 *
	 * @expectedIncorrectUsage WP_Block_Bindings_Registry::register
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '60282' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Block_Bindings_Registry', 'register' )]
	public function test_register_invalid_missing_get_value_callback() {

		// Remove the get_value_callback from the properties.
		unset( self::$test_source_properties['get_value_callback'] );

		$result = $this->registry->register( self::$test_source_name, self::$test_source_properties );
		$this->assertFalse( $result );
	}

	/**
	 * Should reject block bindings registration if `get_value_callback` is not a callable.
	 *
	 *
	 *
	 * @expectedIncorrectUsage WP_Block_Bindings_Registry::register
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '60282' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Block_Bindings_Registry', 'register' )]
	public function test_register_invalid_incorrect_callback_type() {

		self::$test_source_properties['get_value_callback'] = 'not-a-callback';

		$result = $this->registry->register( self::$test_source_name, self::$test_source_properties );
		$this->assertFalse( $result );
	}

	/**
	 * Should reject block bindings registration if `uses_context` is not an array.
	 *
	 *
	 *
	 * @expectedIncorrectUsage WP_Block_Bindings_Registry::register
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '60525' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Block_Bindings_Registry', 'register' )]
	public function test_register_invalid_string_uses_context() {

		self::$test_source_properties['uses_context'] = 'not-an-array';

		$result = $this->registry->register( self::$test_source_name, self::$test_source_properties );
		$this->assertFalse( $result );
	}

	/**
	 * Should accept valid block binding source.
	 *
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '60282' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Block_Bindings_Registry', 'register' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Block_Bindings_Source', '__construct' )]
	public function test_register_block_binding_source() {
		$result = $this->registry->register( self::$test_source_name, self::$test_source_properties );
		$this->assertInstanceOf( WP_Block_Bindings_Source::class, $result );
		$this->assertSame( 'test/source', $result->name );
		$this->assertSame( 'Test source', $result->label );
		$this->assertSame(
			'test-value',
			$result->get_value( array(), null, '' )
		);
		$this->assertSame( array( 'sourceContext' ), $result->uses_context );
	}

	/**
	 * Unregistering should fail if a block binding source is not registered.
	 *
	 *
	 *
	 * @expectedIncorrectUsage WP_Block_Bindings_Registry::unregister
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '60282' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Block_Bindings_Registry', 'unregister' )]
	public function test_unregister_not_registered_block() {
		$result = $this->registry->unregister( 'test/unregistered' );
		$this->assertFalse( $result );
	}

	/**
	 * Should unregister existing block binding source.
	 *
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '60282' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Block_Bindings_Registry', 'register' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Block_Bindings_Registry', 'unregister' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Block_Bindings_Source', '__construct' )]
	public function test_unregister_block_source() {
		$this->registry->register( self::$test_source_name, self::$test_source_properties );

		$result = $this->registry->unregister( self::$test_source_name );
		$this->assertInstanceOf( WP_Block_Bindings_Source::class, $result );
		$this->assert_test_source( self::$test_source_name, $result );
	}

	/**
	 * Should find all registered sources.
	 *
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '60282' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Block_Bindings_Registry', 'register' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Block_Bindings_Registry', 'get_all_registered' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Block_Bindings_Source', '__construct' )]
	public function test_get_all_registered() {
		$source_one_name       = 'test/source-one';
		$source_one_properties = self::$test_source_properties;
		$this->registry->register( $source_one_name, $source_one_properties );

		$source_two_name       = 'test/source-two';
		$source_two_properties = self::$test_source_properties;
		$this->registry->register( $source_two_name, $source_two_properties );

		$source_three_name       = 'test/source-three';
		$source_three_properties = self::$test_source_properties;
		$this->registry->register( $source_three_name, $source_three_properties );

		$registered = $this->registry->get_all_registered();
		$this->assertSame( array( $source_one_name, $source_two_name, $source_three_name ), array_keys( $registered ) );
		foreach ( $registered as $source_name => $source ) {
			$this->assert_test_source( $source_name, $source );
		}
	}

	/**
	 * Should not find source that's not registered.
	 *
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '60282' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Block_Bindings_Registry', 'register' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Block_Bindings_Registry', 'get_registered' )]
	public function test_get_registered_rejects_unknown_source_name() {
		$this->registry->register( self::$test_source_name, self::$test_source_properties );

		$source = $this->registry->get_registered( 'test/unknown-source' );
		$this->assertNull( $source );
	}

	/**
	 * Should find registered block binding source by name.
	 *
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '60282' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Block_Bindings_Registry', 'register' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Block_Bindings_Registry', 'get_registered' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Block_Bindings_Source', '__construct' )]
	public function test_get_registered() {
		$source_one_name       = 'test/source-one';
		$source_one_properties = self::$test_source_properties;
		$this->registry->register( $source_one_name, $source_one_properties );

		$source_two_name       = 'test/source-two';
		$source_two_properties = self::$test_source_properties;
		$this->registry->register( $source_two_name, $source_two_properties );

		$source_three_name       = 'test/source-three';
		$source_three_properties = self::$test_source_properties;
		$this->registry->register( $source_three_name, $source_three_properties );

		$result = $this->registry->get_registered( 'test/source-two' );
		$this->assertInstanceOf( WP_Block_Bindings_Source::class, $result );
		$this->assert_test_source( $source_two_name, $result );
	}

	/**
	 * Should return false for source that's not registered.
	 *
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '60282' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Block_Bindings_Registry', 'is_registered' )]
	public function test_is_registered_for_unknown_source() {
		$result = $this->registry->is_registered( 'test/one' );
		$this->assertFalse( $result );
	}

	/**
	 * Should return true if source is registered.
	 *
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '60282' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Block_Bindings_Registry', 'register' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Block_Bindings_Registry', 'is_registered' )]
	public function test_is_registered_for_known_source() {
		$this->registry->register( self::$test_source_name, self::$test_source_properties );

		$result = $this->registry->is_registered( self::$test_source_name );
		$this->assertTrue( $result );
	}

	/**
	 * Should return false when checking registration with a null source name.
	 *
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '63957' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Block_Bindings_Registry', 'is_registered' )]
	public function test_is_registered_with_null_source_name() {
		$result = $this->registry->is_registered( null );
		$this->assertFalse( $result );
	}
}
