<?php

/**
 * Tests for the wp_cache_set_last_changed() function.
 *
 *
 */
#[\PHPUnit\Framework\Attributes\Group( 'functions' )]
#[\PHPUnit\Framework\Attributes\Group( 'cache' )]
#[\PHPUnit\Framework\Attributes\CoversFunction( 'wp_cache_set_last_changed' )]
class Tests_Functions_wpCacheSetLastChanged extends WP_UnitTestCase {

	/**
	 * Check the cache key last_changed is set for the specified group.
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '59737' )]
	public function test_wp_cache_set_last_changed() {
		$group = 'group_name';

		$this->assertSame( wp_cache_set_last_changed( $group ), wp_cache_get( 'last_changed', $group ) );
	}

	/**
	 * Check the action is called.
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '59737' )]
	public function test_wp_cache_set_last_changed_action_is_called() {
		$a1 = new MockAction();
		add_action( 'wp_cache_set_last_changed', array( $a1, 'action' ) );

		wp_cache_set_last_changed( 'group_name' );

		$this->assertSame( 1, $a1->get_call_count() );
	}
}
