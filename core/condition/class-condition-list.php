<?php
/**
 * List of conditions
 *
 * @package   Schema_Integration\Core\Condition
 * @author    Custom Backend
 * @copyright Copyright © 2019
 * @license   GPL-2.0+
 * @wordpress-plugin
 */

namespace Schema_Integration\Core\Condition;

/**
 * Class Condition_List
 */
class Condition_List {

	/**
	 * List of the conditions
	 *
	 * @var array
	 */
	private $list;

	/**
	 * Get all post type conditions
	 *
	 * @return array
	 */
	private function get_post_type_conditions() {
		$conditions         = [];
		$post_types         = get_post_types( [ 'publicly_queryable' => 1 ], 'objects' );
		$post_types['page'] = get_post_type_object( 'page' );
		foreach ( $post_types as $post_type_slug => $post_type ) {
			$label        = ! empty( $post_type->labels->all_items ) ? $post_type->labels->all_items : 'Все ' . $post_type->label;
			$conditions[] = new Condition( '{is_' . $post_type_slug . '}', $label );
		}

		return $conditions;
	}

	/**
	 * Get all terms conditions
	 *
	 * @return array
	 */
	private function get_taxonomy_conditions() {
		$conditions = [];
		$taxonomies = get_taxonomies( [ 'public' => true ], 'objects' );
		foreach ( $taxonomies as $key => $taxonomy ) {
			$label        = ! empty( $taxonomy->labels->all_items ) ? $taxonomy->labels->all_items : 'Все ' . $taxonomy->label;
			$conditions[] = new Condition( '{is_' . $taxonomy->name . '}', $label );
		}

		return $conditions;
	}

	/**
	 * Create all conditions
	 */
	private function init() {
		$this->list = [
			'none'       => [
				'options' => [
					'' => '',
				],
			],
			'basic'      => [
				'name'    => 'Базовые условия',
				'options' => [
					new Condition( '{is_singular}', 'Все записи и страницы' ),
					new Condition( '{is_archive}', 'Все архивные страницы' ),
					new Condition( '{is_front_page}', 'Главная страница' ),
				],
			],
			'post_types' => [
				'name'    => 'Типы постов',
				'options' => $this->get_post_type_conditions(),
			],
			'taxonomies' => [
				'name'    => 'Типы таксономии',
				'options' => $this->get_taxonomy_conditions(),
			],
			'custom'     => [
				'name'    => 'Произвольные страницы',
				'options' => [
					new Condition( '{custom_post_id}', 'Пост с определеным id' ),
					new Condition( '{custom_posts_in_term_id}', 'Посты из термина с определеным id' ),
					new Condition( '{custom_term_id}', 'Термин с определеным id' ),
				],
			],
		];
	}

	/**
	 * Get list of the conditions
	 *
	 * @return array
	 */
	public function get_conditions() {
		$this->init();

		return $this->list;
	}

}
