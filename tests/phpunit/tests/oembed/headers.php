<?php

/**
 */
#[\PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses]
#[\PHPUnit\Framework\Attributes\PreserveGlobalState( false )]
#[\PHPUnit\Framework\Attributes\Group( 'oembed' )]
#[\PHPUnit\Framework\Attributes\Group( 'oembed-headers' )]
class Tests_oEmbed_HTTP_Headers extends WP_UnitTestCase {

	public function test_rest_pre_serve_request_headers() {
		$post = self::factory()->post->create_and_get(
			array(
				'post_title' => 'Hello World',
			)
		);

		$request = new WP_REST_Request( 'GET', '/oembed/1.0/embed' );
		$request->set_param( 'url', get_permalink( $post->ID ) );
		$request->set_param( 'format', 'xml' );

		$server   = new Spy_REST_Server();
		$response = $server->dispatch( $request );
		$output   = get_echo( '_oembed_rest_pre_serve_request', array( true, $response, $request, $server ) );

		$this->assertNotEmpty( $output );
		$this->assertSame( 'text/xml; charset=' . get_option( 'blog_charset' ), $server->sent_headers['Content-Type'] );
	}
}
