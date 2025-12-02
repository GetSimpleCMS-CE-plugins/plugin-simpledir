<?php

// == Constants ==
define('SIMPLEDIR', basename(__FILE__, '.php'));
define('SIMPLEDIR_PLUGINPATH', GSPLUGINPATH . SIMPLEDIR . '/');
define('SIMPLEDIR_CONFIGFILE', GSDATAOTHERPATH . 'simpledir.xml');
define('SIMPLEDIR_PLUGINURL', $SITEURL . 'plugins/' . SIMPLEDIR . '/');
define('SIMPLEDIR_IMGURL', SIMPLEDIR_PLUGINURL . 'images/');

// == Common functions (used throughout plugin) =
require_once(SIMPLEDIR_PLUGINPATH . 'common_functions.php');

// == Languages ==
i18n_merge(SIMPLEDIR) || i18n_merge(SIMPLEDIR, 'en_US');

// == Register plugin ==
register_plugin(
	SIMPLEDIR,
	simpledir_i18n('PLUGIN_TITLE'),
	'1.0',
	'CE Team ',
	'https://getsimple-ce.ovh/ce-plugins',
	simpledir_i18n('PLUGIN_DESC'),
	'plugins',
	'simpledir_config'
);

// == Register actions and filters ==
// Sidebar link
add_action('plugins-sidebar','createSideMenu', array(SIMPLEDIR, simpledir_i18n('PLUGIN_SIDEBAR')));
// Placeholder filter
add_filter('content','simpledir_display');

// == Register styles ==
// SimpleDir responsive styles
register_style('simpledir-css', SIMPLEDIR_PLUGINURL . 'css/simpledir.css', null, 'screen');
queue_style('simpledir-css', GSFRONT);

// == Functions ==
// Admin Panel
function simpledir_config() {
	// Load admin functions
	include(SIMPLEDIR_PLUGINPATH . 'admin_functions.php');

	// Process POST form
	if (!empty($_POST)) {
		$data = array();

		// Validation
		if (isset($_POST['dirpath'])) {
			$data['dirpath'] = urldecode($_POST['dirpath']);
		}

		if (isset($_POST['urlpath'])) {
			$data['urlpath'] = urldecode($_POST['urlpath']);
		}

		if (isset($_POST['ignore'])) {
			$data['ignore'] = explode(',', urldecode($_POST['ignore']));
		}

		$succ = simpledir_saveconf($data);

		if ($succ) {
			simpledir_admin_message('updated', i18n_r('SETTINGS_UPDATED'));
		} else {
			simpledir_admin_message('error', i18n_r('ER_SETTINGS_UPD'));
		}
	}

	// Load config
	$simpledir_conf = simpledir_loadconf(true);

	// Settings Page
	include(SIMPLEDIR_PLUGINPATH . 'save_config.php');
}

// Frontend Display
function simpledir_display($contents) {
	require_once(SIMPLEDIR_PLUGINPATH . 'display_functions.php');
	return preg_replace_callback('/\(% simpledir(.*?)%\)/i', 'simpledir_display_callback', $contents);
}