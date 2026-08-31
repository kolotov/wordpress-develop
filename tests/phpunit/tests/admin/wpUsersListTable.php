<?php

/**
 *
 */
#[\PHPUnit\Framework\Attributes\Group( 'admin' )]
#[\PHPUnit\Framework\Attributes\Group( 'user' )]
#[\PHPUnit\Framework\Attributes\CoversClass( WP_Users_List_Table::class )]
#[\PHPUnit\Framework\Attributes\CoversMethod( WP_Users_List_Table::class, 'get_views' )]
class Tests_Admin_wpUsersListTable extends WP_UnitTestCase {
	/**
	 * @var WP_Users_List_Table
	 */
	public $table = false;

	public function set_up() {
		parent::set_up();
		$this->table = _get_list_table( 'WP_Users_List_Table', array( 'screen' => 'users' ) );
	}

	/**
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '42066' )]
	public function test_get_views_should_return_views_by_default() {
		$expected = array(
			'all'           => '<a href="users.php" class="current" aria-current="page">All <span class="count">(1)</span></a>',
			'administrator' => '<a href="users.php?role=administrator">Administrator <span class="count">(1)</span></a>',
		);

		$this->assertSame( $expected, $this->table->get_views() );
	}
}
