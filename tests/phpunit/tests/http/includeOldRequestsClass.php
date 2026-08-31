<?php

/**
 * Tests that the old Requests class is included
 * for plugins or themes that still use it.
 *
 */
#[\PHPUnit\Framework\Attributes\Group( 'http' )]
class Tests_HTTP_IncludeOldRequestsClass extends WP_UnitTestCase {

	/**
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '57341' )]
	#[\PHPUnit\Framework\Attributes\CoversNothing]
	public function test_should_include_old_requests_class() {
		$deprecations = array();
		set_error_handler(
			static function ( int $severity, string $message ) use ( &$deprecations ): bool {
				$deprecations[] = compact( 'severity', 'message' );
				return true;
			},
			E_USER_DEPRECATED
		);

		try {
			$loaded = class_exists( 'Requests' );
		} finally {
			restore_error_handler();
		}

		$this->assertTrue( $loaded, 'The legacy Requests class was not loaded.' );
		$this->assertSame(
			array(
				array(
					'severity' => E_USER_DEPRECATED,
					'message'  => 'The PSR-0 `Requests_...` class names in the Requests library are deprecated. Switch to the PSR-4 `WpOrg\Requests\...` class names at your earliest convenience.',
				),
			),
			$deprecations
		);
	}
}
