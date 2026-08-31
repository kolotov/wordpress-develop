<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/phpunit13-coverage.php';

$support_files = array(
	'../tests/admin/plugin-dependencies/base.php',
	'../tests/block-templates/base.php',
	'../tests/filesystem/base.php',
	'../tests/filesystem/wpFilesystemDirect/base.php',
	'../tests/fonts/font-face/base.php',
	'../tests/fonts/font-face/wp-font-face-tests-dataset.php',
	'../tests/fonts/font-library/wpFontLibrary/base.php',
	'../tests/http/base.php',
	'../tests/image/base.php',
	'../tests/image/resize.php',
	'../tests/media/testcase-adjacent-image-link.php',
	'../tests/rest-api/rest-test-controller.php',
	'../tests/theme/base.php',
);

foreach ( $support_files as $support_file ) {
	require_once __DIR__ . '/' . $support_file;
}
