<?php

#[\PHPUnit\Framework\Attributes\Group( 'admin' )]
class Tests_Admin_wpCommentsListTable extends WP_UnitTestCase {

	/**
	 * @var WP_Comments_List_Table
	 */
	protected $table;

	public function set_up() {
		parent::set_up();
		$this->table = _get_list_table( 'WP_Comments_List_Table', array( 'screen' => 'edit-comments' ) );
	}

	#[\PHPUnit\Framework\Attributes\Ticket( '40188' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Comments_List_Table', 'extra_tablenav' )]
	public function test_filter_button_should_not_be_shown_if_there_are_no_comments() {
		ob_start();
		$this->table->extra_tablenav( 'top' );
		$output = ob_get_clean();

		$this->assertStringNotContainsString( 'id="post-query-submit"', $output );
	}

	#[\PHPUnit\Framework\Attributes\Ticket( '40188' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Comments_List_Table', 'extra_tablenav' )]
	public function test_filter_button_should_be_shown_if_there_are_comments() {
		$post_id    = self::factory()->post->create();
		$comment_id = self::factory()->comment->create(
			array(
				'comment_post_ID'  => $post_id,
				'comment_approved' => '1',
			)
		);

		$this->table->prepare_items();

		ob_start();
		$this->table->extra_tablenav( 'top' );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'id="post-query-submit"', $output );
	}

	#[\PHPUnit\Framework\Attributes\Ticket( '40188' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Comments_List_Table', 'extra_tablenav' )]
	public function test_filter_comment_type_dropdown_should_be_shown_if_there_are_comments() {
		$post_id    = self::factory()->post->create();
		$comment_id = self::factory()->comment->create(
			array(
				'comment_post_ID'  => $post_id,
				'comment_approved' => '1',
			)
		);

		$this->table->prepare_items();

		ob_start();
		$this->table->extra_tablenav( 'top' );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'id="filter-by-comment-type"', $output );
		$this->assertStringContainsString( "<option value='comment'>", $output );
	}

	#[\PHPUnit\Framework\Attributes\Ticket( '38341' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Comments_List_Table', 'extra_tablenav' )]
	public function test_empty_trash_button_should_not_be_shown_if_there_are_no_comments() {
		ob_start();
		$this->table->extra_tablenav( 'top' );
		$output = ob_get_clean();

		$this->assertStringNotContainsString( 'id="delete_all"', $output );
	}

	#[\PHPUnit\Framework\Attributes\Ticket( '19278' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_List_Table', 'bulk_actions' )]
	public function test_bulk_action_menu_supports_options_and_optgroups() {
		add_filter(
			'bulk_actions-edit-comments',
			static function () {
				return array(
					'delete'       => 'Delete',
					'Change State' => array(
						'feature' => 'Featured',
						'sale'    => 'On Sale',
					),
				);
			}
		);

		ob_start();
		$this->table->bulk_actions();
		$output = ob_get_clean();

		$expected = <<<'OPTIONS'
<option value="delete">Delete</option>
	<optgroup label="Change State">
		<option value="feature">Featured</option>
		<option value="sale">On Sale</option>
	</optgroup>
OPTIONS;
		$expected = str_replace( "\r\n", "\n", $expected );

		$this->assertStringContainsString( $expected, $output );
	}

	#[\PHPUnit\Framework\Attributes\Ticket( '45089' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_List_Table', 'print_column_headers' )]
	public function test_sortable_columns() {
		$override_sortable_columns = array(
			'author'   => array( 'comment_author', true ),
			'response' => 'comment_post_ID',
			'date'     => array( 'comment_date', 'dEsC' ), // The ordering support should be case-insensitive.
		);

		// Stub the get_sortable_columns() method.
		$object = $this->getStubBuilder( 'WP_Comments_List_Table' )
			->setConstructorArgs( array( array( 'screen' => 'edit-comments' ) ) )
			->onlyMethods( array( 'get_sortable_columns' ) )
			->getStub();

		// Change the null return value of the stubbed get_sortable_columns() method.
		$object->method( 'get_sortable_columns' )
			->willReturn( $override_sortable_columns );

		$output = get_echo( array( $object, 'print_column_headers' ) );

		$this->assertStringContainsString( '?orderby=comment_author&#038;order=desc', $output, 'Mismatch of the default link ordering for comment author column. Should be desc.' );
		$this->assertStringContainsString( 'column-author sortable asc', $output, 'Mismatch of CSS classes for the comment author column.' );

		$this->assertStringContainsString( '?orderby=comment_post_ID&#038;order=asc', $output, 'Mismatch of the default link ordering for comment response column. Should be asc.' );
		$this->assertStringContainsString( 'column-response sortable desc', $output, 'Mismatch of CSS classes for the comment post ID column.' );

		$this->assertStringContainsString( '?orderby=comment_date&#038;order=desc', $output, 'Mismatch of the default link ordering for comment date column. Should be asc.' );
		$this->assertStringContainsString( 'column-date sortable asc', $output, 'Mismatch of CSS classes for the comment date column.' );
	}

	#[\PHPUnit\Framework\Attributes\Ticket( '45089' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_List_Table', 'print_column_headers' )]
	public function test_sortable_columns_with_current_ordering() {
		$override_sortable_columns = array(
			'author'   => array( 'comment_author', false ),
			'response' => 'comment_post_ID',
			'date'     => array( 'comment_date', 'asc' ), // We will override this with current ordering.
		);

		$orderby_existed  = array_key_exists( 'orderby', $_GET );
		$original_orderby = $_GET['orderby'] ?? null;
		$order_existed    = array_key_exists( 'order', $_GET );
		$original_order   = $_GET['order'] ?? null;

		// Current ordering.
		$_GET['orderby'] = 'comment_date';
		$_GET['order']   = 'desc';

		// Stub the get_sortable_columns() method.
		$object = $this->getStubBuilder( 'WP_Comments_List_Table' )
			->setConstructorArgs( array( array( 'screen' => 'edit-comments' ) ) )
			->onlyMethods( array( 'get_sortable_columns' ) )
			->getStub();

		// Change the null return value of the stubbed get_sortable_columns() method.
		$object->method( 'get_sortable_columns' )
			->willReturn( $override_sortable_columns );

		$output = get_echo( array( $object, 'print_column_headers' ) );

		if ( $orderby_existed ) {
			$_GET['orderby'] = $original_orderby;
		} else {
			unset( $_GET['orderby'] );
		}
		if ( $order_existed ) {
			$_GET['order'] = $original_order;
		} else {
			unset( $_GET['order'] );
		}

		$this->assertStringContainsString( '?orderby=comment_author&#038;order=asc', $output, 'Mismatch of the default link ordering for comment author column. Should be asc.' );
		$this->assertStringContainsString( 'column-author sortable desc', $output, 'Mismatch of CSS classes for the comment author column.' );

		$this->assertStringContainsString( '?orderby=comment_post_ID&#038;order=asc', $output, 'Mismatch of the default link ordering for comment response column. Should be asc.' );
		$this->assertStringContainsString( 'column-response sortable desc', $output, 'Mismatch of CSS classes for the comment post ID column.' );

		$this->assertStringContainsString( '?orderby=comment_date&#038;order=asc', $output, 'Mismatch of the current link ordering for comment date column. Should be asc.' );
		$this->assertStringContainsString( 'column-date sorted desc', $output, 'Mismatch of CSS classes for the comment date column.' );
	}

	#[\PHPUnit\Framework\Attributes\Ticket( '42066' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_Comments_List_Table', 'get_views' )]
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

	/**
	 * Verify that the comments table never shows the note comment_type.
	 *
	 *
	 *
	 * @param string $comment_type The comment_type parameter value to test.
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '64198' )]
	#[\PHPUnit\Framework\Attributes\Ticket( '64474' )]
	#[\PHPUnit\Framework\Attributes\DataProvider( 'data_comment_type' )]
	public function test_comments_list_table_does_not_show_note_comment_type( string $comment_type ) {
		$post_id = self::factory()->post->create();
		self::factory()->comment->create(
			array(
				'comment_post_ID'  => $post_id,
				'comment_content'  => 'This is a note.',
				'comment_type'     => 'note',
				'comment_approved' => '1',
				'comment_date'     => '2024-01-01 10:00:00',
				'comment_date_gmt' => '2024-01-01 10:00:00',
			)
		);
		$regular_comment_id       = self::factory()->comment->create(
			array(
				'comment_post_ID'  => $post_id,
				'comment_content'  => 'This is a regular comment.',
				'comment_type'     => '',
				'comment_approved' => '1',
				'comment_date'     => '2024-01-01 11:00:00',
				'comment_date_gmt' => '2024-01-01 11:00:00',
			)
		);
		$pingback_comment_id      = self::factory()->comment->create(
			array(
				'comment_post_ID'  => $post_id,
				'comment_content'  => 'This is a pingback comment.',
				'comment_type'     => '',
				'comment_approved' => '1',
				'comment_date'     => '2024-01-01 12:00:00',
				'comment_date_gmt' => '2024-01-01 12:00:00',
			)
		);
		$comment_type_existed     = array_key_exists( 'comment_type', $_REQUEST );
		$original_comment_type    = $_REQUEST['comment_type'] ?? null;
		$_REQUEST['comment_type'] = $comment_type;
		$this->table->prepare_items();
		$items = $this->table->items;
		if ( $comment_type_existed ) {
			$_REQUEST['comment_type'] = $original_comment_type;
		} else {
			unset( $_REQUEST['comment_type'] );
		}
		$this->assertCount( 2, $items );
		$this->assertEquals( $pingback_comment_id, $items[0]->comment_ID );
		$this->assertEquals( $regular_comment_id, $items[1]->comment_ID );
	}

	/**
	 * Data provider for test_comments_list_table_does_not_show_note_comment_type().
	 *
	 * @return array<string, string[]>
	 */
	public static function data_comment_type(): array {
		return array(
			'note type explicitly requested' => array( 'note' ),
			'all type requested'             => array( 'all' ),
		);
	}
}
