<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @since      1.0.0
 *
 * @package    Schema_Integration
 * @subpackage Schema_Integration/Admin
 */

namespace Schema_Integration\Admin;

use QM_DB;
use Schema_Integration\Core\Condition\Condition_List;
use Schema_Integration\Core\Libs\Vo3da_Functions;
use Schema_Integration\Core\Schema\Data\Custom_Data;
use Schema_Integration\Core\Schema\Data\Post_Data;
use Schema_Integration\Core\Schema\Data\Site_Data;
use Schema_Integration\Core\Schema\Data\Term_Data;
use Schema_Integration\Core\Schema\Template_List;
use WP_Admin_Bar;
use WP_Post;
use wpdb;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Schema_Integration
 * @subpackage Schema_Integration/admin
 * @author     Custom Backend
 */
class Admin {

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The string used to uniquely identify this plugin.
	 */
	private $plugin_name;
	/**
	 * The plugin title.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The plugin title.
	 */
	private $plugin_title;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of the plugin.
	 */
	private $version;

	/**
	 * The plugin settings
	 *
	 * @var array $options
	 */
	private $options;

	/**
	 * Current page name string
	 *
	 * @var mixed
	 */
	private $page;
	/**
	 * Instance of WPDB
	 *
	 * @var QM_DB|string|wpdb
	 */
	private $db;
	/**
	 * Instance of Template_List
	 *
	 * @var Template_List
	 */
	private $template_list;

