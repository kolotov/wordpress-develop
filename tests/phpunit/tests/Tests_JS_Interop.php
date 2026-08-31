<?php
/**
 * Tests verifying behaviors for supporting interoperability with JavaScript.
 *
 * @package WordPress
 */
#[\PHPUnit\Framework\Attributes\Group( 'js-interop' )]
class Tests_JS_Interop extends WP_UnitTestCase {
	/**
	 * Ensures proper recognition of a data attribute and how to transform its
	 * name into what JavaScript code would read from an element's `dataset`.
	 *
	 *
	 *
	 * @param string|null $attribute_name Raw HTML attribute name, if representable.
	 * @param string|null $dataset_name   Transformed attribute name, or `null` if not a custom data attribute.
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '61501' )]
	#[\PHPUnit\Framework\Attributes\DataProvider( 'data_custom_data_attributes_and_transformed_names' )]
	public function test_transforms_custom_attributes_to_proper_dataset_name( string $attribute_name, ?string $dataset_name ) {
		$transformed_name = wp_js_dataset_name( $attribute_name );

		if ( isset( $dataset_name ) ) {
			$this->assertSame(
				$dataset_name,
				$transformed_name,
				'Improperly transformed custom data attribute name.'
			);
		} else {
			$this->assertNull(
				$transformed_name,
				"Should not have identified '{$attribute_name}' as a custom data attribute."
			);
		}
	}

	/**
	 * Ensures proper transformation from JS dataset name to HTML custom attribute name.
	 *
	 *
	 *
	 * @param string|null $attribute_name Raw HTML attribute name, if representable.
	 * @param string|null $dataset_name   Transformed attribute name, or `null` if not a custom data attribute.
	 */
	#[\PHPUnit\Framework\Attributes\Ticket( '61501' )]
	#[\PHPUnit\Framework\Attributes\DataProvider( 'data_dataset_names_and_transformed_attributes' )]
	public function test_transforms_dataset_to_proper_html_attribute_name( ?string $attribute_name, string $dataset_name ) {
		$transformed_name = wp_html_custom_data_attribute_name( $dataset_name );

		if ( isset( $attribute_name ) ) {
			$this->assertSame(
				strtolower( $attribute_name ),
				$transformed_name,
				'Improperly transformed dataset property name.'
			);
		} else {
			$this->assertNull(
				$transformed_name,
				"Should not have identified '{$dataset_name}' as a representable dataset property."
			);
		}
	}

	/**
	 * Data provider.
	 *
	 * @return array[].
	 */
	public static function data_possible_custom_data_attributes_and_transformed_names() {
		return array(
			// Non-custom-data attributes.
			'Normal attribute'             => array( 'post-id', null ),
			'Single word'                  => array( 'id', null ),

			// Invalid HTML attribute names.
			'Contains spaces'              => array( 'no spaces', null ),
			'Contains solidus'             => array( 'one/more/name', null ),

			// Unrepresentable dataset names.
			'Dataset contains spaces'      => array( null, 'one two' ),
			'Dataset contains solidus'     => array( null, 'no/more/names' ),

			// Normative custom data attributes.
			'Normal custom data attribute' => array( 'data-post-id', 'postId' ),
			'Leading dash'                 => array( 'data--before', 'Before' ),
			'Trailing dash'                => array( 'data-after-', 'after-' ),
			'Double-dashes'                => array( 'data-wp-bind--enabled', 'wpBind-Enabled' ),
			'Double-dashes everywhere'     => array( 'data--one--two--', 'One-Two--' ),
			'Triple-dashes'                => array( 'data---one---two---', '-One--Two---' ),

			// Unexpected but recognized custom data attributes.
			'Only comprising a prefix'     => array( 'data-', '' ),
			'With upper case ASCII'        => array( 'data-Post-ID', 'postId' ),
			'With medial upper casing'     => array( 'data-uPPer-cAsE', 'upperCase' ),
			'With Unicode whitespace'      => array( "data-\u{2003}", "\u{2003}" ),
			'With Emoji'                   => array( 'data-🐄-pasture', '🐄Pasture' ),
			'Brackets and colon'           => array( 'data-[wish:granted]', '[wish:granted]' ),

			// Pens and Pencils: a collection of interesting combinations of dash and underscore.
			'data-pens-and-pencils'        => array( 'data-pens-and-pencils', 'pensAndPencils' ),
			'data-pens--and--pencils'      => array( 'data-pens--and--pencils', 'pens-And-Pencils' ),
			'data--pens--and--pencils'     => array( 'data--pens--and--pencils', 'Pens-And-Pencils' ),
			'data---pens---and---pencils'  => array( 'data---pens---and---pencils', '-Pens--And--Pencils' ),
			'data-pens-and-pencils-'       => array( 'data-pens-and-pencils-', 'pensAndPencils-' ),
			'data-pens-and-pencils--'      => array( 'data-pens-and-pencils--', 'pensAndPencils--' ),
			'data-pens_and_pencils__'      => array( 'data-pens_and_pencils__', 'pens_and_pencils__' ),
		);
	}

	public static function data_custom_data_attributes_and_transformed_names() {
		return array_filter(
			self::data_possible_custom_data_attributes_and_transformed_names(),
			static fn ( array $data ): bool => null !== $data[0]
		);
	}

	public static function data_dataset_names_and_transformed_attributes() {
		return array_filter(
			self::data_possible_custom_data_attributes_and_transformed_names(),
			static fn ( array $data ): bool => null !== $data[1]
		);
	}
}
