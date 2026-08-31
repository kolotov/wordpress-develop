<?php

/**
 *
 */
#[\PHPUnit\Framework\Attributes\Group( 'date' )]
#[\PHPUnit\Framework\Attributes\Group( 'datetime' )]
#[\PHPUnit\Framework\Attributes\CoversFunction( 'current_datetime' )]
class Tests_Date_CurrentDatetime extends WP_UnitTestCase {

	/**
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '53484' )]
	public function test_current_datetime_return_type() {
		$this->assertInstanceOf( 'DateTimeImmutable', current_datetime() );
	}
}
