<?php

/**
 *
 */
#[\PHPUnit\Framework\Attributes\Group( 'l10n' )]
#[\PHPUnit\Framework\Attributes\Group( 'i18n' )]
#[\PHPUnit\Framework\Attributes\CoversFunction( 'wp_get_word_count_type' )]
class Tests_L10n_wpGetWordCountType extends WP_UnitTestCase {

	/**
	 * Tests that the function returns a value when the $wp_locale global is not set.
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '56698' )]
	public function test_should_return_default_value_if_wp_locale_is_not_set() {
		global $wp_locale;

		$original_locale = $wp_locale;
		$wp_locale       = null;

		$actual = wp_get_word_count_type();

		$wp_locale = $original_locale;

		$this->assertSame( 'words', $actual );
	}
}
