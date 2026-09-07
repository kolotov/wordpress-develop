<?php
#[\PHPUnit\Framework\Attributes\Group( 'admin' )]
#[\PHPUnit\Framework\Attributes\Group( 'user' )]
class Tests_Admin_wpUserSearch extends WP_UnitTestCase {

	/**
	 * @expectedDeprecated WP_User_Search
	 */
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_METHOD, 'WP_User_Search', '__construct' )]
	public function test_class_is_deprecated() {
		$wp_user_search = new WP_User_Search();
	}
}
