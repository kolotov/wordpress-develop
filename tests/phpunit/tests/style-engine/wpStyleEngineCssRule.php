<?php
/**
 * Tests the Style Engine CSS Rule class.
 *
 * @package WordPress
 * @subpackage StyleEngine
 * @since 6.1.0
 *

 */

/**
 * Tests for registering, storing and generating CSS rules.
 *
 */
#[\PHPUnit\Framework\Attributes\Group( 'style-engine' )]






class Tests_Style_Engine_wpStyleEngineCSSRule extends WP_UnitTestCase {
	/**
	 * Tests that declarations are set on instantiation.
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '56467' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Style_Engine_CSS_Rule', '__construct' )]
	public function test_should_instantiate_with_selector_and_rules() {
		$selector           = '.law-and-order';
		$input_declarations = array(
			'margin-top' => '10px',
			'font-size'  => '2rem',
		);
		$css_declarations   = new WP_Style_Engine_CSS_Declarations( $input_declarations );
		$css_rule           = new WP_Style_Engine_CSS_Rule( $selector, $css_declarations );

		$this->assertSame( $selector, $css_rule->get_selector(), 'Return value of get_selector() does not match value passed to constructor.' );

		$expected = "$selector{{$css_declarations->get_declarations_string()}}";

		$this->assertSame( $expected, $css_rule->get_css(), 'Value returned by get_css() does not match expected declarations string.' );
	}

	/**
	 * Tests setting and getting a rules group.
	 *
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '61099' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Style_Engine_CSS_Rule', 'set_rules_group' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Style_Engine_CSS_Rule', 'get_rules_group' )]
	public function test_should_set_rules_group() {
		$rule = new WP_Style_Engine_CSS_Rule( '.heres-johnny', array(), '@layer state' );

		$this->assertSame( '@layer state', $rule->get_rules_group(), 'Return value of get_rules_group() does not match value passed to constructor.' );

		$rule->set_rules_group( '@layer pony' );

		$this->assertSame( '@layer pony', $rule->get_rules_group(), 'Return value of get_rules_group() does not match value passed to set_rules_group().' );
	}

	/**
	 * Tests that declaration properties are deduplicated.
	 *
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '56467' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Style_Engine_CSS_Rule', 'add_declarations' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Style_Engine_CSS_Rule', 'get_css' )]
	public function test_should_dedupe_properties_in_rules() {
		$selector                    = '.taggart';
		$first_declaration           = array(
			'font-size' => '2rem',
		);
		$overwrite_first_declaration = array(
			'font-size' => '4px',
		);
		$css_rule                    = new WP_Style_Engine_CSS_Rule( $selector, $first_declaration );
		$css_rule->add_declarations( new WP_Style_Engine_CSS_Declarations( $overwrite_first_declaration ) );

		$expected = '.taggart{font-size:4px;}';

		$this->assertSame( $expected, $css_rule->get_css() );
	}

	/**
	 * Tests that declarations can be added to existing rules.
	 *
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '56467' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Style_Engine_CSS_Rule', 'add_declarations' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Style_Engine_CSS_Rule', 'get_css' )]
	public function test_should_add_declarations_to_existing_rules() {
		// Declarations using a WP_Style_Engine_CSS_Declarations object.
		$some_css_declarations = new WP_Style_Engine_CSS_Declarations( array( 'margin-top' => '10px' ) );
		// Declarations using a property => value array.
		$some_more_css_declarations = array( 'font-size' => '1rem' );
		$css_rule                   = new WP_Style_Engine_CSS_Rule( '.hill-street-blues', $some_css_declarations );
		$css_rule->add_declarations( $some_more_css_declarations );

		$expected = '.hill-street-blues{margin-top:10px;font-size:1rem;}';

		$this->assertSame( $expected, $css_rule->get_css() );
	}

	/**
	 * Tests setting a selector to a rule.
	 *
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '56467' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Style_Engine_CSS_Rule', 'set_selector' )]
	public function test_should_set_selector() {
		$selector = '.taggart';
		$css_rule = new WP_Style_Engine_CSS_Rule( $selector );

		$this->assertSame( $selector, $css_rule->get_selector(), 'Return value of get_selector() does not match value passed to constructor.' );

		$css_rule->set_selector( '.law-and-order' );

		$this->assertSame( '.law-and-order', $css_rule->get_selector(), 'Return value of get_selector() does not match value passed to set_selector().' );
	}

	/**
	 * Tests generating a CSS rule string.
	 *
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '56467' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Style_Engine_CSS_Rule', 'get_css' )]
	public function test_should_generate_css_rule_string() {
		$selector           = '.chips';
		$input_declarations = array(
			'margin-top' => '10px',
			'font-size'  => '2rem',
		);
		$css_declarations   = new WP_Style_Engine_CSS_Declarations( $input_declarations );
		$css_rule           = new WP_Style_Engine_CSS_Rule( $selector, $css_declarations );
		$expected           = "$selector{{$css_declarations->get_declarations_string()}}";

		$this->assertSame( $expected, $css_rule->get_css() );
	}

	/**
	 * Tests that an empty string will be returned where there are no declarations in a CSS rule.
	 *
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '56467' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Style_Engine_CSS_Rule', 'get_css' )]
	public function test_should_return_empty_string_with_no_declarations() {
		$selector           = '.holmes';
		$input_declarations = array();
		$css_declarations   = new WP_Style_Engine_CSS_Declarations( $input_declarations );
		$css_rule           = new WP_Style_Engine_CSS_Rule( $selector, $css_declarations );

		$this->assertSame( '', $css_rule->get_css() );
	}

	/**
	 * Tests that CSS rules are prettified.
	 *
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '56467' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Style_Engine_CSS_Rule', 'get_css' )]
	public function test_should_prettify_css_rule_output() {
		$selector           = '.baptiste';
		$input_declarations = array(
			'margin-left' => '0',
			'font-family' => 'Detective Sans',
		);
		$css_declarations   = new WP_Style_Engine_CSS_Declarations( $input_declarations );
		$css_rule           = new WP_Style_Engine_CSS_Rule( $selector, $css_declarations );
		$expected           = '.baptiste {
	margin-left: 0;
	font-family: Detective Sans;
}';

		$this->assertSameIgnoreEOL( $expected, $css_rule->get_css( true ) );
	}
}
