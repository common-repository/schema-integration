<?php
/**
 * The public area functionality of the plugin.
 *
 * @since      1.0.0
 *
 * @package    Vo3da
 * @subpackage Vo3da/Front
 */

namespace Schema_Integration\Front;

use Schema_Integration\Core\Schema\Schema;
use Schema_Integration\Core\Schema\Template_List;

/**
 * The public-facing functionality of the plugin.
 *
 * @link       /
 * @since      1.0.0
 *
 * @package    Vo3da
 * @subpackage Vo3da/Front
 */
class Front {

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name
	 */
	private $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version
	 */
	private $version;

	/**
	 * The plugin settings for current domain
	 *
	 * @var array $options
	 */
	private $options;
	/**
	 * @var Template_List
	 */
	private $template_list;

	/**
	 * Public constructor.
	 *
	 * @param array         $plugin_info
	 * @param array         $options The plugin settings for current domain.
	 * @param Template_List $template_list
	 */
	public function __construct( array $plugin_info, array $options, Template_List $template_list ) {

		$this->plugin_name   = $plugin_info['name'];
		$this->version       = $plugin_info['version'];
		$this->options       = $options;
		$this->template_list = $template_list;

	}

	/**
	 * Run front actions and filters
	 */
	public function hooks() {
		add_action( 'wp_footer', [ $this, 'schema' ] );
		add_action( 'schema_integration', [ $this, 'schema_by_object' ] );
		if ( $this->options['enable_in_amp'] ) {
			add_action( 'ampforwp_global_after_footer', [ $this, 'schema' ] );
		}
	}

	/**
	 * Show all schemas
	 */
	public function schema() {
		$object = get_queried_object();

		if ( empty( $object ) ) {
			if ( is_front_page() || is_home() ) {
				$object_id = get_option( 'page_on_front' );
				$object    = get_post( $object_id );
			}
		}

		if ( ! empty( $object ) ) {
			$this->render_schema( $object );
		}
	}

	/**
	 * Render schema by object
	 * You can call this function from outside.
	 *
	 * @param object $object WP_Post/WP_Term object.
	 */
	public function schema_by_object( $object ) {
		$this->render_schema( $object );
	}

	/**
	 * Render schemas by order
	 *
	 * @param object $object WP_Post or WP_Term object.
	 */
	private function render_schema( $object ) {
		$schemas = $this->template_list->templates( $object );
		$schema  = new Schema( $object, $schemas );
		$schema->script();
	}

}
