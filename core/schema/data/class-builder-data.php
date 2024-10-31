<?php
/**
 * Schema Builder
 *
 * @package   Schema_Integration\Core\Schema\Data
 * @author    Custom Backend
 * @copyright Copyright © 2019
 * @license   GPL-2.0+
 * @wordpress-plugin
 */

namespace Schema_Integration\Core\Schema\Data;

use WP_Post;
use WP_Term;

/**
 * Class Builder_Data
 */
class Builder_Data {

	/**
	 * Current object for page.
	 *
	 * @var WP_Post|WP_Term
	 */
	private $object;
	/**
	 * Template for schema.
	 *
	 * @var array
	 */
	private $schema_template;
	/**
	 * Plugin settings
	 *
	 * @var array
	 */
	private $options;

	/**
	 * Builder_Data constructor.
	 *
	 * @param object $object          WP_Post/WP_Term object.
	 * @param array  $schema_template Template for schema.
	 * @param array  $options         Plugin settings.
	 */
	public function __construct( $object, array $schema_template, array $options ) {
		$this->schema_template = $schema_template;
		$this->options         = $options;
		$this->object          = $object;
	}

	/**
	 * Get all variables and replace on current data
	 */
	private function replace_variables() {
		if ( $this->object instanceof WP_Post ) {
			$object_data           = new Post_Data( $this->schema_template, $this->options, $this->object );
			$this->schema_template = $object_data->update_template();
		} elseif ( $this->object instanceof WP_Term ) {
			$object_data           = new Term_Data( $this->schema_template, $this->options, $this->object );
			$this->schema_template = $object_data->update_template();
		}
		$site_data             = new Site_Data( $this->schema_template, $this->options );
		$this->schema_template = $site_data->update_template();
		$this->schema_template = apply_filters( 'custom_schema_template', $this->schema_template, $this->object );
		$custom_data           = new Custom_Data( $this->schema_template, $this->options, $this->object );
		$this->schema_template = $custom_data->update_template();
	}

	/**
	 * Delete all not replaced variables.
	 */
	private function delete_other_variables() {
		foreach ( $this->schema_template as $key => $schema_row ) {
			$schema_part[ $key ] = isset( $schema_row['value'] ) ? trim( preg_replace( '/{.*?}/', '', $schema_row['value'] ) ) : '';
		}
	}

	/**
	 * Sort array
	 *
	 * @param array $array     Schema data.
	 * @param int   $parent_id Current parent ID.
	 *
	 * @return array
	 */
	private function sort_array( array $array, $parent_id = 0 ) {
		$output = [];
		foreach ( $array as $key => $value ) {
			$current_parent_id = (int) $value['parent_id'];
			if ( $parent_id === $current_parent_id ) {
				if ( '{object}' === $value['value'] ) {
					$output[ $value['key'] ] = $this->sort_array( $array, (int) $key );
				} else {
					$output[ $value['key'] ] = $value['value'];
				}
			}
		}

		return $output;
	}

	/**
	 * Get current template schema
	 *
	 * @return array
	 */
	public function get_schema() {
		$this->replace_variables();
		$this->schema_template = $this->sort_array( $this->schema_template );
		$this->delete_other_variables();

		return $this->schema_template;
	}

}
