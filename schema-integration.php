<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              /
 * @since             1.0.0
 * @package           Schema_Integration
 *
 * @wordpress-plugin
 * Plugin Name:       Schema Integration
 * Plugin URI:        /
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.2.1
 * Author:            VO3DA Team
 * Author URI:        vo3da.tech
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       schema_integration
 * Domain Path:       schema_integration/core/languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

use Schema_Integration\Core\Main;
use Schema_Integration\Core\Activator;
use Schema_Integration\Core\Deactivator;

require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'SCHEMA_INTEGRATION_VERSION', '1.2.1' );

/**
 * The code that runs during plugin activation.
 */
function activate_schema_integration() {
	Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivation_schema_integration() {
	Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_schema_integration' );
register_deactivation_hook( __FILE__, 'deactivation_schema_integration' );

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */

$main = new Main();
$main->init();
