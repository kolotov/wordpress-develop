<?php
/**
 * Unit tests covering PHPMailer translations.
 *
 * @package WordPress
 * @subpackage PHPMailer
 * @since 6.8.0
 */

/**
 * Class Test_PHPMailer_Translations.
 *
 * Provides tests for PHPMailer translations.
 *
 *
 * @since 6.8.0
 */
#[\PHPUnit\Framework\Attributes\Group( 'mail' )]
#[\PHPUnit\Framework\Attributes\Group( 'i18n' )]
#[\PHPUnit\Framework\Attributes\Group( 'l10n' )]
class Test_PHPMailer_Translations extends WP_UnitTestCase {
	/**
	 * Tests that PHPMailer error message translation works as expected.
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '23311' )]
	public function test_missing_recipient_error_message_should_be_translated() {
		reset_phpmailer_instance();

		$is_switched = switch_to_locale( 'de_DE' );

		$phpmailer = tests_retrieve_phpmailer_instance();
		$phpmailer->setFrom( 'invalid-email@example.com' );

		try {
			$phpmailer->send();
			$this->fail( 'Expected exception was not thrown' );
		} catch ( PHPMailer\PHPMailer\Exception $e ) {
			$error_message = $e->getMessage();
		} finally {
			if ( $is_switched ) {
				restore_previous_locale();
			}
		}

		$this->assertSame(
			'Du musst mindestens eine Empfänger-E-Mail-Adresse angeben.',
			$error_message,
			'Error message is not translated as expected'
		);
	}

	/**
	 * Test that PHPMailer error message keys are consistent across implementations.
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '23311' )]
	public function test_all_error_message_keys_should_be_translated() {
		reset_phpmailer_instance();

		$phpmailer    = new PHPMailer\PHPMailer\PHPMailer();
		$wp_phpmailer = tests_retrieve_phpmailer_instance();

		$this->assertEqualSets( array_keys( $phpmailer->GetTranslations() ), array_keys( $wp_phpmailer->GetTranslations() ) );
	}
}
