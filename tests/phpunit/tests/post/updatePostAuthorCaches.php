<?php
/**
 * Test `update_post_author_caches()`.
 *
 * @package WordPress
 */

/**
 * Test class for `update_post_author_caches()`.
 *
 *
 */
#[\PHPUnit\Framework\Attributes\Group( 'post' )]
#[\PHPUnit\Framework\Attributes\Group( 'query' )]
#[\PHPUnit\Framework\Attributes\Group( 'user' )]
#[\PHPUnit\Framework\Attributes\CoversFunction( 'update_post_author_caches' )]
class Tests_Post_UpdatePostAuthorCaches extends WP_UnitTestCase {

	/**
	 * User IDs from the shared fixture.
	 *
	 * @var int[]
	 */
	public static $user_ids;

	/**
	 * Post author count.
	 *
	 * @var int
	 */
	public static $post_author_count = 5;

	/**
	 * Set up test resources before the class.
	 *
	 * @param WP_UnitTest_Factory $factory The unit test factory.
	 */
	public static function wpSetupBeforeClass( WP_UnitTest_Factory $factory ) {
		self::$user_ids = array();

		for ( $i = 0; $i < self::$post_author_count; $i++ ) {
			self::$user_ids[ $i ] = $factory->user->create();
			$factory->post->create(
				array(
					'post_type'   => 'post',
					'post_author' => self::$user_ids[ $i ],
				)
			);
		}
	}

	#[\PHPUnit\Framework\Attributes\Ticket( '55716' )]
	public function test_update_post_author_caches() {
		$action = new MockAction();
		add_filter( 'update_user_metadata_cache', array( $action, 'filter' ), 10, 2 );

		$q = new WP_Query(
			array(
				'post_type'      => 'post',
				'posts_per_page' => self::$post_author_count,
			)
		);

		while ( $q->have_posts() ) {
			$q->the_post();
		}

		$args      = $action->get_args();
		$last_args = end( $args );

		$this->assertSameSets( self::$user_ids, $last_args[1], 'Ensure that user IDs are primed' );
	}
}
