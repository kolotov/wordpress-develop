<?php
/**
 * A set of unit tests for functions in wp-includes/general-template.php
 *
 */

require_once ABSPATH . 'wp-admin/includes/class-wp-site-icon.php';














#[\PHPUnit\Framework\Attributes\Group( 'general' )]
#[\PHPUnit\Framework\Attributes\Group( 'template' )]
#[\PHPUnit\Framework\Attributes\Group( 'site_icon' )]
class Tests_General_Template extends WP_UnitTestCase {

	protected $wp_site_icon;
	public $site_icon_id;
	public $site_icon_url;

	public $custom_logo_id;
	public $custom_logo_url;

	/**
	 * Blog page used by aria tests.
	 *
	 * @var int
	 */
	public static $blog_page_id;

	/**
	 * Home page used by aria tests.
	 *
	 * @var int
	 */
	public static $home_page_id;

	/**
	 * ID of the administrator user.
	 *
	 * @var int
	 */
	public static $administrator_id;

	/**
	 * ID of the author user.
	 *
	 * @var int
	 */
	public static $author_id;

	/**
	 * Set up the shared fixtures.
	 *
	 * @param WP_UnitTest_Factory $factory Factory instance.
	 */
	public static function wpSetUpBeforeClass( WP_UnitTest_Factory $factory ) {
		self::$administrator_id = $factory->user->create( array( 'role' => 'administrator' ) );
		self::$author_id        = $factory->user->create( array( 'role' => 'author' ) );

		/*
		 * Declare theme support for custom logo.
		 *
		 * This ensures that the `site_logo` option gets deleted in
		 * _delete_site_logo_on_remove_theme_mods(), which in turn
		 * prevents the `core/site-logo` block filters from affecting
		 * the custom logo tests.
		 *
		 * Alternatively, these filters can be removed instead:
		 *
		 *     remove_filter( 'theme_mod_custom_logo', '_override_custom_logo_theme_mod' );
		 *     remove_filter( 'pre_set_theme_mod_custom_logo', '_sync_custom_logo_to_site_logo' );
		 */
		add_theme_support( 'custom-logo' );

		self::$blog_page_id = self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'Blog',
				'page_name'  => 'blog',
			)
		);

		self::$home_page_id = self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'Home',
				'page_name'  => 'home',
			)
		);
	}

	public static function wpTearDownAfterClass() {
		remove_theme_support( 'custom-logo' );
	}

	public function set_up() {
		parent::set_up();

		switch_theme( 'default' );
		$this->wp_site_icon = new WP_Site_Icon();
	}

	public function tear_down() {
		global $wp_customize;
		$this->remove_custom_logo();
		$this->remove_site_icon();
		$wp_customize = null;

		parent::tear_down();
	}

	/**
	 */
	#[\PHPUnit\Framework\Attributes\Group( 'site_icon' )]
	#[\PHPUnit\Framework\Attributes\RequiresFunction( 'imagejpeg' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'get_site_icon_url' )]
	public function test_get_site_icon_url() {
		$this->assertEmpty( get_site_icon_url(), 'Site icon URL should not be set initially.' );

		$this->set_site_icon();
		$this->assertSame( $this->site_icon_url, get_site_icon_url(), 'Site icon URL should be set.' );

		$this->remove_site_icon();
		$this->assertEmpty( get_site_icon_url(), 'Site icon URL should not be set after removal.' );
	}

	/**
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '65098' )]
	#[\PHPUnit\Framework\Attributes\Group( 'site_icon' )]
	#[\PHPUnit\Framework\Attributes\RequiresFunction( 'imagejpeg' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'get_site_icon_url' )]
	public function test_get_site_icon_url_returns_fallback_when_attachment_url_fails(): void {
		$this->set_site_icon();

		$fallback = 'https://example.com/fallback-icon.png';
		add_filter( 'wp_get_attachment_image_src', '__return_false' );
		$url = get_site_icon_url( 32, $fallback );

		$this->assertSame( $fallback, $url, 'Fallback URL should be returned when attachment URL lookup fails.' );
	}

	/**
	 * Ensures the site icon URL scheme is upgraded for the current request, but never downgraded.
	 *
	 * The site icon is display chrome that also renders in wp-admin and on the
	 * login screen, where wp_get_attachment_image_url() does not correct the scheme.
	 *
	 * On an HTTPS request with an http:// siteurl the icon must still be served
	 * over HTTPS to avoid a broken, mixed-content image.
	 *
	 *
	 *
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '65696' )]
	#[\PHPUnit\Framework\Attributes\Group( 'site_icon' )]
	#[\PHPUnit\Framework\Attributes\RequiresFunction( 'imagejpeg' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'get_site_icon_url' )]
	public function test_get_site_icon_url_scheme() {
		$this->set_site_icon();

		set_current_screen( 'dashboard' );
		$this->assertTrue( is_admin(), 'Test should run in the admin context.' );
		$this->assertFalse( is_ssl(), 'Baseline request should not be detected as SSL.' );
		$this->assertStringStartsWith( 'http://', get_site_icon_url(), 'Baseline icon URL should use the HTTP scheme.' );

		$_SERVER['HTTPS'] = 'on';
		$this->assertTrue( is_ssl(), 'Request should now be detected as SSL.' );
		$this->assertStringStartsWith( 'https://', get_site_icon_url(), 'Site icon URL should use the HTTPS scheme on an SSL admin request.' );

		add_filter(
			'upload_dir',
			static function ( $uploads ) {
				$uploads['url']     = set_url_scheme( $uploads['url'], 'https' );
				$uploads['baseurl'] = set_url_scheme( $uploads['baseurl'], 'https' );
				return $uploads;
			}
		);

		unset( $_SERVER['HTTPS'] );
		$this->assertFalse( is_ssl(), 'Request should no longer be detected as SSL.' );
		$this->assertStringStartsWith( 'https://', get_site_icon_url(), 'Site icon URL should preserve the HTTPS scheme on a non-SSL request.' );
	}

	/**
	 */
	#[\PHPUnit\Framework\Attributes\Group( 'site_icon' )]
	#[\PHPUnit\Framework\Attributes\RequiresFunction( 'imagejpeg' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'site_icon_url' )]
	public function test_site_icon_url() {
		ob_start();
		site_icon_url();
		$this->assertSame( '', ob_get_clean() );

		$this->set_site_icon();
		ob_start();
		site_icon_url();
		$this->assertSame( $this->site_icon_url, ob_get_clean() );
	}

	/**
	 */
	#[\PHPUnit\Framework\Attributes\Group( 'site_icon' )]
	#[\PHPUnit\Framework\Attributes\RequiresFunction( 'imagejpeg' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'has_site_icon' )]
	public function test_has_site_icon() {
		$this->assertFalse( has_site_icon(), 'Site icon should not be set initially.' );

		$this->set_site_icon();
		$this->assertTrue( has_site_icon(), 'Site icon should be set.' );

		$this->remove_site_icon();
		$this->assertFalse( has_site_icon(), 'Site icon should not be set after removal.' );
	}

	/**
	 */
	#[\PHPUnit\Framework\Attributes\Group( 'site_icon' )]
	#[\PHPUnit\Framework\Attributes\Group( 'multisite' )]
	#[\PHPUnit\Framework\Attributes\Group( 'ms-required' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'has_site_icon' )]
	public function test_has_site_icon_returns_true_when_called_for_other_site_with_site_icon_set() {
		$blog_id = self::factory()->blog->create();
		switch_to_blog( $blog_id );
		$this->set_site_icon();
		restore_current_blog();

		$this->assertTrue( has_site_icon( $blog_id ) );
	}

	/**
	 */
	#[\PHPUnit\Framework\Attributes\Group( 'site_icon' )]
	#[\PHPUnit\Framework\Attributes\Group( 'multisite' )]
	#[\PHPUnit\Framework\Attributes\Group( 'ms-required' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'has_site_icon' )]
	public function test_has_site_icon_returns_false_when_called_for_other_site_without_site_icon_set() {
		$blog_id = self::factory()->blog->create();

		$this->assertFalse( has_site_icon( $blog_id ) );
	}

	/**
	 */
	#[\PHPUnit\Framework\Attributes\Group( 'site_icon' )]
	#[\PHPUnit\Framework\Attributes\RequiresFunction( 'imagejpeg' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'wp_site_icon' )]
	public function test_wp_site_icon() {
		ob_start();
		wp_site_icon();
		$this->assertSame( '', ob_get_clean() );

		$this->set_site_icon();
		$output = array(
			sprintf( '<link rel="icon" href="%s" sizes="32x32" />', esc_url( get_site_icon_url( 32 ) ) ),
			sprintf( '<link rel="icon" href="%s" sizes="192x192" />', esc_url( get_site_icon_url( 192 ) ) ),
			sprintf( '<link rel="apple-touch-icon" href="%s" />', esc_url( get_site_icon_url( 180 ) ) ),
			sprintf( '<meta name="msapplication-TileImage" content="%s" />', esc_url( get_site_icon_url( 270 ) ) ),
			'',
		);
		$output = implode( "\n", $output );

		ob_start();
		wp_site_icon();
		$this->assertSame( $output, ob_get_clean() );
	}

	/**
	 */
	#[\PHPUnit\Framework\Attributes\Group( 'site_icon' )]
	#[\PHPUnit\Framework\Attributes\RequiresFunction( 'imagejpeg' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'wp_site_icon' )]
	public function test_wp_site_icon_with_filter() {
		ob_start();
		wp_site_icon();
		$this->assertSame( '', ob_get_clean() );

		$this->set_site_icon();
		$output = array(
			sprintf( '<link rel="icon" href="%s" sizes="32x32" />', esc_url( get_site_icon_url( 32 ) ) ),
			sprintf( '<link rel="icon" href="%s" sizes="192x192" />', esc_url( get_site_icon_url( 192 ) ) ),
			sprintf( '<link rel="apple-touch-icon" href="%s" />', esc_url( get_site_icon_url( 180 ) ) ),
			sprintf( '<meta name="msapplication-TileImage" content="%s" />', esc_url( get_site_icon_url( 270 ) ) ),
			sprintf( '<link rel="apple-touch-icon" sizes="150x150" href="%s" />', esc_url( get_site_icon_url( 150 ) ) ),
			'',
		);
		$output = implode( "\n", $output );

		add_filter( 'site_icon_meta_tags', array( $this, 'custom_site_icon_meta_tag' ) );
		ob_start();
		wp_site_icon();
		$actual = ob_get_clean();
		remove_filter( 'site_icon_meta_tags', array( $this, 'custom_site_icon_meta_tag' ) );
		$this->assertSame( $output, $actual );
	}

	/**
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '38377' )]
	#[\PHPUnit\Framework\Attributes\Group( 'site_icon' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'wp_site_icon' )]
	public function test_customize_preview_wp_site_icon_empty() {
		global $wp_customize;
		wp_set_current_user( self::$administrator_id );

		require_once ABSPATH . WPINC . '/class-wp-customize-manager.php';
		$wp_customize = new WP_Customize_Manager();
		$wp_customize->register_controls();
		$wp_customize->start_previewing_theme();

		$this->expectOutputString( '<link rel="icon" href="/favicon.ico" sizes="32x32" />' . "\n" );
		wp_site_icon();
	}

	/**
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '38377' )]
	#[\PHPUnit\Framework\Attributes\Group( 'site_icon' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'wp_site_icon' )]
	public function test_customize_preview_wp_site_icon_dirty() {
		global $wp_customize;
		wp_set_current_user( self::$administrator_id );

		require_once ABSPATH . WPINC . '/class-wp-customize-manager.php';
		$wp_customize = new WP_Customize_Manager();
		$wp_customize->register_controls();
		$wp_customize->start_previewing_theme();

		$attachment_id = $this->insert_attachment();
		$wp_customize->set_post_value( 'site_icon', $attachment_id );
		$wp_customize->get_setting( 'site_icon' )->preview();
		$output = array(
			sprintf( '<link rel="icon" href="%s" sizes="32x32" />', esc_url( wp_get_attachment_image_url( $attachment_id, 32 ) ) ),
			sprintf( '<link rel="icon" href="%s" sizes="192x192" />', esc_url( wp_get_attachment_image_url( $attachment_id, 192 ) ) ),
			sprintf( '<link rel="apple-touch-icon" href="%s" />', esc_url( wp_get_attachment_image_url( $attachment_id, 180 ) ) ),
			sprintf( '<meta name="msapplication-TileImage" content="%s" />', esc_url( wp_get_attachment_image_url( $attachment_id, 270 ) ) ),
			'',
		);
		$output = implode( "\n", $output );
		$this->expectOutputString( $output );
		wp_site_icon();
	}

	/**
	 * Builds and retrieves a custom site icon meta tag.
	 *
	 * @since 4.3.0
	 *
	 * @param $meta_tags
	 * @return array
	 */
	public function custom_site_icon_meta_tag( $meta_tags ) {
		$meta_tags[] = sprintf( '<link rel="apple-touch-icon" sizes="150x150" href="%s" />', esc_url( get_site_icon_url( 150 ) ) );

		return $meta_tags;
	}

	/**
	 * Sets a site icon in options for testing.
	 *
	 * @since 4.3.0
	 */
	private function set_site_icon() {
		if ( ! $this->site_icon_id ) {
			add_filter( 'intermediate_image_sizes_advanced', array( $this->wp_site_icon, 'additional_sizes' ) );
			$this->insert_attachment();
			remove_filter( 'intermediate_image_sizes_advanced', array( $this->wp_site_icon, 'additional_sizes' ) );
		}

		update_option( 'site_icon', $this->site_icon_id );
	}

	/**
	 * Removes the site icon from options.
	 *
	 * @since 4.3.0
	 */
	private function remove_site_icon() {
		delete_option( 'site_icon' );
	}

	/**
	 * Inserts an attachment for testing site icons.
	 *
	 * @since 4.3.0
	 */
	private function insert_attachment() {
		$filename = DIR_TESTDATA . '/images/test-image.jpg';
		$contents = file_get_contents( $filename );

		$upload              = wp_upload_bits( wp_basename( $filename ), null, $contents );
		$this->site_icon_url = $upload['url'];

		// Save the data.
		$this->site_icon_id = $this->_make_attachment( $upload );
		return $this->site_icon_id;
	}

	/**
	 *
	 * @since 4.5.0
	 */
	#[\PHPUnit\Framework\Attributes\Group( 'custom_logo' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'has_custom_logo' )]
	public function test_has_custom_logo() {
		$this->assertFalse( has_custom_logo(), 'Custom logo should not be set initially.' );

		$this->set_custom_logo();
		$this->assertTrue( has_custom_logo(), 'Custom logo should be set.' );

		$this->remove_custom_logo();
		$this->assertFalse( has_custom_logo(), 'Custom logo should not be set after removal.' );
	}

	/**
	 */
	#[\PHPUnit\Framework\Attributes\Group( 'custom_logo' )]
	#[\PHPUnit\Framework\Attributes\Group( 'multisite' )]
	#[\PHPUnit\Framework\Attributes\Group( 'ms-required' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'has_custom_logo' )]
	public function test_has_custom_logo_returns_true_when_called_for_other_site_with_custom_logo_set() {
		$blog_id = self::factory()->blog->create();
		switch_to_blog( $blog_id );
		$this->set_custom_logo();
		restore_current_blog();

		$this->assertTrue( has_custom_logo( $blog_id ) );
	}

	/**
	 */
	#[\PHPUnit\Framework\Attributes\Group( 'custom_logo' )]
	#[\PHPUnit\Framework\Attributes\Group( 'multisite' )]
	#[\PHPUnit\Framework\Attributes\Group( 'ms-required' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'has_custom_logo' )]
	public function test_has_custom_logo_returns_false_when_called_for_other_site_without_custom_logo_set() {
		$blog_id = self::factory()->blog->create();

		$this->assertFalse( has_custom_logo( $blog_id ) );
	}

	/**
	 *
	 * @since 4.5.0
	 */
	#[\PHPUnit\Framework\Attributes\Group( 'custom_logo' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'get_custom_logo' )]
	public function test_get_custom_logo() {
		$this->assertEmpty( get_custom_logo(), 'Custom logo should not be set initially.' );

		$this->set_custom_logo();
		$custom_logo = get_custom_logo();
		$this->assertNotEmpty( $custom_logo, 'Custom logo markup should not be empty.' );
		$this->assertIsString( $custom_logo, 'Custom logo markup should be a string.' );

		$this->remove_custom_logo();
		$this->assertEmpty( get_custom_logo(), 'Custom logo should not be set after removal.' );
	}

	/**
	 */
	#[\PHPUnit\Framework\Attributes\Group( 'custom_logo' )]
	#[\PHPUnit\Framework\Attributes\Group( 'multisite' )]
	#[\PHPUnit\Framework\Attributes\Group( 'ms-required' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'get_custom_logo' )]
	public function test_get_custom_logo_returns_logo_when_called_for_other_site_with_custom_logo_set() {
		$blog_id = self::factory()->blog->create();
		switch_to_blog( $blog_id );

		$this->set_custom_logo();

		$custom_logo_attr = array(
			'class'   => 'custom-logo',
			'loading' => false,
		);

		// If the logo alt attribute is empty, use the site title.
		$image_alt = get_post_meta( $this->custom_logo_id, '_wp_attachment_image_alt', true );
		if ( empty( $image_alt ) ) {
			$custom_logo_attr['alt'] = get_bloginfo( 'name', 'display' );
		}

		$home_url = get_home_url( $blog_id, '/' );
		$image    = wp_get_attachment_image( $this->custom_logo_id, 'full', false, $custom_logo_attr );
		restore_current_blog();

		$expected_custom_logo = '<a href="' . $home_url . '" class="custom-logo-link" rel="home">' . $image . '</a>';
		$this->assertSame( $expected_custom_logo, get_custom_logo( $blog_id ) );
	}

	/**
	 *
	 * @since 4.5.0
	 */
	#[\PHPUnit\Framework\Attributes\Group( 'custom_logo' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'the_custom_logo' )]
	public function test_the_custom_logo() {
		ob_start();
		the_custom_logo();
		$this->assertSame( '', ob_get_clean() );

		$this->set_custom_logo();

		$custom_logo_attr = array(
			'class'   => 'custom-logo',
			'loading' => false,
		);

		// If the logo alt attribute is empty, use the site title.
		$image_alt = get_post_meta( $this->custom_logo_id, '_wp_attachment_image_alt', true );
		if ( empty( $image_alt ) ) {
			$custom_logo_attr['alt'] = get_bloginfo( 'name', 'display' );
		}

		$image = wp_get_attachment_image( $this->custom_logo_id, 'full', false, $custom_logo_attr );

		ob_start();
		the_custom_logo();
		$this->assertSame( '<a href="http://' . WP_TESTS_DOMAIN . '/" class="custom-logo-link" rel="home">' . $image . '</a>', ob_get_clean() );
	}

	/**
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '38768' )]
	#[\PHPUnit\Framework\Attributes\Group( 'custom_logo' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'the_custom_logo' )]
	public function test_the_custom_logo_with_alt() {
		$this->set_custom_logo();

		$image_alt = 'My alt attribute';

		update_post_meta( $this->custom_logo_id, '_wp_attachment_image_alt', $image_alt );

		$image = wp_get_attachment_image(
			$this->custom_logo_id,
			'full',
			false,
			array(
				'class'   => 'custom-logo',
				'loading' => false,
			)
		);

		$this->expectOutputString( '<a href="http://' . WP_TESTS_DOMAIN . '/" class="custom-logo-link" rel="home">' . $image . '</a>' );
		the_custom_logo();
	}

	/**
	 * Sets a custom logo in options for testing.
	 *
	 * @since 4.5.0
	 */
	private function set_custom_logo() {
		if ( ! $this->custom_logo_id ) {
			$this->insert_custom_logo();
		}

		set_theme_mod( 'custom_logo', $this->custom_logo_id );
	}

	/**
	 * Removes the custom logo from options.
	 *
	 * @since 4.5.0
	 */
	private function remove_custom_logo() {
		remove_theme_mod( 'custom_logo' );
	}

	/**
	 * Inserts an attachment for testing custom logos.
	 *
	 * @since 4.5.0
	 */
	private function insert_custom_logo() {
		$filename = DIR_TESTDATA . '/images/test-image.jpg';
		$contents = file_get_contents( $filename );
		$upload   = wp_upload_bits( wp_basename( $filename ), null, $contents );

		// Save the data.
		$this->custom_logo_url = $upload['url'];
		$this->custom_logo_id  = $this->_make_attachment( $upload );
		return $this->custom_logo_id;
	}

	/**
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '38253' )]
	#[\PHPUnit\Framework\Attributes\Group( 'ms-required' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'get_site_icon_url' )]
	public function test_get_site_icon_url_preserves_switched_state() {
		$blog_id = self::factory()->blog->create();
		switch_to_blog( $blog_id );

		$expected = $GLOBALS['_wp_switched_stack'];

		get_site_icon_url( 512, '', $blog_id );

		$result = $GLOBALS['_wp_switched_stack'];

		restore_current_blog();

		$this->assertSame( $expected, $result );
	}

	/**
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '38253' )]
	#[\PHPUnit\Framework\Attributes\Group( 'ms-required' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'has_custom_logo' )]
	public function test_has_custom_logo_preserves_switched_state() {
		$blog_id = self::factory()->blog->create();
		switch_to_blog( $blog_id );

		$expected = $GLOBALS['_wp_switched_stack'];

		has_custom_logo( $blog_id );

		$result = $GLOBALS['_wp_switched_stack'];

		restore_current_blog();

		$this->assertSame( $expected, $result );
	}

	/**
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '38253' )]
	#[\PHPUnit\Framework\Attributes\Group( 'ms-required' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'get_custom_logo' )]
	public function test_get_custom_logo_preserves_switched_state() {
		$blog_id = self::factory()->blog->create();
		switch_to_blog( $blog_id );

		$expected = $GLOBALS['_wp_switched_stack'];

		get_custom_logo( $blog_id );

		$result = $GLOBALS['_wp_switched_stack'];

		restore_current_blog();

		$this->assertSame( $expected, $result );
	}

	/**
	 * Test the aria attribute for the custom logo on the front page set to the blog.
	 *
	 *
	 *
	 *
	 * @param string $url                The URL to visit.
	 * @param bool   $attribute_expected Whether the aria-current attribute is expected.
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '62879' )]
	#[\PHPUnit\Framework\Attributes\DataProvider( 'data_get_custom_logo_aria_current_attribute_blog_front_page' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'get_custom_logo' )]
	public function test_get_custom_logo_aria_current_attribute_blog_front_page( $url, $attribute_expected ) {
		// Set the custom logo.
		$this->set_custom_logo();
		$this->go_to( $url );

		$this->assertNotEmpty( get_custom_logo(), 'Custom logo is expected to be set' );

		if ( $attribute_expected ) {
			$this->assertStringContainsString( 'aria-current="page"', get_custom_logo(), 'Custom logo is expected to contain aria-current attribute' );
		} else {
			$this->assertStringNotContainsString( 'aria-current="page"', get_custom_logo(), 'Custom logo is expected to contain aria-current attribute' );
		}
	}

	/**
	 * Data provider for the test_get_custom_logo_aria_current_attribute_blog_front_page.
	 *
	 * @return array[]
	 */
	public static function data_get_custom_logo_aria_current_attribute_blog_front_page() {
		return array(
			'Front page'  => array( home_url(), true ),
			'Blog post'   => array( home_url( '/?p=1' ), false ),
			'Sample page' => array( home_url( '/?page_id=2' ), false ),
		);
	}

	/**
	 * Test the aria attribute for the custom logo on the front page set to the blog.
	 *
	 *
	 *
	 * @param string $url                The URL to visit.
	 * @param bool   $attribute_expected Whether the aria-current attribute is expected.
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '62879' )]
	#[\PHPUnit\Framework\Attributes\DataProvider( 'data_get_custom_logo_aria_current_attribute_blog_set_to_page_without_front_page_defined' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'get_custom_logo' )]
	public function test_get_custom_logo_aria_current_attribute_blog_set_to_page_without_front_page_defined( $url, $attribute_expected ) {
		// Set up pretty permalinks.
		update_option( 'permalink_structure', '/%postname%/' );

		// Set posts to show on a static page.
		update_option( 'show_on_front', 'page' );
		update_option( 'page_for_posts', self::$blog_page_id );

		// Set the custom logo.
		$this->set_custom_logo();
		$this->go_to( $url );

		$this->assertNotEmpty( get_custom_logo(), 'Custom logo is expected to be set' );

		if ( $attribute_expected ) {
			$this->assertStringContainsString( 'aria-current="page"', get_custom_logo(), 'Custom logo is expected to contain aria-current attribute' );
		} else {
			$this->assertStringNotContainsString( 'aria-current="page"', get_custom_logo(), 'Custom logo is expected to contain aria-current attribute' );
		}
	}

	/**
	 * Data provider for the test_get_custom_logo_aria_current_attribute_blog_set_to_page_without_front_page_defined.
	 *
	 * @return array[]
	 */
	public static function data_get_custom_logo_aria_current_attribute_blog_set_to_page_without_front_page_defined() {
		return array(
			'Front page'  => array( home_url(), true ),
			'Blog index'  => array( home_url( '/blog/' ), true ),
			'Blog post'   => array( home_url( '/?p=1' ), false ),
			'Sample page' => array( home_url( '/?page_id=2' ), false ),
		);
	}

	/**
	 * Test the aria attribute for the custom logo on the front page set to the blog.
	 *
	 *
	 *
	 *
	 * @param string $url                The URL to visit.
	 * @param bool   $attribute_expected Whether the aria-current attribute is expected.
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '62879' )]
	#[\PHPUnit\Framework\Attributes\DataProvider( 'data_get_custom_logo_aria_current_attribute_blog_set_to_page_with_front_page_defined' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'get_custom_logo' )]
	public function test_get_custom_logo_aria_current_attribute_blog_set_to_page_with_front_page_defined( $url, $attribute_expected ) {
		// Set up pretty permalinks.
		update_option( 'permalink_structure', '/%postname%/' );

		// Set posts to show on a static page, show static page on front.
		update_option( 'show_on_front', 'page' );
		update_option( 'page_for_posts', self::$blog_page_id );
		update_option( 'page_on_front', self::$home_page_id );

		// Set the custom logo.
		$this->set_custom_logo();
		$this->go_to( $url );

		$this->assertNotEmpty( get_custom_logo(), 'Custom logo is expected to be set' );

		if ( $attribute_expected ) {
			$this->assertStringContainsString( 'aria-current="page"', get_custom_logo(), 'Custom logo is expected to contain aria-current attribute' );
		} else {
			$this->assertStringNotContainsString( 'aria-current="page"', get_custom_logo(), 'Custom logo is expected to contain aria-current attribute' );
		}
	}

	/**
	 * Data provider for the test_get_custom_logo_aria_current_attribute_blog_set_to_page_with_front_page_defined.
	 *
	 * @return array[]
	 */
	public static function data_get_custom_logo_aria_current_attribute_blog_set_to_page_with_front_page_defined() {
		return array(
			'Front page'  => array( home_url(), true ),
			'Blog index'  => array( home_url( '/blog/' ), true ),
			'Blog post'   => array( home_url( '/?p=1' ), false ),
			'Sample page' => array( home_url( '/?page_id=2' ), false ),
		);
	}

	/**
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '40969' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'get_header' )]
	public function test_get_header_returns_nothing_on_success() {
		$this->expectOutputRegex( '/Header/' );

		// The `get_header()` function must not return anything
		// due to themes in the wild that may echo its return value.
		$this->assertNull( get_header() );
	}

	/**
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '40969' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'get_footer' )]
	public function test_get_footer_returns_nothing_on_success() {
		$this->expectOutputRegex( '/Footer/' );

		// The `get_footer()` function must not return anything
		// due to themes in the wild that may echo its return value.
		$this->assertNull( get_footer() );
	}

	/**
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '40969' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'get_sidebar' )]
	public function test_get_sidebar_returns_nothing_on_success() {
		$this->expectOutputRegex( '/Sidebar/' );

		// The `get_sidebar()` function must not return anything
		// due to themes in the wild that may echo its return value.
		$this->assertNull( get_sidebar() );
	}

	/**
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '40969' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'get_template_part' )]
	public function test_get_template_part_returns_nothing_on_success() {
		$this->expectOutputRegex( '/Template Part/' );

		// The `get_template_part()` function must not return anything
		// due to themes in the wild that echo its return value.
		$this->assertNull( get_template_part( 'template', 'part' ) );
	}

	/**
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '40969' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'get_template_part' )]
	public function test_get_template_part_returns_false_on_failure() {
		$this->assertFalse( get_template_part( 'non-existing-template' ) );
	}

	/**
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '21676' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'get_template_part' )]
	public function test_get_template_part_passes_arguments_to_template() {
		$this->expectOutputRegex( '/{"foo":"baz"}/' );

		get_template_part( 'template', 'part', array( 'foo' => 'baz' ) );
	}

	/**
	 *
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '44183' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'get_the_archive_title' )]
	public function test_get_the_archive_title_is_correct_for_author_queries() {
		$user_with_posts    = get_user_by( 'id', self::$administrator_id );
		$user_with_no_posts = get_user_by( 'id', self::$author_id );

		self::factory()->post->create(
			array(
				'post_author' => $user_with_posts->ID,
			)
		);

		// Simplify the assertion by removing the default archive title prefix:
		add_filter( 'get_the_archive_title_prefix', '__return_empty_string' );

		$this->go_to( get_author_posts_url( $user_with_posts->ID ) );
		$title_when_posts = get_the_archive_title();

		$this->go_to( get_author_posts_url( $user_with_no_posts->ID ) );
		$title_when_no_posts = get_the_archive_title();

		// Ensure the title is correct both when the user has posts and when they dont:
		$this->assertSame( $user_with_posts->display_name, $title_when_posts );
		$this->assertSame( $user_with_no_posts->display_name, $title_when_no_posts );
	}

	/**
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '65098' )]
	#[\PHPUnit\Framework\Attributes\Group( 'site_icon' )]
	#[\PHPUnit\Framework\Attributes\RequiresFunction( 'imagejpeg' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'the_embed_site_title' )]
	public function test_the_embed_site_title_contains_site_icon_when_set(): void {
		$this->set_site_icon();

		$url_32 = get_site_icon_url( 32 );
		$url_64 = get_site_icon_url( 64 );

		$output    = get_echo( 'the_embed_site_title' );
		$processor = new WP_HTML_Tag_Processor( $output );

		$this->assertTrue( $processor->next_tag( 'IMG' ), 'Expected IMG tag.' );
		$this->assertTrue( $processor->has_class( 'wp-embed-site-icon' ), 'Expected IMG to have wp-embed-site-icon class.' );
		$this->assertSame( $url_32, $processor->get_attribute( 'src' ), 'Output should contain 32px site icon URL in src.' );
		$srcset = $processor->get_attribute( 'srcset' );
		$this->assertIsString( $srcset, 'Expected srcset to be present.' );
		$this->assertStringContainsString( $url_64, $srcset, 'Output should contain 64px site icon URL in srcset.' );
	}

	/**
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '65098' )]
	#[\PHPUnit\Framework\Attributes\Group( 'site_icon' )]
	#[\PHPUnit\Framework\Attributes\RequiresFunction( 'imagejpeg' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'the_embed_site_title' )]
	public function test_the_embed_site_title_uses_fallback_when_attachment_url_fails(): void {
		$this->set_site_icon();

		// Simulate wp_get_attachment_image_url() failing.
		add_filter( 'wp_get_attachment_image_src', '__return_false' );
		$output = get_echo( 'the_embed_site_title' );

		$fallback  = includes_url( 'images/w-logo-gray-white-bg.svg' );
		$processor = new WP_HTML_Tag_Processor( $output );

		$this->assertTrue( $processor->next_tag( 'IMG' ), 'Expected IMG tag with fallback.' );
		$this->assertTrue( $processor->has_class( 'wp-embed-site-icon' ), 'Expected IMG to have wp-embed-site-icon class.' );
		$this->assertSame( $fallback, $processor->get_attribute( 'src' ), 'Output should contain fallback URL in src when attachment URL fails.' );
	}

	/**
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '65098' )]
	#[\PHPUnit\Framework\Attributes\Group( 'site_icon' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'the_embed_site_title' )]
	public function test_the_embed_site_title_omits_img_when_url_is_empty(): void {
		// Force get_site_icon_url() to return empty string via filter.
		add_filter( 'get_site_icon_url', '__return_empty_string' );
		$output = get_echo( 'the_embed_site_title' );

		$processor = new WP_HTML_Tag_Processor( $output );

		$this->assertFalse( $processor->next_tag( 'IMG' ), 'IMG tag should be omitted when URL is empty.' );
		$this->assertStringContainsString( get_bloginfo( 'name' ), $output, 'Site name should still be present.' );
	}

	/**
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '65098' )]
	#[\PHPUnit\Framework\Attributes\Group( 'site_icon' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'the_embed_site_title' )]
	public function test_the_embed_site_title_omits_srcset_when_1x_and_2x_urls_are_identical(): void {
		// Force both sizes to return the same URL.
		$svg_url = 'https://example.com/icon.svg';
		$filter  = static function () use ( $svg_url ) {
			return $svg_url;
		};

		add_filter( 'get_site_icon_url', $filter );
		$output = get_echo( 'the_embed_site_title' );

		$processor = new WP_HTML_Tag_Processor( $output );

		$this->assertTrue( $processor->next_tag( 'IMG' ), 'Expected IMG tag.' );
		$this->assertTrue( $processor->has_class( 'wp-embed-site-icon' ), 'Expected IMG to have wp-embed-site-icon class.' );
		$this->assertSame( $svg_url, $processor->get_attribute( 'src' ), '1x URL should be present in src.' );
		$this->assertNull( $processor->get_attribute( 'srcset' ), 'srcset should be omitted when 1x and 2x URLs are identical.' );
	}

	/**
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '65098' )]
	#[\PHPUnit\Framework\Attributes\Group( 'site_icon' )]
	#[WP_PHPUnit_Covers( WP_PHPUnit_Covers::TARGET_FUNCTION, 'the_embed_site_title' )]
	public function test_the_embed_site_title_uses_fallback_without_srcset_when_no_site_icon_set(): void {
		$output   = get_echo( 'the_embed_site_title' );
		$fallback = includes_url( 'images/w-logo-gray-white-bg.svg' );

		$processor = new WP_HTML_Tag_Processor( $output );

		$this->assertTrue( $processor->next_tag( 'IMG' ), 'Expected IMG tag with fallback.' );
		$this->assertTrue( $processor->has_class( 'wp-embed-site-icon' ), 'Expected IMG to have wp-embed-site-icon class.' );
		$this->assertSame( $fallback, $processor->get_attribute( 'src' ), 'Output should contain fallback icon URL in src.' );
		$this->assertNull( $processor->get_attribute( 'srcset' ), 'srcset should be omitted when 1x and 2x fallback URLs are identical.' );
	}
}
