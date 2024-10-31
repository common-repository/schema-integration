<?php
/**
 * Condition class
 *
 * @package   Schema_Integration\Core\Condition
 * @author    Custom Backend
 * @copyright Copyright © 2019
 * @license   GPL-2.0+
 * @wordpress-plugin
 */

namespace Schema_Integration\Core\Condition;

/**
 * Class Condition
 */
class Condition {

	/**
	 * Condition slug.
	 *
	 * @var string
	 */
	private $slug;
	/**
	 * Condition label
	 *
	 * @var string
	 */
	private $label;

	/**
	 * Condition constructor.
	 *
	 * @param string $slug  Condition slug.
	 * @param string $label Condition label.
	 */
	public function __construct( $slug, $label ) {
		$this->slug  = $slug;
		$this->label = $label;
	}

	/**
	 * Get condition slug
	 *
	 * @return string
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 * Get condition label
	 *
	 * @return string
	 */
	public function get_label() {
		return $this->label;
	}

}
