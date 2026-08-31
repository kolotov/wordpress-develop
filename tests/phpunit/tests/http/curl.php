<?php

require_once __DIR__ . '/base.php';

/**
 */
#[\PHPUnit\Framework\Attributes\CoversFunction( 'wp_remote_request' )]
#[\PHPUnit\Framework\Attributes\Group( 'http' )]
#[\PHPUnit\Framework\Attributes\Group( 'external-http' )]
class Tests_HTTP_curl extends WP_HTTP_UnitTestCase {
	public $transport = 'curl';

	/**
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '39783' )]
	public function test_http_api_curl_stream_parameter_is_a_reference() {
		add_action( 'http_api_curl', array( $this, '_action_test_http_api_curl_stream_parameter_is_a_reference' ), 10, 3 );
		wp_remote_request(
			$this->file_stream_url,
			array(
				'stream'  => true,
				'timeout' => 30,
			)
		);
		remove_action( 'http_api_curl', array( $this, '_action_test_http_api_curl_stream_parameter_is_a_reference' ), 10 );
	}

	public function _action_test_http_api_curl_stream_parameter_is_a_reference( &$stream, $r, $url ) {
		$this->assertInstanceOf( CurlHandle::class, $stream );
		$this->assertIsArray( $r );
		$this->assertSame( $this->file_stream_url, $url );
	}
}
