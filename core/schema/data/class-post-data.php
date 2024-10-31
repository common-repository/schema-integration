<?php
/**
 * Schema Data for Post
 *
 * @package   Schema_Itegration\Core\Schema
 * @author    Custom Backend
 * @copyright Copyright © 2019
 * @license   GPL-2.0+
 * @wordpress-plugin
 */

namespace Schema_Integration\Core\Schema\Data;

use DateTime;
use Schema_Integration\Core\Schema\Schema_Variable;
use Schema_Itegration\Core\Schema\Data\Data;
use WP_Post;

/**
 * Class Post_Data
 */
class Post_Data extends Data {

	/**
	 * Current WP_Post
	 *
	 * @var WP_Post
	 */
	private $post;

	/**
	 * Post_Data constructor.
	 *
	 * @param array   $schema_template Schema template.
	 * @param array   $options         Plugin settings.
	 * @param WP_Post $post            Current WP_Post.
	 */
	public function __construct( array $schema_template, array $options, WP_Post $post ) {
		$this->post = $post;
		parent::__construct( $schema_template, $options );
	}

	/**
	 * Get post excerpt
	 *
	 * @param WP_Post $post Current post.
	 *
	 * @return string
	 */
	public function get_the_excerpt( WP_Post $post ) {
		if ( has_excerpt( $post ) ) {
			$excerpt = get_the_excerpt( $post );
		} else {
			$excerpt = wp_trim_excerpt( $post->post_content );
		}

		return $excerpt;
	}

	/**
	 * Get post image
	 *
	 * @param WP_Post $post        Current post.
	 * @param string  $default_img Default post image.
	 *
	 * @return string
	 */
	public function get_post_image_url( WP_Post $post, $default_img ) {
		$post_image_url = get_the_post_thumbnail_url( $post, 'full' );
		if ( empty( $post_image_url ) && ! empty( $default_img ) ) {
			$post_image_url = wp_get_attachment_image_url( $default_img, 'full' );
		}

		return ! empty( $post_image_url ) ? $post_image_url : '';
	}

	/**
	 * Register variables.
	 *
	 * @return array
	 */
	protected function register_variable() {
		return apply_filters(
			'custom_schema_integration_post_data',
			[
				new Schema_Variable( '{post_title}', 'Заголовок записи', 'get_the_title', [ $this->post->ID ] ),
				new Schema_Variable(
					'{post_content}',
					'Контент записи',
					'apply_filters',
					[
						'the_content',
						$this->post->post_content,
					]
				),
				new Schema_Variable(
					'{post_excerpt}',
					'Краткое описание записи',
					[
						$this,
						'get_the_excerpt',
					],
					[
						$this->post,
					]
				),
				new Schema_Variable( '{post_permalink}', 'Ссылка на запись', 'get_the_permalink', [ $this->post->ID ] ),
				new Schema_Variable(
					'{post_h1}',
					'Заголовок H1 записи',
					[
						$this,
						'get_h1',
					],
					[
						$this->post->post_content,
					]
				),
				new Schema_Variable(
					'{post_date}',
					'Дата публикации записи',
					'get_post_time',
					[
						DateTime::ATOM,
						true,
						$this->post,
					]
				),
				new Schema_Variable(
					'{post_modified}',
					'Дата изменения записи',
					'get_the_modified_date',
					[
						DateTime::ATOM,
						$this->post,
					]
				),
				new Schema_Variable(
					'{post_thumbnail}',
					'Изображение записи',
					[
						$this,
						'get_post_image_url',
					],
					[
						$this->post,
						! empty( $this->options['default_img'] ) ? $this->options['default_img'] : '',
					]
				),
			],
			$this->post
		);
	}

}
