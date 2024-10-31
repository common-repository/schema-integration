<?php
/**
 * Schema Data for Term
 *
 * @package   Schema_Integration\Core\Schema
 * @author    Custom Backend
 * @copyright Copyright © 2019
 * @license   GPL-2.0+
 * @wordpress-plugin
 */

namespace Schema_Integration\Core\Schema\Data;

use Schema_Integration\Core\Schema\Schema_Variable;
use Schema_Itegration\Core\Schema\Data\Data;
use WP_Term;

/**
 * Class Term_Data
 */
class Term_Data extends Data {

	/**
	 * Current WP_Term
	 *
	 * @var WP_Term
	 */
	private $term;

	/**
	 * Term_Data constructor.
	 *
	 * @param array   $schema_template Schema template.
	 * @param array   $options         Plugin settings.
	 * @param WP_Term $term            Current WP_Term.
	 */
	public function __construct( array $schema_template, array $options, WP_Term $term ) {
		$this->term = $term;
		parent::__construct( $schema_template, $options );
	}

	/**
	 * Register variables.
	 */
	protected function register_variable() {
		return apply_filters(
			'custom_schema_term_data',
			[
				new Schema_Variable( '{term_name}', 'Название термина', 'trim', [ $this->term->name ] ),
				new Schema_Variable( '{term_link}', 'Ссылка термина', 'get_term_link', [ $this->term ] ),
				new Schema_Variable( '{term_description}', 'Описание термина', 'term_description', [ $this->term ] ),
				new Schema_Variable(
					'{term_h1}',
					'Заголовок H1 термина',
					[
						$this,
						'get_h1',
					],
					[
						term_description( $this->term ),
					]
				),
				new Schema_Variable(
					'{term_date_published}',
					'Дата публикации термина',
					'get_term_meta',
					[
						$this->term->term_id,
						'term_published_date',
						true,
					]
				),
				new Schema_Variable(
					'{term_last_modify}',
					'Дата изменения термина',
					'get_term_meta',
					[
						$this->term->term_id,
						'term_last_mod',
						true,
					]
				),
			],
			$this->term
		);
	}

}
