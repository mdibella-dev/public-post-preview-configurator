<?php
/**
 * Plugin Name: Public Post Preview Configurator
 * Plugin URI: http://www.bjoerne.com
 * Version: 1.0.1
 * Description: Enables you to configure 'public post preview' plugin with a user interface.
 * Author: Björn Weinbrenner
 * Author URI: http://www.bjoerne.com/
 *
 * License: GPLv2 or later
 */

include_once dirname(__FILE__).'/options.php';

add_filter( 'ppp_nonce_life', 'ppp_configurator_nonce_life');
if (is_admin()) {
	add_action('admin_init', 'ppp_configurator_register_setting');
	add_action('admin_menu', 'ppp_configurator_add_options_page');
}

function ppp_configurator_register_setting() {
	register_setting('ppp_configurator_group', 'ppp_configurator_expiration_hours');
}

function ppp_configurator_add_options_page() {
	add_options_page(__('Public Post Preview Configurator'), __('Public Post Preview Configurator'), 'manage_options', basename(__FILE__), 'ppp_configurator_show_settings_page');
}

function ppp_configurator_nonce_life($nonce_life) {
	$expiration_hours = (int) get_option('ppp_configurator_expiration_hours');
	if ($expiration_hours && $expiration_hours > 0) {
		return 60 * 60 * $expiration_hours;
	}
	return $nonce_life;
}
