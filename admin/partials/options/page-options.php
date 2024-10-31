<?php
/**
 * Template for page options page
 *
 * @since      1.0.0
 *
 * @package    Schema_Integration
 * @subpackage Schema_Integration/Admin
 */

$plugin_title = ! empty( $data['plugin_title'] ) ? $data['plugin_title'] : 'Schema Integration';
$plugin_name  = ! empty( $data['plugin_name'] ) ? $data['plugin_name'] : 'schema_integration';
?>
<div class="wrap">
	<form action="options.php" method="POST">
		<h2><?php echo esc_html( $plugin_title ); ?></h2>
		<?php settings_fields( esc_attr( $plugin_name ) ); ?>
		<p>
			<label>
				<input type="checkbox" name="schema_integration[enable_in_amp]" id="enable_in_amp"
						value="1" <?php checked( ! empty( $data['options']['enable_in_amp'] ) ); ?>>
				<?php esc_attr_e( 'Enable microdata on AMP version?', 'schema_integration' ); ?>
			</label>
		</p>

		<?php submit_button( __( 'Save', 'schema_integration' ) ); ?>
	</form>
</div>
