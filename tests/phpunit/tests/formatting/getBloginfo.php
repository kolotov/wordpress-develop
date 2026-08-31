<?php

/**
 *
 */
#[\PHPUnit\Framework\Attributes\Group( 'formatting' )]


#[\PHPUnit\Framework\Attributes\CoversFunction( 'get_bloginfo' )]
class Tests_Formatting_GetBloginfo extends WP_UnitTestCase {

	/**
	 */
	#[\PHPUnit\Framework\Attributes\DataProvider( 'data_get_bloginfo_language' )]
	#[\PHPUnit\Framework\Attributes\Ticket( '28303' )]
	public function test_get_bloginfo_language( $test_locale, $expected ) {
		global $locale;

		$old_locale = $locale;

		$locale = $test_locale;
		$this->assertSame( $expected, get_bloginfo( 'language' ) );

		$locale = $old_locale;
	}

	public static function data_get_bloginfo_language() {
		return array(
			// Locale, language code.
			array( 'en_US', 'en-US' ),
			array( 'ar', 'ar' ),
			array( 'de_DE', 'de-DE' ),
			array( 'de_DE_formal', 'de-DE-formal' ),
			array( 'oci', 'oci' ),
			array( 'pt_PT_ao1990', 'pt-PT-ao1990' ),
			array( 'ja_JP', 'ja-JP' ),
		);
	}

	/**
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '27942' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'sanitize_option' )]
	public function test_bloginfo_sanitize_option() {
		$old_values = array(
			'blogname'        => get_option( 'blogname' ),
			'blogdescription' => get_option( 'blogdescription' ),
		);

		$values = array(
			'foo'                  => 'foo',
			'<em>foo</em>'         => '&lt;em&gt;foo&lt;/em&gt;',
			'<script>foo</script>' => '&lt;script&gt;foo&lt;/script&gt;',
			'&lt;foo&gt;'          => '&lt;foo&gt;',
			'<foo'                 => '&lt;foo',
		);

		foreach ( $values as $value => $expected ) {
			$sanitized_value = sanitize_option( 'blogname', $value );
			update_option( 'blogname', $sanitized_value );

			$this->assertSame( $expected, $sanitized_value );
			$this->assertSame( $expected, get_bloginfo( 'name' ) );
			$this->assertSame( $expected, get_bloginfo( 'name', 'display' ) );

			$sanitized_value = sanitize_option( 'blogdescription', $value );
			update_option( 'blogdescription', $sanitized_value );

			$this->assertSame( $expected, $sanitized_value );
			$this->assertSame( $expected, get_bloginfo( 'description' ) );
			$this->assertSame( $expected, get_bloginfo( 'description', 'display' ) );
		}

		// Restore old values.
		foreach ( $old_values as $option_name => $value ) {
			update_option( $option_name, $value );
		}
	}
}
