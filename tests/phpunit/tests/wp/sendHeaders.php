<?php

/**
 *
 */
#[\PHPUnit\Framework\Attributes\Group( 'wp' )]
#[\PHPUnit\Framework\Attributes\CoversMethod( WP::class, 'send_headers' )]
class Tests_WP_SendHeaders extends WP_UnitTestCase {
	protected $headers_sent = array();

	/**
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '56068' )]
	public function test_send_headers_runs_after_posts_have_been_queried() {
		add_action(
			'send_headers',
			function ( $wp ) {
				$this->assertQueryTrue( 'is_front_page', 'is_home' );
			}
		);

		$this->go_to( home_url() );
	}

	/**
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '56840' )]
	public function test_send_headers_sets_x_pingback_for_single_posts_that_allow_pings() {
		add_action(
			'wp_headers',
			function ( $headers ) {
				$this->assertArrayHasKey( 'X-Pingback', $headers );
			}
		);

		$post_id = self::factory()->post->create();
		$this->go_to( get_permalink( $post_id ) );
	}

	/**
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '61711' )]
	public function test_send_headers_sets_cache_control_header_for_password_protected_posts() {
		$password                 = 'password';
		$cookie_name              = 'wp-postpass_' . COOKIEHASH;
		$password_cookie_existed  = array_key_exists( $cookie_name, $_COOKIE );
		$original_password_cookie = $_COOKIE[ $cookie_name ] ?? null;

		add_filter(
			'wp_headers',
			function ( $headers ) {
				$this->headers_sent = $headers;
				return $headers;
			}
		);

		$post_id = self::factory()->post->create(
			array(
				'post_password' => $password,
			)
		);
		$this->go_to( get_permalink( $post_id ) );

		$headers_without_password         = $this->headers_sent;
		$password_status_without_password = post_password_required( $post_id );

		require_once ABSPATH . WPINC . '/class-phpass.php';

		$hash = ( new PasswordHash( 8, true ) )->HashPassword( $password );

		$_COOKIE[ $cookie_name ] = $hash;

		$this->go_to( get_permalink( $post_id ) );

		$headers_with_password         = $this->headers_sent;
		$password_status_with_password = post_password_required( $post_id );

		if ( $password_cookie_existed ) {
			$_COOKIE[ $cookie_name ] = $original_password_cookie;
		} else {
			unset( $_COOKIE[ $cookie_name ] );
		}

		$this->assertTrue( $password_status_without_password );
		$this->assertArrayHasKey( 'Cache-Control', $headers_without_password );

		$this->assertFalse( $password_status_with_password );
		$this->assertArrayHasKey( 'Cache-Control', $headers_with_password );
	}
}
