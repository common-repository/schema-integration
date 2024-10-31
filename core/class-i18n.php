<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       /
 * @since      1.0.0
 *
 * @package    Schema_Integration
 * @subpackage Schema_Integration/core
 */

namespace Schema_Integration\Core;

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Schema_Integration
 * @subpackage Schema_Integration/core
 * @author     Vo3da
 */
class I18n {

	/**
	 * The name of the plugin
	 *
	 * @var string
	 */
	private $plugin_name;

	/**
	 * I18n constructor.
	 *
	 * @param string $plugin_name The name of the plugin.
	 */
	public function __construct( $plugin_name ) {
		$this->plugin_name = $plugin_name;
	}

	/**
	 * Run I18n actions and filters
	 */
	public function hooks() {
		add_action( 'plugins_loaded', [ $this, 'load_plugin_textdomain' ] );
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			$this->plugin_name,
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}

}
