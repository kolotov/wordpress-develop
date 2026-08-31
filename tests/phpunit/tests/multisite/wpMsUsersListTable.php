<?php

/**
 *
 */
#[\PHPUnit\Framework\Attributes\Group( 'admin' )]
#[\PHPUnit\Framework\Attributes\Group( 'ms-required' )]
#[\PHPUnit\Framework\Attributes\Group( 'network-admin' )]
#[\PHPUnit\Framework\Attributes\CoversClass( WP_MS_Users_List_Table::class )]
#[\PHPUnit\Framework\Attributes\CoversMethod( WP_MS_Users_List_Table::class, 'get_views' )]
class Tests_Multisite_wpMsUsersListTable extends WP_UnitTestCase {
	protected static $site_ids;

	/**
	 * @var WP_MS_Users_List_Table
	 */
	public $table = false;

	public function set_up() {
		parent::set_up();
		$this->table = _get_list_table( 'WP_MS_Users_List_Table', array( 'screen' => 'ms-users' ) );
	}

	public static function wpSetUpBeforeClass( WP_UnitTest_Factory $factory ) {
		self::$site_ids = array(
			'wordpress.org/'          => array(
				'domain' => 'wordpress.org',
				'path'   => '/',
			),
			'wordpress.org/foo/'      => array(
				'domain' => 'wordpress.org',
				'path'   => '/foo/',
			),
			'wordpress.org/foo/bar/'  => array(
				'domain' => 'wordpress.org',
				'path'   => '/foo/bar/',
			),
			'wordpress.org/afoo/'     => array(
				'domain' => 'wordpress.org',
				'path'   => '/afoo/',
			),
			'make.wordpress.org/'     => array(
				'domain' => 'make.wordpress.org',
				'path'   => '/',
			),
			'make.wordpress.org/foo/' => array(
				'domain' => 'make.wordpress.org',
				'path'   => '/foo/',
			),
			'www.w.org/'              => array(
				'domain' => 'www.w.org',
				'path'   => '/',
			),
			'www.w.org/foo/'          => array(
				'domain' => 'www.w.org',
				'path'   => '/foo/',
			),
			'www.w.org/foo/bar/'      => array(
				'domain' => 'www.w.org',
				'path'   => '/foo/bar/',
			),
			'test.example.org/'       => array(
				'domain' => 'test.example.org',
				'path'   => '/',
			),
			'test2.example.org/'      => array(
				'domain' => 'test2.example.org',
				'path'   => '/',
			),
			'test3.example.org/zig/'  => array(
				'domain' => 'test3.example.org',
				'path'   => '/zig/',
			),
			'atest.example.org/'      => array(
				'domain' => 'atest.example.org',
				'path'   => '/',
			),
		);

		foreach ( self::$site_ids as &$id ) {
			$id = $factory->blog->create( $id );
		}
		unset( $id );
	}

	public static function wpTearDownAfterClass() {
		foreach ( self::$site_ids as $site_id ) {
			wp_delete_site( $site_id );
		}
	}

	/**
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '42066' )]
	public function test_get_views_should_return_views_by_default() {
		$all   = get_user_count();
		$super = count( get_super_admins() );

		$expected = array(
			'all'   => '<a href="http://' . WP_TESTS_DOMAIN . '/wp-admin/network/users.php" class="current" aria-current="page">All <span class="count">(' . $all . ')</span></a>',
			'super' => '<a href="http://' . WP_TESTS_DOMAIN . '/wp-admin/network/users.php?role=super">Super Admin <span class="count">(' . $super . ')</span></a>',
		);

		$this->assertSame( $expected, $this->table->get_views() );
	}
}
