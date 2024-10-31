<?php
/**
 * Yoast support
 *
 * @package   Schema_Integration\Core\Schema
 * @author    Custom Backend
 * @copyright Copyright © 2019
 * @license   GPL-2.0+
 * @wordpress-plugin
 */

namespace Schema_Integration\Core\Schema\Data\Plugins;

use Schema_Integration\Core\Schema\Schema_Variable;
use WP_Post;
use WP_Term;

/**
 * Class Yoast_Variables
 */
class Yoast_Variables {

	public function hooks() {
		add_filter( 'custom_schema_post_data', [ $this, 'post' ], 10, 2 );
		add_filter( 'custom_schema_term_data', [ $this, 'term' ], 10, 2 );
	}

	/**
	 * Add yoast variables to posts.
	 *
	 * @param array   $variables List of variables.
	 * @param WP_Post $post      Current post.
	 *
	 * @return array
	 */
	public function post( array $variables, WP_Post $post ) {
		$variables[] = new Schema_Variable(
			'{yoast_post_title}',
			'Yoast title для записи',
			[
				$this,
				'get_post_title',
			],
			[
				$post,
			]
		);
		$variables[] = new Schema_Variable(
			'{yoast_post_description}',
			'Yoast description для записи',
			[
				$this,
				'get_post_description',
			],
			[
				$post,
			]
		);

		return $variables;
	}

	/**
	 * Add yoast variables to terms.
	 *
	 * @param array   $variables List of variables.
	 * @param WP_Term $term      Current term.
	 *
	 * @return array
	 */
	public function term( array $variables, WP_Term $term ) {
		$variables[] = new Schema_Variable(
			'{yoast_term_title}',
			'Yoast title для термина',
			[
				$this,
				'get_term_title',
			],
			[
				$term,
			]
		);
		$variables[] = new Schema_Variable(
			'{yoast_term_description}',
			'Yoast description для термина',
			[
				$this,
				'get_term_description',
			],
			[
				$term,
			]
		);

		return $variables;
	}

	/**
	 * Get current yoast title for post
	 *
	 * @param WP_Post $post Current post.
	 *
	 * @return string
	 */
	public function get_post_title( WP_Post $post ) {
		$yoast_title = get_post_meta( $post->ID, '_yoast_wpseo_title', true );
		if ( empty( $yoast_title ) ) {
			$wpseo_titles = get_option( 'wpseo_titles', [] );
			$yoast_title  = isset( $wpseo_titles[ 'title-' . $post->post_type ] ) ? $wpseo_titles[ 'title-' . $post->post_type ] : get_the_title();
		}

		return wpseo_replace_vars( $yoast_title, $post );
	}

	/**
	 * Get current yoast description for post
	 *
	 * @param WP_Post $post Current post.
	 *
	 * @return string
	 */
	public function get_post_description( WP_Post $post ) {
		$yoast_description = get_post_meta( $post->ID, '_yoast_wpseo_metadesc', true );
		if ( empty( $yoast_description ) ) {
			$wpseo_titles      = get_option( 'wpseo_titles', [] );
			$yoast_description = isset( $wpseo_titles[ 'metadesc-' . $post->post_type ] ) ? $wpseo_titles[ 'metadesc-' . $post->post_type ] : '';
		}

		return wpseo_replace_vars( $yoast_description, $post );
	}

	/**
	 * Get current yoast title for term
	 *
	 * @param WP_Term $term Current term.
	 *
	 * @return string
	 */
	public function get_term_title( WP_Term $term ) {
		$meta        = get_option( 'wpseo_taxonomy_meta' );
		$yoast_title = ! empty( $meta[ $term->taxonomy ][ $term->term_id ]['wpseo_title'] ) ? $meta[ $term->taxonomy ][ $term->term_id ]['wpseo_title'] : '';
		if ( empty( $yoast_title ) ) {
			$wpseo_titles = get_option( 'wpseo_titles', [] );
			$yoast_title  = isset( $wpseo_titles[ 'title-tax-' . $term->taxonomy ] ) ? $wpseo_titles[ 'title-tax-' . $term->taxonomy ] : $term->name;
		}

		return wpseo_replace_vars( $yoast_title, $term );
	}

	/**
	 * Get current yoast description for term
	 *
	 * @param WP_Term $term Current term.
	 *
	 * @return string
	 */
	public function get_term_description( WP_Term $term ) {
		$meta              = get_option( 'wpseo_taxonomy_meta' );
		$yoast_description = ! empty( $meta[ $term->taxonomy ][ $term->term_id ]['wpseo_desc'] ) ? $meta[ $term->taxonomy ][ $term->term_id ]['wpseo_desc'] : '';
		if ( empty( $yoast_description ) ) {
			$wpseo_titles      = get_option( 'wpseo_titles', [] );
			$yoast_description = isset( $wpseo_titles[ 'metadesc-tax-' . $term->taxonomy ] ) ? $wpseo_titles[ 'metadesc-tax-' . $term->taxonomy ] : '';
		}

		return wpseo_replace_vars( $yoast_description, $term );
	}

}
