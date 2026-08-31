<?php

/**
 *
 */
#[\PHPUnit\Framework\Attributes\Group( 'admin' )]
#[\PHPUnit\Framework\Attributes\CoversClass( WP_Plugin_Install_List_Table::class )]
#[\PHPUnit\Framework\Attributes\CoversMethod( WP_Plugin_Install_List_Table::class, 'get_views' )]
class Tests_Admin_wpPluginInstallListTable extends WP_UnitTestCase {
	/**
	 * @var WP_Plugin_Install_List_Table
	 */
	public $table = false;

	public function set_up() {
		parent::set_up();
		$this->table = _get_list_table( 'WP_Plugin_Install_List_Table', array( 'screen' => 'plugin-install' ) );
	}

	/**
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '42066' )]
	public function test_get_views_should_return_no_views_by_default() {
		$this->assertSame( array(), $this->table->get_views() );
	}
}
