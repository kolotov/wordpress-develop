<?php

/**
 * Tests for the wp_autoload_values_to_autoload function.
 *
 *
 */
#[\PHPUnit\Framework\Attributes\Group( 'Option' )]
#[\PHPUnit\Framework\Attributes\CoversFunction( 'wp_autoload_values_to_autoload' )]
class Tests_Option_wpAutoloadValuesToAutoload extends WP_UnitTestCase {

	/**
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '42441' )]
	public function test_wp_autoload_values_to_autoload() {
		$this->assertSameSets( array( 'yes', 'on', 'auto-on', 'auto' ), wp_autoload_values_to_autoload() );
	}

	/**
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '42441' )]
	public function test_wp_autoload_values_to_autoload_filter_remove() {

		add_filter(
			'wp_autoload_values_to_autoload',
			static function () {
				return array( 'yes' );
			}
		);

		$this->assertSameSets( array( 'yes' ), wp_autoload_values_to_autoload() );
	}

	/**
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '42441' )]
	public function test_wp_autoload_values_to_autoload_filter_extra() {

		add_filter(
			'wp_autoload_values_to_autoload',
			static function () {
				return array( 'yes', 'on', 'auto-on', 'auto', 'extra' );
			}
		);

		$this->assertSameSets( array( 'yes', 'on', 'auto-on', 'auto' ), wp_autoload_values_to_autoload() );
	}

	/**
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '42441' )]
	public function test_wp_autoload_values_to_autoload_filter_replace() {

		add_filter(
			'wp_autoload_values_to_autoload',
			static function () {
				return array( 'yes', 'on', 'auto-on', 'extra' );
			}
		);

		$this->assertSameSets( array( 'yes', 'on', 'auto-on' ), wp_autoload_values_to_autoload() );
	}
}
