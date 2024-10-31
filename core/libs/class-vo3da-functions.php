<?php
/**
 * Custom autoload function.
 *
 * @since      1.0.0
 *
 * @package    Code_Inserter\Core\Libs
 */

namespace Schema_Integration\Core\Libs;

use WP_Filesystem_Direct;

/**
 * Class Autoload_Functions
 *
 * @package Code_Inserter\Core\Libs
 */
class Vo3da_Functions {

	/**
	 * Create a database if necessary
	 *
	 * @param string $database_name Name database.
	 * @param string $sql           SQL for creating database.
	 *
	 * @return bool
	 */
	public static function maybe_create_table( $database_name, $sql ) {
		if ( ! function_exists( 'maybe_create_table' ) ) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		}

		return maybe_create_table( $database_name, $sql );
	}

	/**
	 * Create instance WP_Filesystem
	 *
	 *
	 * phpcs:disable WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
	 */
	public static function WP_Filesystem() {
		//phpcs:enable WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
		global $wp_filesystem;
		if ( null === $wp_filesystem ) {
			if ( ! class_exists( 'WP_Filesystem_Base' ) || ! function_exists( 'WP_Filesystem' ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
				require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
				require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';
			}
			WP_Filesystem();
		}

		return new WP_Filesystem_Direct( null );
	}

	/**
	 * Check active plugin or no
	 *
	 * @param string $plugin plugin folder and main file.
	 *
	 * @return bool
	 */
	public static function is_plugin_active( $plugin ) {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		return is_plugin_active( $plugin );
	}

	/**
	 * Return current site protocol
	 *
	 * @return string
	 */
	public static function get_protocol() {
		return ( ! empty( $_SERVER['HTTPS'] ) && ( 'on' === $_SERVER['HTTPS'] || 1 === $_SERVER['HTTPS'] ) ) || ( ! empty( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && 'https' === $_SERVER['HTTP_X_FORWARDED_PROTO'] ) ? 'https' : 'http';
	}

	/**
	 * Recursive sanitation for an array
	 *
	 * @param mixed $array Data for sanitize.
	 *
	 * @return mixed
	 */
	public static function recursive_sanitize_text_field( $array ) {
		foreach ( $array as $key => &$value ) {
			if ( is_array( $value ) || is_object( $value ) ) {
				$value = self::recursive_sanitize_text_field( $value );
			} else {
				$value = sanitize_text_field( $value );
			}
		}

		return $array;
	}

}
