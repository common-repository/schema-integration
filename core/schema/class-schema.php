<?php
/**
 * Schema
 *
 * @package   Schema_Integration\Core\Schema
 * @author    Custom Backend
 * @copyright Copyright © 2019
 * @license   GPL-2.0+
 * @wordpress-plugin
 */

namespace Schema_Integration\Core\Schema;

use Schema_Integration\Core\Main;

/**
 * Class Schema
 */
class Schema {

	/**
	 * WP_Post/WP_Term object.
	 *
	 * @var object
	 */
	private $object;
	/**
	 * List of schemas
	 *
	 * @var array
	 */
	private $schemas;
	/**
	 * Plugins options
	 *
	 * @var void
	 */
	private $options;

	/**
	 * Schema constructor.
	 *
	 * @param object $object  WP_Post/WP_Term object.
	 * @param array  $schemas Templates for schema.
	 */
	public function __construct( $object, $schemas ) {
		$this->object  = $object;
		$this->schemas = $schemas;
		$this->options = Main::options();
	}

	/**
	 * Return data in script json ld format
	 */
	public function script() {
		if ( is_a( $this->object, 'WP_Post' ) || is_a( $this->object, 'WP_Term' ) ) {
			if ( ! empty( $this->schemas ) ) {
				foreach ( $this->schemas as $schema_template ) {
					if ( ! empty( $schema_template ) ) {
						echo wp_kses(
							'<script type="application/ld+json">' . $schema_template . '</script>',
							[
								'script' =>
									[
										'type' => true,
									],
							]
						);
					}
				}
			}
		}
	}

}
