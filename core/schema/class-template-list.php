<?php
/**
 * List of JSON templates for Schema
 *
 * @package   Schema_Integration\Core\Schema
 * @author    Custom Backend
 * @copyright Copyright © 2019
 * @license   GPL-2.0+
 * @wordpress-plugin
 */

namespace Schema_Integration\Core\Schema;

use Schema_Integration\Core\Libs\Vo3da_Functions;
use WP_Filesystem_Base;

/**
 * Class Template_List
 *
 * @package Schema_Integration\Core\Schema
 */
class Template_List {

	/**
	 * The name of this name
	 *
	 * @var string
	 */
	private $plugin_name;
	/**
	 * Filesystem
	 *
	 * @var WP_Filesystem_Base
	 */
	private $filesystem;
	/**
	 * Folder with global templates.
	 *
	 * @var string
	 */
	private $folder;
	/**
	 * Database
	 *
	 * @var wpdb
	 */
	private $db;
	/**
	 * The options of this plugin
	 *
	 * @var array
	 */
	private $options;

	/**
	 * Template_List constructor.
	 *
	 * @param array $plugin_info Name, slug and version of the plugin.
	 * @param array $options     The options of this plugin.
	 */
	public function __construct( array $plugin_info, array $options ) {
		$this->plugin_name = $plugin_info['name'];
		$this->filesystem  = Vo3da_Functions::WP_Filesystem();
		$this->folder      = __DIR__ . '/../../global/';
		$this->options     = $options;
		global $wpdb;
		$this->db = $wpdb;
	}

	/**
	 * Get current page conditions
	 *
	 * @param object $object WP_Post|WP_Term.
	 *
	 * @return array
	 */
	public function get_page_condition( $object ) {
		$reg = [];
		if ( is_a( $object, 'WP_Post' ) ) {
			$front_page = (int) get_option( 'page_on_front' );
			if ( $object->ID === $front_page ) {
				$reg[] = [
					'condition' => '{is_front_page}',
				];
			} else {
				$conditions = [
					[
						'condition' => '{is_singular}',
					],
					[
						'condition' => '{is_' . $object->post_type . '}',
					],
					[
						'condition' => '{custom_post_id}',
						'custom'    => (string) $object->ID,
					],
				];
				$reg        = $reg + $conditions;

				$terms = $this->db->get_results(
					'SELECT term_id FROM ' . $this->db->term_taxonomy . ' WHERE  term_taxonomy_id IN (
						SELECT term_taxonomy_id FROM ' . $this->db->term_relationships . ' WHERE object_id = ' . $object->ID . '
					)'
				);
				if ( $terms ) {
					foreach ( $terms as $term ) {
						$reg[] = [
							'condition' => '{custom_posts_in_term_id}',
							'custom'    => (string) $term->term_id,
						];
					}
				}
			}
		} elseif ( is_a( $object, 'WP_Term' ) ) {
			$reg[] = [
				'condition' => '{is_archive}',
			];
			$reg[] = [
				'condition' => '{is_' . $object->taxonomy . '}',
			];
			$reg[] = [
				'condition' => '{custom_term_id}',
				'custom'    => (string) $object->term_id,
			];
		}

