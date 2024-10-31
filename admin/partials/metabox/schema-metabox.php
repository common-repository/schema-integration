<?php
/**
 * Template of schema_integration post type metabox
 *
 * @since   1.0.0
 * @package Schema_Integration\Admin
 */

$plugin_title = ! empty( $data['plugin_title'] ) ? $data['plugin_title'] : 'Schema Integration';
$plugin_name  = ! empty( $data['plugin_name'] ) ? $data['plugin_name'] : 'schema_integration';
$schema_id    = ! empty( $data['post_id'] ) ? $data['post_id'] : null;
$security     = ! empty( $data['security'] ) ? $data['security'] : '';
$conditions   = ! empty( $data['list_conditions'] ) ? $data['list_conditions'] : [];
$nonce        = ! empty( $data['nonce_field'] ) ? $data['nonce_field'] : '';
?>
<h3><?php echo esc_html( $plugin_title ); ?></h3>
<div id=app data-id="<?php echo esc_attr( $schema_id ); ?>"
		data-security="<?php echo esc_attr( $security ); ?>"
		data-conditions='<?php echo wp_json_encode( $conditions ); ?>'></div>
<?php wp_nonce_field( $plugin_name, $plugin_name . '_nonce', false, true ); ?>
