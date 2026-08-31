<?php
/**
 *
 */
#[\PHPUnit\Framework\Attributes\Group( 'admin' )]
#[\PHPUnit\Framework\Attributes\Group( 'user' )]
#[\PHPUnit\Framework\Attributes\CoversNothing]
class Tests_Admin_wpUserSearch extends WP_UnitTestCase {

	/**
	 * @expectedDeprecated WP_User_Search
	 */
	public function test_class_is_deprecated() {
		$wp_user_search = new WP_User_Search();
	}
}
