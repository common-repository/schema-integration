<?php
/**
 * Variable for schema property
 *
 * @package   Schema_Integration\Core\Schema
 * @author    Custom Backend
 * @copyright Copyright © 2019
 * @license   GPL-2.0+
 * @wordpress-plugin
 */

namespace Schema_Integration\Core\Schema;

/**
 * Class Schema_Variable
 *
 * @package Schema_Integration\Core\Schema
 */
class Schema_Variable {

	/**
	 * Variable
	 *
	 * @var string
	 */
	private $variable;
	/**
	 * Variable name
	 *
	 * @var string
	 */
	private $name;
	/**
	 * Callback
	 *
	 * @var callable
	 */
	private $callback;
	/**
	 * Arguments for callback
	 *
	 * @var array
	 */
	private $args;

	/**
	 * Schema_Variable constructor.
	 *
	 * @param string   $variable Variable in {}.
	 * @param string   $name     Name.
	 * @param callable $callback Callback name.
	 * @param array    $args     Arguments for callback.
	 */
	public function __construct( $variable, $name, callable $callback, array $args = [] ) {
		$this->variable = $variable;
		$this->name     = $name;
		$this->callback = $callback;
		$this->args     = $args;
	}

	/**
	 * Get variable
	 *
	 * @return string
	 */
	public function get_variable() {
		return $this->variable;
	}

	/**
	 * Get variable name
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Get name and arguments of the callback
	 *
	 * @return array
	 */
	public function get_callback_info() {
		return [
			'name' => $this->callback,
			'args' => $this->args,
		];
	}

}
