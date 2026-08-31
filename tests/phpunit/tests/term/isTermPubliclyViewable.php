<?php

/**
 *
 */
#[\PHPUnit\Framework\Attributes\Group( 'term' )]
#[\PHPUnit\Framework\Attributes\CoversFunction( 'is_term_publicly_viewable' )]
class Tests_Term_IsTermPubliclyViewable extends WP_UnitTestCase {
	/**
	 * Unit tests for is_term_publicly_viewable().
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '56215' )]
	public function test_non_existent_term_is_not_publicly_viewable() {
		$this->assertFalse( is_term_publicly_viewable( 123 ) );
	}

	/**
	 * Unit tests for is_term_publicly_viewable().
	 *
	 *
	 * @param string $taxonomy The taxonomy name.
	 * @param bool   $expected The expected result of the function call.
	 */
	#[\PHPUnit\Framework\Attributes\DataProvider( 'data_is_term_publicly_viewable' )]
	#[\PHPUnit\Framework\Attributes\Ticket( '56215' )]
	public function test_is_term_publicly_viewable( $taxonomy, $expected ) {
		$term_id = self::factory()->term->create(
			array(
				'taxonomy' => $taxonomy,
			)
		);

		$this->assertSame( $expected, is_term_publicly_viewable( $term_id ) );
	}

	/**
	 * Data provider for test_is_term_publicly_viewable().
	 *
	 * return array[] {
	 *     @type string $taxonomy The taxonomy.
	 *     @type bool   $expected The expected result of the function call.
	 * }
	 */
	public static function data_is_term_publicly_viewable() {
		return array(
			array( 'category', true ),
			array( 'post_tag', true ),
			array( 'post_format', true ),

			array( 'nav_menu', false ),
			array( 'wp_theme', false ),
			array( 'wp_template_part_area', false ),
		);
	}
}
