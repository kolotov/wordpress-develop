<?php
/**
 * Tests for the wp_privacy_exports_url function.
 *
 *
 */
#[\PHPUnit\Framework\Attributes\CoversFunction( 'wp_privacy_exports_url' )]
#[\PHPUnit\Framework\Attributes\Group( 'functions' )]
class Tests_Functions_wpPrivacyExportsUrl extends WP_UnitTestCase {

	/**
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '59709' )]
	public function test_wp_privacy_exports_url() {
		$upload_dir = wp_upload_dir();
		$this->assertSame( trailingslashit( $upload_dir['baseurl'] ) . 'wp-personal-data-exports/', wp_privacy_exports_url() );
	}

	/**
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '59709' )]
	public function test_wp_privacy_exports_url_filtered() {
		add_filter( 'wp_privacy_exports_url', array( $this, 'filter_wp_privacy_exports_url' ) );

		$upload_dir   = wp_upload_dir();
		$expected_url = trailingslashit( $upload_dir['baseurl'] ) . 'filtered-exports/';
		$actual_url   = wp_privacy_exports_url();
		$this->assertSame( $expected_url, $actual_url );
	}

	/**
	 * Filters the personal data exports directory URL for tests.
	 *
	 * @param string $exports_url Default exports directory URL.
	 * @return string Filtered exports directory URL.
	 */
	public function filter_wp_privacy_exports_url( $exports_url ) {
		return str_replace( 'wp-personal-data-exports/', 'filtered-exports/', $exports_url );
	}
}
