<?php

/**
 *
 */
#[\PHPUnit\Framework\Attributes\Group( 'admin' )]
#[\PHPUnit\Framework\Attributes\CoversClass( WP_Theme_Install_List_Table::class )]
#[\PHPUnit\Framework\Attributes\CoversMethod( WP_Theme_Install_List_Table::class, 'get_views' )]
class Tests_Admin_wpThemeInstallListTable extends WP_UnitTestCase {
	/**
	 * @var WP_Theme_Install_List_Table
	 */
	public $table = false;

	public function set_up() {
		parent::set_up();
		$this->table = _get_list_table( 'WP_Theme_Install_List_Table', array( 'screen' => 'theme-install' ) );
	}

	/**
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '42066' )]
	public function test_get_views_should_return_no_views_by_default() {
		$this->assertSame( array(), $this->table->get_views() );
	}
}
