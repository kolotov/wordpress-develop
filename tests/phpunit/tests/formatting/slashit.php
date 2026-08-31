<?php

/**
 */
#[\PHPUnit\Framework\Attributes\Group( 'formatting' )]



class Tests_Formatting_Slashit extends WP_UnitTestCase {

	/**
	 */
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'backslashit' )]
	public function test_backslashes_middle_numbers() {
		$this->assertSame( "\\a-!9\\a943\\b\\c", backslashit( 'a-!9a943bc' ) );
	}

	/**
	 */
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'backslashit' )]
	public function test_backslashes_alphas() {
		$this->assertSame( "\\a943\\b\\c", backslashit( 'a943bc' ) );
	}

	/**
	 */
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'backslashit' )]
	public function test_double_backslashes_leading_numbers() {
		$this->assertSame( '\\\\95', backslashit( '95' ) );
	}

	/**
	 */
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'untrailingslashit' )]
	public function test_removes_trailing_slashes() {
		$this->assertSame( 'a', untrailingslashit( 'a/' ) );
		$this->assertSame( 'a', untrailingslashit( 'a////' ) );
	}

	/**
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '22267' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'untrailingslashit' )]
	public function test_removes_trailing_backslashes() {
		$this->assertSame( 'a', untrailingslashit( 'a\\' ) );
		$this->assertSame( 'a', untrailingslashit( 'a\\\\\\\\' ) );
	}

	/**
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '22267' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'untrailingslashit' )]
	public function test_removes_trailing_mixed_slashes() {
		$this->assertSame( 'a', untrailingslashit( 'a/\\' ) );
		$this->assertSame( 'a', untrailingslashit( 'a\\/\\///\\\\//' ) );
	}

	/**
	 */
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'trailingslashit' )]
	public function test_adds_trailing_slash() {
		$this->assertSame( 'a/', trailingslashit( 'a' ) );
	}

	/**
	 */
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'trailingslashit' )]
	public function test_does_not_add_trailing_slash_if_one_exists() {
		$this->assertSame( 'a/', trailingslashit( 'a/' ) );
	}

	/**
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '22267' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'trailingslashit' )]
	public function test_converts_trailing_backslash_to_slash_if_one_exists() {
		$this->assertSame( 'a/', trailingslashit( 'a\\' ) );
	}
}
