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

use Schema_Integration\Core\Schema\Schema_Variable;
use Schema_Itegration\Core\Schema\Data\Data;

/**
 * Class Post_Data
 */
class Site_Data extends Data {


	/**
	 * Register variables.
	 *
	 * @return array
	 */
	protected function register_variable() {
		return apply_filters(
			'custom_schema_integration_site_data',
			[
				new Schema_Variable( '{site_name}', 'Название сайта', 'get_bloginfo', [ 'name' ] ),
				new Schema_Variable( '{site_description}', 'Описание сайта', 'get_bloginfo', [ 'description' ] ),
				new Schema_Variable( '{site_url}', 'Адрес сайта', 'get_bloginfo', [ 'url' ] ),
				new Schema_Variable( '{site_logo}', 'Логотип сайта', [ $this, 'get_logo' ] ),
			]
		);
	}

	/**
	 * Get logo url
	 *
	 * @return string
	 */
	public function get_logo() {
		$logo_url = '';
		if ( ! empty( $this->options['logo'] ) ) {
			$logo_url = wp_get_attachment_image_url( $this->options['logo'], 'full' );
		}

		return $logo_url;
	}

}
