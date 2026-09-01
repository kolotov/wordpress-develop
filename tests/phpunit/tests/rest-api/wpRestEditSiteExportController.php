<?php
/**
 * Unit tests covering WP_REST_Edit_Site_Export_Controller functionality.
 *
 * @package WordPress
 * @subpackage REST_API
 * @since 5.9.0
 *
 *

 */
#[\PHPUnit\Framework\Attributes\Group( 'restapi' )]
#[\PHPUnit\Framework\Attributes\CoversClass( WP_REST_Edit_Site_Export_Controller::class )]
class Tests_REST_WpRestEditSiteExportController extends WP_Test_REST_Controller_Testcase {

	/**
	 * The REST API route for the edit site export.
	 *
	 * @since 5.9.0
	 *
	 * @var string
	 */
	const REQUEST_ROUTE = '/wp-block-editor/v1/export';

	/**
	 * Subscriber user ID.
	 *
	 * @since 5.9.0
	 *
	 * @var int
	 */
	protected static $subscriber_id;

	/**
	 * Set up class test fixtures.
	 *
	 * @since 5.9.0
	 *
	 * @param WP_UnitTest_Factory $factory WordPress unit test factory.
	 */
	public static function wpSetUpBeforeClass( WP_UnitTest_Factory $factory ) {
		self::$subscriber_id = $factory->user->create(
			array(
				'role' => 'subscriber',
			)
		);
	}

	/**
	 * Delete test data after our tests run.
	 *
	 * @since 5.9.0
	 */
	public static function wpTearDownAfterClass() {
		self::delete_user( self::$subscriber_id );
	}

	#[\PHPUnit\Framework\Attributes\Ticket( '54448' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_REST_Edit_Site_Export_Controller', 'register_routes' )]
	public function test_register_routes() {
		$routes = rest_get_server()->get_routes();
		$this->assertArrayHasKey( static::REQUEST_ROUTE, $routes );
		$this->assertCount( 1, $routes[ static::REQUEST_ROUTE ] );
	}

	#[\PHPUnit\Framework\Attributes\Ticket( '54448' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_REST_Edit_Site_Export_Controller', 'permissions_check' )]
	public function test_export_for_no_user_permissions() {
		wp_set_current_user( 0 );

		$request  = new WP_REST_Request( 'GET', static::REQUEST_ROUTE );
		$response = rest_get_server()->dispatch( $request );

		$this->assertErrorResponse( 'rest_cannot_export_templates', $response, 401 );
	}

	#[\PHPUnit\Framework\Attributes\Ticket( '54448' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_REST_Edit_Site_Export_Controller', 'permissions_check' )]
	public function test_export_for_user_with_insufficient_permissions() {
		wp_set_current_user( self::$subscriber_id );

		$request  = new WP_REST_Request( 'GET', static::REQUEST_ROUTE );
		$response = rest_get_server()->dispatch( $request );

		$this->assertErrorResponse( 'rest_cannot_export_templates', $response, 403 );
	}

	#[\PHPUnit\Framework\Attributes\DoesNotPerformAssertions]
	public function test_context_param() {
		// Controller does not use get_context_param().
	}

	#[\PHPUnit\Framework\Attributes\DoesNotPerformAssertions]
	public function test_get_item() {
		// Controller does not implement get_item().
	}

	#[\PHPUnit\Framework\Attributes\DoesNotPerformAssertions]
	public function test_get_items() {
		// Controller does not implement get_items().
	}

	#[\PHPUnit\Framework\Attributes\DoesNotPerformAssertions]
	public function test_create_item() {
		// Controller does not implement create_item().
	}

	#[\PHPUnit\Framework\Attributes\DoesNotPerformAssertions]
	public function test_update_item() {
		// Controller does not implement update_item().
	}

	#[\PHPUnit\Framework\Attributes\DoesNotPerformAssertions]
	public function test_delete_item() {
		// Controller does not implement delete_item().
	}

	#[\PHPUnit\Framework\Attributes\DoesNotPerformAssertions]
	public function test_prepare_item() {
		// Controller does not implement prepare_item().
	}

	#[\PHPUnit\Framework\Attributes\DoesNotPerformAssertions]
	public function test_get_item_schema() {
		// Controller does not implement get_item_schema().
	}
}
