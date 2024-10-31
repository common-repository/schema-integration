<?php
/**
 * Abstract schema
 *
 * @package   Schema_Itegration\Core\Schema
 * @author    Custom Backend
 * @copyright Copyright © 2019
 * @license   GPL-2.0+
 * @wordpress-plugin
 */

namespace Schema_Itegration\Core\Schema\Data;

/**
 * Class Data
 */
abstract class Data {

	/**
	 * Template schema
	 *
	 * @var array
	 */
	protected $schema_template;
	/**
	 * Plugin settings
	 *
	 * @var array
	 */
	protected $options;
	/**
	 * Variables
	 *
	 * @var array
	 */
	private $variables;

	/**
	 * Cache
	 *
	 * @var array
	 */
	protected $cache;

	/**
	 * Data constructor.
	 *
	 * @param array $schema_template Schema Template.
	 * @param array $options         Plugin settings.
	 */
	public function __construct( array $schema_template, array $options ) {
		$this->schema_template = $schema_template;
		$this->options         = $options;
		$this->init();
	}

	/**
	 * Register variables.
	 *
	 * @return array
	 */
	abstract protected function register_variable();

	/**
	 * Register data variables.
	 */
	final protected function init() {
		$this->variables = $this->register_variable();
	}

	/**
	 * Get all registered variables
	 *
	 * @return array
	 */
	public function get_keys() {
		$keys = [];
		foreach ( $this->variables as $variable ) {
			$keys[ $variable->get_variable() ] = $variable->get_name();
		}

		return $keys;
	}

	/**
	 * Get h1 from content
	 *
	 * @param string $content HTML content.
	 *
	 * @return string
	 */
	public function get_h1( $content ) {
		preg_match( '|<h1(.*)>(.*)</h1>|iUs', $content, $matches );

		return ! empty( $matches[2] ) ? $matches[2] : '';
	}


	/**
	 * Replace variable in sting
	 *
	 * @param array $value Text for value.
	 *
	 * @return array
	 */
	protected function replace( array $value ) {
		if ( isset( $value['value'] ) ) {
			foreach ( $this->variables as $variable ) {
				$variable_name = $variable->get_variable();
				if ( false !== strpos( $value['value'], $variable_name ) ) {
					$callback         = $variable->get_callback_info();
					$callback['args'] = $this->update_args( $callback['args'], $value['custom'] ?? '' );
					$variable_result  = $this->get_variable_result( $variable_name, $callback );
					$value['value']   = str_replace( $variable_name, $variable_result, $value['value'] );
				}
			}
		}

		return $value;
	}

	/**
	 * Get result of current variable.
	 *
	 * @param string $variable_name Name current variable.
	 * @param array  $callback      Callback parameters for current variable.
	 *
	 * @return string
	 */
	private function get_variable_result( $variable_name, array $callback ) {
		$variable_result = isset( $this->cache[ $variable_name ] ) ? $this->cache[ $variable_name ] : $this->do_callback( $callback );
		if ( empty( $custom ) ) {
			$this->save_cache( $variable_name, $variable_result );
		}

		return $variable_result;
	}

	/**
	 * Set custom parameter
	 *
	 * @param array  $args   Current arguments.
	 * @param string $custom Custom parameter.
	 *
	 * @return array
	 */
	private function update_args( array $args, $custom = '' ) {
		if ( ! empty( $custom ) ) {
			$args['custom'] = $custom;
		}

		return array_values( $args );
	}

	/**
	 * Do callback
	 *
	 * @param array $callback Callback name and parameters.
	 *
	 * @return string
	 */
	private function do_callback( array $callback ) {
		return call_user_func( $callback['name'], ...$callback['args'] );
	}

	/**
	 * Save variable to cache.
	 *
	 * @param string $variable_name   Name current variable.
	 * @param string $variable_result Result current variable.
	 */
	private function save_cache( $variable_name, $variable_result ) {
		if ( false === stripos( $variable_name, '{custom' ) ) {
			$this->cache[ $variable_name ] = $variable_result;
		}
	}

	/**
	 * Get array for json
	 *
	 * @return array
	 */
	public function update_template() {
		foreach ( $this->schema_template as $key => $schema_row ) {
			if ( ! empty( $schema_row['value'] ) ) {
				$this->schema_template[ $key ] = $this->replace( $schema_row );
			}
		}

		return $this->schema_template;
	}

}
