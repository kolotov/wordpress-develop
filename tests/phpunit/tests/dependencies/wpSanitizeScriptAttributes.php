<?php

/**
 * Test wp_sanitize_script_attributes().
 *
 */
#[\PHPUnit\Framework\Attributes\Group( 'dependencies' )]
#[\PHPUnit\Framework\Attributes\Group( 'scripts' )]
class Tests_Dependencies_wpSanitizeScriptAttributes extends WP_UnitTestCase {

	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'wp_sanitize_script_attributes' )]
	public function test_sanitize_script_attributes_type_set() {
		$this->setExpectedDeprecated( 'wp_sanitize_script_attributes' );
		$this->assertSame(
			' type="application/javascript" src="https://DOMAIN.TLD/PATH/FILE.js" nomodule',
			wp_sanitize_script_attributes(
				array(
					'type'     => 'application/javascript',
					'src'      => 'https://DOMAIN.TLD/PATH/FILE.js',
					'async'    => false,
					'nomodule' => true,
				)
			)
		);
	}

	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'wp_sanitize_script_attributes' )]
	public function test_sanitize_script_attributes_type_not_set() {
		$this->setExpectedDeprecated( 'wp_sanitize_script_attributes' );
		$this->assertSame(
			' src="https://DOMAIN.TLD/PATH/FILE.js" nomodule',
			wp_sanitize_script_attributes(
				array(
					'src'      => 'https://DOMAIN.TLD/PATH/FILE.js',
					'async'    => false,
					'nomodule' => true,
				)
			)
		);
	}


	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'wp_sanitize_script_attributes' )]
	public function test_sanitize_script_attributes_no_attributes() {
		$this->setExpectedDeprecated( 'wp_sanitize_script_attributes' );
		$this->assertSame(
			'',
			wp_sanitize_script_attributes( array() )
		);
	}

	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'wp_sanitize_script_attributes' )]
	public function test_sanitize_script_attributes_relative_src() {
		$this->setExpectedDeprecated( 'wp_sanitize_script_attributes' );
		$this->assertSame(
			' src="PATH/FILE.js" nomodule',
			wp_sanitize_script_attributes(
				array(
					'src'      => 'PATH/FILE.js',
					'async'    => false,
					'nomodule' => true,
				)
			)
		);
	}


	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'wp_sanitize_script_attributes' )]
	public function test_sanitize_script_attributes_only_false_boolean_attributes() {
		$this->setExpectedDeprecated( 'wp_sanitize_script_attributes' );
		$this->assertSame(
			'',
			wp_sanitize_script_attributes(
				array(
					'async'    => false,
					'nomodule' => false,
				)
			)
		);
	}

	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'wp_sanitize_script_attributes' )]
	public function test_sanitize_script_attributes_only_true_boolean_attributes() {
		$this->setExpectedDeprecated( 'wp_sanitize_script_attributes' );
		$this->assertSame(
			' async nomodule',
			wp_sanitize_script_attributes(
				array(
					'async'    => true,
					'nomodule' => true,
				)
			)
		);
	}
}
