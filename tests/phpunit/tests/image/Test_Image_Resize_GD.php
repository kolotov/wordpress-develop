<?php

require_once __DIR__ . '/resize.php';

#[\PHPUnit\Framework\Attributes\Group( 'image' )]
#[\PHPUnit\Framework\Attributes\Group( 'media' )]
#[\PHPUnit\Framework\Attributes\Group( 'upload' )]
#[\PHPUnit\Framework\Attributes\Group( 'resize' )]
#[\PHPUnit\Framework\Attributes\Group( 'wp-image-editor-gd' )]
#[\PHPUnit\Framework\Attributes\RequiresFunction( 'imagejpeg' )]
class Test_Image_Resize_GD extends WP_Tests_Image_Resize_UnitTestCase {

	/**
	 * Use the GD image editor engine
	 *
	 * @var string
	 */
	public $editor_engine = 'WP_Image_Editor_GD';

	public function set_up() {
		require_once ABSPATH . WPINC . '/class-wp-image-editor.php';
		require_once ABSPATH . WPINC . '/class-wp-image-editor-gd.php';

		// This needs to come after the mock image editor class is loaded.
		parent::set_up();
	}

	/**
	 * Verify that GD reports its lack of HEIC decoding support explicitly.
	 *
	 * The successful HEIC-to-JPEG resize contract is exercised by the Imagick
	 * implementation, which supports decoding HEIC images.
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '53645' )]
	public function test_resize_heic() {
		$editor = wp_get_image_editor( DIR_TESTDATA . '/images/test-image.heic' );

		$this->assertWPError( $editor );
		$this->assertSame( 'image_no_editor', $editor->get_error_code() );
		$this->assertFalse( WP_Image_Editor_GD::supports_mime_type( 'image/heic' ) );
	}

	/**
	 * Try resizing a php file (bad image)
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '6821' )]
	public function test_resize_bad_image() {

		$image = $this->resize_helper( DIR_TESTDATA . '/export/crazy-cdata.xml', 25, 25 );
		$this->assertInstanceOf( 'WP_Error', $image );
		$this->assertSame( 'invalid_image', $image->get_error_code() );
	}
}