		return $reg;
	}

	/**
	 * Get all schema templates
	 *
	 * @param object $object WP_Post|WP_Term.
	 *
	 * @return array
	 */
	private function post_type_templates( $object ) {
		$sql_conditions   = str_replace(
			[
				'{',
				'}',
			],
			[
				'\\{',
				'\\}',
			],
			$this->get_page_sql_condition( $object )
		);
		$hash             = wp_hash( $sql_conditions );
		$schema_templates = wp_cache_get( 'current_schema_integration_templates_' . $hash );

		if ( empty( $schema_templates ) && ! empty( $sql_conditions ) ) {
			$sql              = $this->db->prepare(
				'SELECT post_id, meta_value FROM ' . $this->db->postmeta . ' WHERE meta_key = "schema_integration" AND post_id IN (
					SELECT post_id FROM ' . $this->db->postmeta . ' WHERE post_id IN (
						SELECT ID FROM ' . $this->db->posts . ' WHERE post_type = "schema_integration" AND post_status="publish"
					) AND meta_key = "schema_integration_conditions" AND meta_value REGEXP "%s"
				)',
				$sql_conditions
			);
			$schema_templates = $this->db->get_results( $sql );
			if ( ! empty( $schema_templates ) ) {

				$schema_templates = wp_list_pluck( $schema_templates, 'meta_value', 'post_id' );

				wp_cache_set( 'current_schema_integration_templates_' . $hash, $schema_templates );
			}
		}

		return ! empty( $schema_templates ) ? $schema_templates : [];
	}

	/**
	 * Create condition for sql
	 *
	 * @param object $object WP_Post/WP_Term object.
	 *
	 * @return string
	 */
	public function get_page_sql_condition( $object ) {

		$page_conditions = $this->get_page_condition( $object );
		foreach ( $page_conditions as $key => $condition ) {
			if ( 0 === strpos( $condition['condition'], '{custom_' ) ) {
				$page_conditions[ $key ] = maybe_serialize( $condition );
			} else {
				$page_conditions[ $key ] = $condition['condition'];
			}
		}

		return implode( '|', $page_conditions );
	}

	/**
	 * Get templates for current page.
	 *
	 * @param object $object WP_Post/WP_Term object.
	 *
	 * @return array
	 */
	public function templates( $object ) {
		$post_type_templates = ! empty( $this->post_type_templates( $object ) ) ? $this->post_type_templates( $object ) : [];
		$file_templates      = ! empty( $this->file_templates( $object ) ) ? $this->file_templates( $object ) : [];

		return $post_type_templates + $file_templates;
	}

	/**
	 * Get all global templates.
	 *
	 * @param object $object WP_Post/WP_Term object.
	 *
	 * @return array
	 */
	private function file_templates( $object ) {
		$templates       = [];
		$page_conditions = $this->get_page_condition( $object );
		$global          = ! empty( $this->options['global'] ) ? $this->options['global'] : [];
		if ( ! empty( $global ) ) {
			foreach ( $global as $schema_name => $schema_conditions ) {
				if ( ! empty( $schema_conditions ) ) {
					foreach ( $schema_conditions as $condition ) {
						if ( $this->check_condition( $condition, $page_conditions ) ) {
							$template = $this->filesystem->get_contents( $this->folder . $schema_name );
							if ( ! empty( $template ) ) {
								$templates[] = json_decode( $template, true );
							}
							break;
						}
					}
				}
			}
		}

		return $templates;
	}

	/**
	 * Check condition
	 *
	 * @param array $condition       Current condition.
	 * @param array $page_conditions Current page conditions.
	 *
	 * @return bool
	 */
	private function check_condition( array $condition, array $page_conditions ) {
		foreach ( $page_conditions as $page_condition ) {
			if ( $page_condition['condition'] === $condition['condition'] ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get all global template names
	 *
	 * @return array
	 */
	public function file_template_names() {
		if ( ! $this->filesystem->exists( $this->folder ) ) {
			return [];
		}
		$dir = dir( $this->folder );
		if ( ! $dir ) {
			return [];
		}

		$templates = [];

		//phpcs:disable WordPress.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
		while ( false !== ( $entry = $dir->read() ) ) {
			if ( '.' === $entry || '..' === $entry ) {
				continue;
			}

			$full_path = $this->folder . '/' . $entry;

			if ( ! $this->filesystem->is_file( $full_path ) ) {
				continue;
			}

			$file = new \SplFileInfo( $full_path );
			if ( 'json' === $file->getExtension() ) {
				$templates[] = $file->getFilename();
			}
		}
		//phpcs:enable WordPress.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
		$dir->close();
		unset( $dir );

		return $templates;
	}

}
