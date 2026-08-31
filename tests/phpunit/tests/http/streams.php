<?php

require_once __DIR__ . '/base.php';

/**
 */
#[\PHPUnit\Framework\Attributes\Group( 'http' )]
#[\PHPUnit\Framework\Attributes\Group( 'external-http' )]
class Tests_HTTP_streams extends WP_HTTP_UnitTestCase {
	public $transport = 'streams';
}
