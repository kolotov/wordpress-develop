<?php

/**
 *
 */
#[\PHPUnit\Framework\Attributes\Group( 'admin' )]
#[\PHPUnit\Framework\Attributes\Group( 'privacy' )]
#[\PHPUnit\Framework\Attributes\CoversFunction( '_wp_privacy_settings_filter_draft_page_titles' )]
class Tests_Admin_Includes_Misc_WpPrivacySettingsFilterDraftPageTitles_Test extends WP_UnitTestCase {

	/**
	 * Tests that _wp_privacy_settings_filter_draft_page_titles() appends '(Draft)' when appropriate.
	 *
	 *
	 *
	 * @param string $expected    The expected title.
	 * @param string $title       The input title.
	 * @param string $post_status The post status.
	 * @param string $screen_id   The current screen ID.
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '65202' )]
	#[\PHPUnit\Framework\Attributes\DataProvider( 'data_wp_privacy_settings_filter_draft_page_titles' )]
	public function test_wp_privacy_settings_filter_draft_page_titles( $expected, $title, $post_status, $screen_id ) {
		set_current_screen( $screen_id );

		$page = self::factory()->post->create_and_get( array( 'post_status' => $post_status ) );

		$actual = _wp_privacy_settings_filter_draft_page_titles( $title, $page );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * Data provider for test_wp_privacy_settings_filter_draft_page_titles().
	 *
	 * @return array<string, array{
	 *     expected:    string,
	 *     title:       string,
	 *     post_status: string,
	 *     screen_id:   string,
	 * }>
	 */
	public static function data_wp_privacy_settings_filter_draft_page_titles(): array {
		return array(
			'draft page on privacy screen'     => array(
				'expected'    => 'Privacy Policy (Draft)',
				'title'       => 'Privacy Policy',
				'post_status' => 'draft',
				'screen_id'   => 'privacy',
			),
			'published page on privacy screen' => array(
				'expected'    => 'Privacy Policy',
				'title'       => 'Privacy Policy',
				'post_status' => 'publish',
				'screen_id'   => 'privacy',
			),
			'draft page on other screen'       => array(
				'expected'    => 'About Us',
				'title'       => 'About Us',
				'post_status' => 'draft',
				'screen_id'   => 'edit-page',
			),
			'pending page on privacy screen'   => array(
				'expected'    => 'Privacy Policy',
				'title'       => 'Privacy Policy',
				'post_status' => 'pending',
				'screen_id'   => 'privacy',
			),
		);
	}
}
