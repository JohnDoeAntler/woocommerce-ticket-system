<?php
// import the composer plugin
require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
// import the utils function
require_once plugin_dir_path( __FILE__ ) . 'includes/utils/query.php';
// import the utils function
require_once plugin_dir_path( __FILE__ ) . 'includes/utils/revoke.php';
// import the utils function
require_once plugin_dir_path( __FILE__ ) . 'includes/utils/log.php';

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://github.com/johndoeantler
 * @since             1.0.0
 * @package           Ticket_System
 *
 * @wordpress-plugin
 * Plugin Name:       Ticket System
 * Plugin URI:        http://github.com/johndoeantler/woocommerce-ticket-system
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            John Doe Antler
 * Author URI:        http://github.com/johndoeantler
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       ticket-system
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

$is_woocommerce_activated = in_array( 'woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')));
$is_ts_activated = in_array( 'ticket-system/ticket-system.php', apply_filters('active_plugins', get_option('active_plugins')));

if (!$is_woocommerce_activated  && !$is_ts_activated) {
	die('cannot activate ticket system unless woocommerce has been activated.');
}

/**
 * Check if WooCommerce is active
 **/
if ($is_woocommerce_activated) {
	/**
	 * Currently plugin version.
	 * Start at version 1.0.0 and use SemVer - https://semver.org
	 * Rename this for your plugin and update it as you release new versions.
	 */
	define( 'TICKET_SYSTEM_VERSION', '1.0.0' );

	/**
	 * The code that runs during plugin activation.
	 * This action is documented in includes/class-ticket-system-activator.php
	 */
	function activate_ticket_system() {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-ticket-system-activator.php';
		Ticket_System_Activator::activate();
	}

	/**
	 * The code that runs during plugin deactivation.
	 * This action is documented in includes/class-ticket-system-deactivator.php
	 */
	function deactivate_ticket_system() {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-ticket-system-deactivator.php';
		Ticket_System_Deactivator::deactivate();
	}

	register_activation_hook( __FILE__, 'activate_ticket_system' );
	register_deactivation_hook( __FILE__, 'deactivate_ticket_system' );

	/**
	 * The core plugin class that is used to define internationalization,
	 * admin-specific hooks, and public-facing site hooks.
	 */
	require plugin_dir_path( __FILE__ ) . 'includes/class-ticket-system.php';

	/**
	 * Begins execution of the plugin.
	 *
	 * Since everything within the plugin is registered via hooks,
	 * then kicking off the plugin from this point in the file does
	 * not affect the page life cycle.
	 *
	 * @since    1.0.0
	 */
	function run_ticket_system() {

		$plugin = new Ticket_System();
		$plugin->run();

	}
	run_ticket_system();
}
