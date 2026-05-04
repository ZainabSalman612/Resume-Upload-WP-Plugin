<?php
/**
 * Plugin Name:       Simple Resume Drop
 * Description:       A lightweight plugin to create a dedicated resume upload page (/resume-drop) without using the Media Library.
 * Version:           1.0.0
 * Author:            Zainab Salman
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'SRD_VERSION', '1.0.0' );
define( 'SRD_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SRD_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 */
function activate_simple_resume_drop() {
	require_once SRD_PLUGIN_DIR . 'includes/class-srd-activator.php';
	SRD_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_simple_resume_drop() {
	require_once SRD_PLUGIN_DIR . 'includes/class-srd-deactivator.php';
	SRD_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_simple_resume_drop' );
register_deactivation_hook( __FILE__, 'deactivate_simple_resume_drop' );

/**
 * Include the core files
 */
require_once SRD_PLUGIN_DIR . 'includes/class-srd-ajax.php';
require_once SRD_PLUGIN_DIR . 'public/class-srd-public.php';
require_once SRD_PLUGIN_DIR . 'admin/class-srd-admin.php';

// Initialize the plugin
function run_simple_resume_drop() {
	$plugin_public = new SRD_Public();
	$plugin_public->init();

	$plugin_admin = new SRD_Admin();
	$plugin_admin->init();

	$plugin_ajax = new SRD_Ajax();
	$plugin_ajax->init();
}
run_simple_resume_drop();