	/**
	 * Admin constructor.
	 *
	 * @param array         $plugin_info   Plugin information.
	 * @param array         $options       Plugin options.
	 * @param Template_List $template_list Instance of Template_List.
	 */
	public function __construct( array $plugin_info, array $options, Template_List $template_list ) {

		$this->plugin_name   = $plugin_info['name'];
		$this->plugin_title  = $plugin_info['title'];
		$this->version       = $plugin_info['version'];
		$this->options       = $options;
		$this->page          = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING );
		$this->options       = $options;
		$this->template_list = $template_list;
		global $wpdb;
		$this->db = $wpdb;

	}

	/**
	 * Run admin actions and filters
	 */
	public function hooks() {
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_styles' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'admin_menu', [ $this, 'add_menu' ] );
		add_action( 'admin_init', [ $this, 'register_schema_settings' ] );
		add_action( 'init', [ $this, 'register_post_types' ] );
		add_action( 'add_meta_boxes', [ $this, 'register_meta_boxes' ] );
		add_action( 'save_post', [ $this, 'save_schema_meta' ] );
		add_action( 'admin_bar_menu', [ $this, 'admin_bar_menu' ], 1000 );
		add_action( 'wp_ajax_get_schema_settings', [ $this, 'get_schema_settings' ] );
	}

	/**
	 * Disable WP Emotions for correct plugin work.
	 */
	public function disable_emojis() {
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 *
	 * @add_action('admin_enqueue_scripts', 'enqueue_styles')
	 */
	public function enqueue_styles() {
		if ( $this->is_plugin_page() ) {
			wp_enqueue_style( $this->plugin_name . '-materialdesignicons', plugin_dir_url( __FILE__ ) . 'src/css/materialdesignicons.min.css', [], $this->version, 'all' );
			wp_enqueue_style( $this->plugin_name . '-chunk-vendors', plugin_dir_url( __FILE__ ) . 'src/js/schema/dist/css/chunk-vendors.css', [], $this->version, 'all' );
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'src/js/schema/dist/css/app.css', [], $this->version, 'all' );
			$this->disable_emojis();
		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 *
	 * @add_action('admin_enqueue_scripts', 'enqueue_scripts')
	 */
	public function enqueue_scripts() {
		if ( $this->is_plugin_page() ) {
			wp_enqueue_script( $this->plugin_name . '-chunk-vendors.js', plugin_dir_url( __FILE__ ) . 'src/js/schema/dist/js/chunk-vendors.js', [], $this->version, true );
			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'src/js/schema/dist/js/app.js', [ 'wp-i18n' ], $this->version, true );
		}
	}

	/**
	 * Register settings for plugin options.
	 */
	public function register_schema_settings() {
		register_setting( $this->plugin_name, $this->plugin_name );
	}

	/**
	 * Add plugin page in WordPress menu.
	 */
	public function add_menu() {
		add_submenu_page(
			'edit.php?post_type=' . $this->plugin_name,
			$this->plugin_name,
			esc_attr__( 'Settings', 'schema_integration' ),
			'manage_options',
			$this->plugin_name,
			[
				$this,
				'page_options',
			]
		);
	}

	/**
	 * Plugin page callback.
	 */
	public function page_options() {

		$data = [
			'settings_fields' => $this->settings_fields(),
			'plugin_name'     => $this->plugin_name,
			'plugin_title'    => $this->plugin_title,
			'options'         => $this->options,
		];

		require_once plugin_dir_path( __FILE__ ) . 'partials/options/page-options.php';
	}

	/**
	 * Register custom post type
	 */
	public function register_post_types() {
		register_post_type(
			'schema_integration',
			[
				'labels'             => [
					'name'               => __( 'Schema Int.', 'schema_integration' ),
					'singular_name'      => __( 'Schema', 'schema_integration' ),
					'add_new'            => __( 'Add new schema', 'schema_integration' ),
					'add_new_item'       => __( 'Add new schema', 'schema_integration' ),
					'edit_item'          => __( 'Edit schema', 'schema_integration' ),
					'new_item'           => __( 'New schema', 'schema_integration' ),
					'view_item'          => __( 'View schema', 'schema_integration' ),
					'search_items'       => __( 'Search schema', 'schema_integration' ),
					'not_found'          => __( 'Schemas not found', 'schema_integration' ),
					'not_found_in_trash' => __( 'Schemas not fount in trash', 'schema_integration' ),
					'parent_item_colon'  => '',
					'menu_name'          => __( 'Schema Int.', 'schema_integration' ),

				],
				'public'             => false,
				'publicly_queryable' => false,
				'show_ui'            => true,
				'supports'           => [ 'title' ],
			]
		);
	}

	/**
	 * Register metaboxes
	 */
	public function register_meta_boxes() {
		add_meta_box( $this->plugin_name, $this->plugin_title, [ $this, 'schema_meta_box' ], [ 'schema_integration' ] );
	}

	/**
	 * Callback for schema metabox
	 *
	 * @param WP_Post $post Current post.
	 */
	public function schema_meta_box( WP_Post $post ) {

		$data = [
			'security'        => wp_create_nonce( $this->plugin_name . '_nonce' ),
			'list_conditions' => $this->list_conditions(),
			'post_id'         => $post->ID,
		];

		require_once plugin_dir_path( __FILE__ ) . 'partials/metabox/schema-metabox.php';
	}

	/**
	 * Get all variables
	 *
	 * @param array $schema_markup Current schema markup.
	 *
	 * @return array
	 */
	private function variables( $schema_markup ) {

		$object   = null;
		$settings = [
			'storage'     => [
				'name'    => __( 'Data types', 'schema_integration' ),
				'options' => [
					'{none}'   => '',
					'{object}' => __( 'Object', 'schema_integration' ),
				],
			],
			'site_data'   => [
				'name' => __( 'Site data', 'schema_integration' ),
			],
			'post_data'   => [
				'name' => __( 'Post data', 'schema_integration' ),
			],
			'term_data'   => [
				'name' => __( 'Term data', 'schema_integration' ),
			],
			'custom_data' => [
				'name' => __( 'Custom data', 'schema_integration' ),
			],
		];

		$posts = get_posts(
			[
				'post_type'      => [ 'a', 's' ],
				'posts_per_page' => 1,
			]
		);

		if ( isset( $posts[0] ) ) {
			$post_data                        = new Post_Data( $schema_markup, $this->options, $posts[0] );
			$settings['post_data']['options'] = $post_data->get_keys();
		}
		$terms = get_terms(
			[
				'taxonomy'   => 'category',
				'number'     => 1,
				'hide_empty' => false,
			]
		);
		if ( isset( $terms[0] ) ) {
			$term_data                        = new Term_Data( $schema_markup, $this->options, $terms[0] );
			$settings['term_data']['options'] = $term_data->get_keys();
		}
		$site_data                        = new Site_Data( $schema_markup, $this->options );
		$settings['site_data']['options'] = $site_data->get_keys();

		if ( isset( $posts[0] ) ) {
			$object = $posts[0];
		} elseif ( isset( $terms[0] ) ) {
			$object = $terms[0];
		}

		if ( ! empty( $object ) ) {
			$custom_data                        = new Custom_Data( $schema_markup, $this->options, $object );
			$settings['custom_data']['options'] = $custom_data->get_keys();
		}

		return $settings;
	}

	/**
	 * Get list condition
	 *
	 * @return array
	 */
	private function list_conditions() {
		$result     = [];
		$list       = new Condition_List();
		$conditions = $list->get_conditions();
		if ( ! empty( $conditions ) ) {
			foreach ( $conditions as $group ) {
				if ( ! empty( $group['name'] ) ) {
					$group_item = [
						'text'     => $group['name'],
						'value'    => $group['name'],
						'disabled' => true,
					];
					$result[]   = $group_item;
					if ( ! empty( $group['options'] ) ) {
						foreach ( $group['options'] as $option ) {
							if ( ! empty( $option ) ) {
								$option_item = [
									'text'     => $option->get_label(),
									'value'    => $option->get_slug(),
									'disabled' => false,
								];
								$result[]    = $option_item;
							}
						}
					}
				}
			}
		}

		return $result;
	}

	/**
	 * Get current schema settings
	 */
	public function get_schema_settings() {
		$_REQUEST = (array) wp_unslash( json_decode( file_get_contents( 'php://input' ) ) );
		$result   = [];
		check_ajax_referer( $this->plugin_name . '_nonce', 'security' );
		$post_id = ! empty( $_REQUEST['post_id'] ) ? (int) $_REQUEST['post_id'] : false;
		if ( $post_id ) {
			$result = [
				'schema'      => get_post_meta( $post_id, 'schema_integration_source', true ),
				'conditions'  => wp_json_encode( maybe_unserialize( get_post_meta( $post_id, 'schema_integration_conditions', true ) ) ),
				'schema_name' => get_post_meta( $post_id, 'schema_integration_name', true ),
			];
		}

		wp_send_json( $result );
		wp_die();
	}

	/**
	 * Save metabox
	 *
	 * @param int $post_id Current post_id.
	 */
	public function save_schema_meta( $post_id ) {

		if ( ! empty( $_POST[ $this->plugin_name . '_nonce' ] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ $this->plugin_name . '_nonce' ] ) ), $this->plugin_name ) ) {
			if ( ! empty( $_POST['schema'] ) ) {
				$schema = sanitize_text_field( wp_unslash( $_POST['schema'] ) );
				update_post_meta( $post_id, 'schema_integration', $schema );
			}
			if ( ! empty( $_POST['source'] ) ) {
				$source = sanitize_text_field( wp_unslash( $_POST['source'] ) );
				update_post_meta( $post_id, 'schema_integration_source', $source );
			}
			if ( ! empty( $_POST['conditions'] ) ) {
				$conditions = json_decode( wp_unslash( $_POST['conditions'] ), true );
				$conditions = Vo3da_Functions::recursive_sanitize_text_field( $conditions );
				update_post_meta( $post_id, 'schema_integration_conditions', $conditions );
			}
			if ( ! empty( $_POST['schema_name'] ) ) {
				$schema_name = sanitize_text_field( wp_unslash( $_POST['schema_name'] ) );
				update_post_meta( $post_id, 'schema_integration_name', $schema_name );
			}
		}
	}

	/**
	 * Get all schemas
	 *
	 * @param object $object WP_Post|WP_Term.
	 *
	 * @return array
	 */
	private function get_current_schemas( $object ) {
		$sql_conditions  = str_replace(
			[
				'{',
				'}',
			],
			[
				'\\{',
				'\\}',
			],
			$this->template_list->get_page_sql_condition( $object )
		);
		$hash            = wp_hash( $sql_conditions );
		$current_schemas = wp_cache_get( 'current_schema_integration_' . $hash );
		if ( empty( $current_schemas ) && ! empty( $sql_conditions ) ) {
			$sql             = $this->db->prepare(
				'SELECT p.ID, p.post_title, pm.meta_value
				FROM ' . $this->db->posts . ' as p 
				INNER JOIN ' . $this->db->postmeta . ' pm
				ON ( pm.post_id = p.ID AND pm.meta_key = "schema_integration_conditions" AND pm.meta_value REGEXP %s )
				WHERE post_type = "schema_integration" AND post_status="publish"',
				$sql_conditions
			);
			$current_schemas = $this->db->get_results( $sql );
			wp_cache_set( 'current_schema_integration_' . $hash, $current_schemas );
		}

		return ! empty( $current_schemas ) ? $current_schemas : [];
	}

	/**
	 * Add admin bar menu
	 *
	 * @param WP_Admin_Bar $wp_admin_bar Admin bar object.
	 */
	public function admin_bar_menu( WP_Admin_Bar $wp_admin_bar ) {
		if ( is_admin() ) {
			return;
		}
		$object = get_queried_object();
		if ( empty( $object ) ) {
			return;
		}

		$current_schemas = $this->get_current_schemas( $object );
		if ( ! empty( $current_schemas ) ) {
			$wp_admin_bar->add_menu(
				[
					'id'    => $this->plugin_name,
					'title' => $this->plugin_name,
				]
			);
			foreach ( $current_schemas as $schema ) {
				$wp_admin_bar->add_menu(
					[
						'parent' => $this->plugin_name,
						'id'     => 'schema-' . $schema->ID,
						'title'  => $schema->post_title,
						'href'   => get_edit_post_link( $schema->ID ),
						'meta'   => [ 'target' => '_blank' ],
					]
				);
			}
		}
	}

	/**
	 * Settings fields
	 *
	 * @return string
	 */
	private function settings_fields() {
		ob_start();
		settings_fields( $this->plugin_name );

		return ob_get_clean();
	}

	/**
	 * Check plugin admin page
	 *
	 * @return bool
	 */
	private function is_plugin_page() {
		global $current_screen, $post_type;

		return 'schema_integration' === $post_type || strpos( $current_screen->base, $this->plugin_name );
	}

}
