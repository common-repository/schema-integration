<?php
/**
 * Custom data for schema
 *
 * @package   Schema_Integration\Core\Schema
 * @author    Custom Backend
 * @copyright Copyright © 2019
 * @license   GPL-2.0+
 * @wordpress-plugin
 */

namespace Schema_Integration\Core\Schema\Data;

use Schema_Integration\Core\Schema\Schema_Variable;
use Schema_Itegration\Core\Schema\Data\Data;

/**
 * Class Post_Data
 */
class Custom_Data extends Data {

	/**
	 * WP_Post or WP_Term
	 *
	 * @var object
	 */
	private $object;

	/**
	 * Custom_Data constructor.
	 *
	 * @param array  $schema_template Schema template.
	 * @param array  $options         Plugin settings.
	 * @param object $object          Current post or term.
	 */
	public function __construct( array $schema_template, array $options, $object ) {
		$this->object = $object;
		parent::__construct( $schema_template, $options );
	}

	/**
	 * Get image url from acf field.
	 *
	 * @param object $object     current post or term object.
	 * @param string $field_name acf field name.
	 *
	 * @return string
	 */
	public function get_acf_image_url( $object, $field_name ) {
		if ( function_exists( 'get_field' ) ) {
			$img_id = get_field( $field_name, $object, false );
		}

		return ! empty( $img_id ) ? wp_get_attachment_image_url( $img_id, 'full' ) : '';

	}

	/**
	 * Register variables.
	 *
	 * @return array
	 */
	protected function register_variable() {
		return apply_filters(
			'custom_schema_custom_data',
			[
				new Schema_Variable( '{custom_text}', 'Произвольный текст', 'trim' ),
				new Schema_Variable(
					'{custom_post_field}',
					'Произвольное поле записи',
					'get_post_meta',
					[
						$this->object->ID,
						'custom' => '',
						true,
					]
				),
				new Schema_Variable(
					'{custom_term_field}',
					'Произвольное поле термина',
					'get_term_meta',
					[
						$this->object->term_id,
						'custom' => '',
						true,
					]
				),
				new Schema_Variable(
					'{custom_field_acf_img}',
					'Картинка произвольного поля ACF',
					[
						$this,
						'get_acf_image_url',
					],
					[
						$this->object,
						'custom' => '',
					]
				),
			]
		);
	}

}
