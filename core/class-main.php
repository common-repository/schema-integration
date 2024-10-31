<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       /
 * @since      1.0.0
 *
 * @package    Schema_Integration
 * @subpackage Schema_Integration/core
 */

namespace Schema_Integration\Core;

use Schema_Integration\Core\Libs\Vo3da_Functions;
use Schema_Integration\Core\Schema\Data\Plugins\Yoast_Variables;
use Schema_Integration\Core\Schema\Template_List;
use Schema_Integration\Admin\Admin;
use Schema_Integration\Front\Front;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Schema_Integration
 * @subpackage Schema_Integration/core
 * @author     VO3DA Team
 */
class Main {

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;
	/**
	 * The plugin title
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var string string $plugin_title The plugin title
	 */
	protected $plugin_title;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * The plugin options
	 *
	 * @var array $options
	 */
	private $options;
	/**
	 * @var Template_List
	 */
	private $template;

	/**
	 * Main constructor.
	 */
	public function __construct() {
		if ( defined( 'SCHEMA_INTEGRATION_VERSION' ) ) {
			$this->version = SCHEMA_INTEGRATION_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name  = 'schema_integration';
		$this->plugin_title = 'Schema Integration';
		$options            = get_option( $this->plugin_name );
		$this->options      = ! empty( $options ) ? $options : [];
	}

	/**
	 * Initialization method. Runs admin and front side of plugin.
	 */
	public function init() {
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Get plugins info
	 *
	 * @return array
	 */
	public function plugin_info() {
		return [
			'name'    => $this->plugin_name,
			'title'   => $this->plugin_title,
			'version' => $this->version,
		];
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Plugin_Name_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new I18n( $this->get_plugin_name() );
		$plugin_i18n->hooks();
	}

	public static function options() {
		return get_option( 'schema_integration', [] );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$this->template = new Template_List( $this->plugin_info(), $this->options );
		$admin          = new Admin( $this->plugin_info(), $this->options, $this->template );
		$admin->hooks();

		if ( Vo3da_Functions::is_plugin_active( 'wordpress-seo/wp-seo.php' ) ) {
			$yoast_variables = new Yoast_Variables();
			$yoast_variables->hooks();
		}

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$front = new Front( $this->plugin_info(), $this->options, $this->template );
		$front->hooks();

	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @return    string    The name of the plugin.
	 * @since     1.0.0
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @return    string    The version number of the plugin.
	 * @since     1.0.0
	 */
	public function get_version() {
		return $this->version;
	}

}
