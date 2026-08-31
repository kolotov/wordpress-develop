<?php

/**
 */










#[\PHPUnit\Framework\Attributes\Group( 'http' )]
#[\PHPUnit\Framework\Attributes\Group( 'external-http' )]
class Tests_HTTP_Functions extends WP_UnitTestCase {

	/**
	 */
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'wp_remote_head' )]
	public function test_head_request() {
		// This URL gives a direct 200 response.
		$url      = 'https://s.w.org/screenshots/3.9/dashboard.png';
		$response = $this->wp_remote_head( $url );

		$this->assertNotWPError( $response );

		$headers = wp_remote_retrieve_headers( $response );

		$this->assertIsArray( $response );
		$this->assertSame( 200, wp_remote_retrieve_response_code( $response ) );
		$this->assertSame( 'image/png', $headers['Content-Type'] );
		$this->assertSame( '153204', $headers['Content-Length'] );
	}

	/**
	 */
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'wp_remote_head' )]
	public function test_head_redirect() {
		// This URL will 301 redirect.
		$url      = 'https://wp.org/screenshots/3.9/dashboard.png';
		$response = $this->wp_remote_head( $url );

		$this->assertNotWPError( $response );
		$this->assertSame( 301, wp_remote_retrieve_response_code( $response ) );
	}

	/**
	 */
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'wp_remote_head' )]
	public function test_head_404() {
		$url      = 'https://wordpress.org/screenshots/3.9/awefasdfawef.jpg';
		$response = $this->wp_remote_head( $url );

		$this->assertNotWPError( $response );
		$this->assertSame( 404, wp_remote_retrieve_response_code( $response ) );
	}

	/**
	 */
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'wp_remote_get' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'wp_remote_retrieve_headers' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'wp_remote_retrieve_response_code' )]
	public function test_get_request() {
		$url = 'https://s.w.org/screenshots/3.9/dashboard.png';

		$response = $this->wp_remote_get( $url );

		$this->assertNotWPError( $response );

		$headers = wp_remote_retrieve_headers( $response );

		// Should return the same headers as a HEAD request.
		$this->assertSame( 200, wp_remote_retrieve_response_code( $response ) );
		$this->assertSame( 'image/png', $headers['Content-Type'] );
		$this->assertSame( '153204', $headers['Content-Length'] );
	}

	/**
	 */
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'wp_remote_get' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'wp_remote_retrieve_headers' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'wp_remote_retrieve_response_code' )]
	public function test_get_redirect() {
		// This will redirect to wordpress.org.
		$url = 'https://wp.org/screenshots/3.9/dashboard.png';

		$response = $this->wp_remote_get( $url );

		$this->assertNotWPError( $response );

		$headers = wp_remote_retrieve_headers( $response );

		// Should return the same headers as a HEAD request.
		$this->assertSame( 200, wp_remote_retrieve_response_code( $response ) );
		$this->assertSame( 'image/png', $headers['Content-Type'] );
		$this->assertSame( '153204', $headers['Content-Length'] );
	}

	/**
	 */
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'wp_remote_get' )]
	public function test_get_redirect_limit_exceeded() {
		// This will redirect to wordpress.org.
		$url = 'https://wp.org/screenshots/3.9/dashboard.png';

		// Pretend we've already redirected 5 times.
		$response = $this->wp_remote_get( $url, array( 'redirection' => -1 ) );

		$this->assertWPError( $response );
	}

	/**
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '33711' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'wp_remote_head' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'wp_remote_retrieve_cookies' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'wp_remote_retrieve_cookie' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'wp_remote_retrieve_cookie_value' )]
	public function test_get_response_cookies() {
		$url = 'https://login.wordpress.org/wp-login.php';

		$response = $this->wp_remote_head( $url );

		$this->assertNotWPError( $response );

		$cookies = wp_remote_retrieve_cookies( $response );

		$this->assertNotEmpty( $cookies );

		$cookie = wp_remote_retrieve_cookie( $response, 'wordpress_test_cookie' );
		$this->assertInstanceOf( 'WP_Http_Cookie', $cookie );
		$this->assertSame( 'wordpress_test_cookie', $cookie->name );
		$this->assertSame( 'WP Cookie check', $cookie->value );

		$value = wp_remote_retrieve_cookie_value( $response, 'wordpress_test_cookie' );
		$this->assertSame( 'WP Cookie check', $value );

		$no_value = wp_remote_retrieve_cookie_value( $response, 'not_a_cookie' );
		$this->assertSame( '', $no_value );

		$no_cookie = wp_remote_retrieve_cookie( $response, 'not_a_cookie' );
		$this->assertSame( '', $no_cookie );
	}

	/**
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '37437' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'wp_remote_get' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'wp_remote_retrieve_cookies' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'wp_remote_retrieve_cookie' )]
	public function test_get_response_cookies_with_wp_http_cookie_object() {
		$url = 'https://login.wordpress.org/wp-login.php';

		$response = $this->wp_remote_get(
			$url,
			array(
				'cookies' => array(
					new WP_Http_Cookie(
						array(
							'name'  => 'test',
							'value' => 'foo',
						)
					),
				),
			)
		);

		$this->assertNotWPError( $response );

		$cookies = wp_remote_retrieve_cookies( $response );

		$this->assertNotEmpty( $cookies );

		$cookie = wp_remote_retrieve_cookie( $response, 'test' );
		$this->assertInstanceOf( 'WP_Http_Cookie', $cookie );
		$this->assertSame( 'test', $cookie->name );
		$this->assertSame( 'foo', $cookie->value );
	}

	/**
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '37437' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'wp_remote_get' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'wp_remote_retrieve_cookies' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'wp_remote_retrieve_cookie' )]
	public function test_get_response_cookies_with_name_value_array() {
		$url = 'https://login.wordpress.org/wp-login.php';

		$response = $this->wp_remote_get(
			$url,
			array(
				'cookies' => array(
					'test' => 'foo',
				),
			)
		);

		$this->assertNotWPError( $response );

		$cookies = wp_remote_retrieve_cookies( $response );

		$this->assertNotEmpty( $cookies );

		$cookie = wp_remote_retrieve_cookie( $response, 'test' );
		$this->assertInstanceOf( 'WP_Http_Cookie', $cookie );
		$this->assertSame( 'test', $cookie->name );
		$this->assertSame( 'foo', $cookie->value );
	}

	/**
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '43231' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_HTTP_Requests_Response', '__construct' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Http_Cookie', '__construct' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Http', 'normalize_cookies' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'wp_remote_retrieve_cookies' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'wp_remote_retrieve_cookie' )]
	public function test_get_cookie_host_only() {
		// Emulate WP_Http::request() internals.
		$requests_response = new WpOrg\Requests\Response();

		$requests_response->cookies['test'] = WpOrg\Requests\Cookie::parse( 'test=foo; domain=.wordpress.org' );

		$requests_response->cookies['test']->flags['host-only'] = false; // https://github.com/WordPress/Requests/issues/306

		$http_response = new WP_HTTP_Requests_Response( $requests_response );

		$response = $http_response->to_array();

		// Check the host_only flag in the resulting WP_Http_Cookie.
		$cookie = wp_remote_retrieve_cookie( $response, 'test' );
		$this->assertSame( $cookie->domain, 'wordpress.org' );
		$this->assertFalse( $cookie->host_only, 'host-only flag not set' );

		// Regurgitate (WpOrg\Requests\Cookie -> WP_Http_Cookie -> WpOrg\Requests\Cookie).
		$cookies = WP_Http::normalize_cookies( wp_remote_retrieve_cookies( $response ) );
		$this->assertFalse( $cookies['test']->flags['host-only'], 'host-only flag data lost' );
	}
}
