<?php

/**
 *
 */
#[\PHPUnit\Framework\Attributes\Group( 'admin' )]
#[\PHPUnit\Framework\Attributes\CoversClass( WP_Post_Comments_List_Table::class )]
#[\PHPUnit\Framework\Attributes\CoversMethod( WP_Comments_List_Table::class, 'get_views' )]
class Tests_Admin_wpPostCommentsListTable extends WP_UnitTestCase {

	/**
	 * @var WP_Post_Comments_List_Table
	 */
	protected $table;

	public function set_up() {
		parent::set_up();
		$this->table = _get_list_table( 'WP_Post_Comments_List_Table', array( 'screen' => 'edit-post-comments' ) );
	}

	/**
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '42066' )]
	public function test_get_views_should_return_views_by_default() {
		$this->table->prepare_items();

		$expected = array(
			'all'       => '<a href="http://' . WP_TESTS_DOMAIN . '/wp-admin/edit-comments.php?comment_status=all" class="current" aria-current="page">All <span class="count">(<span class="all-count">0</span>)</span></a>',
			'mine'      => '<a href="http://' . WP_TESTS_DOMAIN . '/wp-admin/edit-comments.php?comment_status=mine&#038;user_id=0">Mine <span class="count">(<span class="mine-count">0</span>)</span></a>',
			'moderated' => '<a href="http://' . WP_TESTS_DOMAIN . '/wp-admin/edit-comments.php?comment_status=moderated">Pending <span class="count">(<span class="pending-count">0</span>)</span></a>',
			'approved'  => '<a href="http://' . WP_TESTS_DOMAIN . '/wp-admin/edit-comments.php?comment_status=approved">Approved <span class="count">(<span class="approved-count">0</span>)</span></a>',
			'spam'      => '<a href="http://' . WP_TESTS_DOMAIN . '/wp-admin/edit-comments.php?comment_status=spam">Spam <span class="count">(<span class="spam-count">0</span>)</span></a>',
			'trash'     => '<a href="http://' . WP_TESTS_DOMAIN . '/wp-admin/edit-comments.php?comment_status=trash">Trash <span class="count">(<span class="trash-count">0</span>)</span></a>',
		);
		$this->assertSame( $expected, $this->table->get_views() );
	}
}
