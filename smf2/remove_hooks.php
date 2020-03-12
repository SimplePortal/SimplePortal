<?php

/**
 * @package SimplePortal
 *
 * @author SimplePortal Team
 * @copyright 2020 SimplePortal Team
 * @license BSD 3-clause
 *
 * @version 2.3.8
 */

// Handle running this file by using SSI.php
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
{
	$_GET['debug'] = 'Blue Dream!';
	require_once(dirname(__FILE__) . '/SSI.php');
}
// Hmm... no SSI.php and no SMF?
elseif (!defined('SMF'))
	die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

// Remove file hooks
remove_integration_function('integrate_admin_include', '$sourcedir/PortalAdminMain.php');
remove_integration_function('integrate_pre_include', '$sourcedir/Subs-Portal.php');
remove_integration_function('integrate_pre_include', '$sourcedir/PortalHooks.php');

// Remove function hooks
remove_integration_function('integrate_action', 'sportal_actions');
remove_integration_function('integrate_admin_areas', 'sportal_admin_areas');
remove_integration_function('integrate_buffer', 'sportal_buffer');
remove_integration_function('integrate_load_permissions', 'sportal_load_permissions');
remove_integration_function('integrate_load_theme', 'sportal_init');
remove_integration_function('integrate_menu_buttons', 'sportal_menu_buttons');
remove_integration_function('integrate_pre_load', 'sportal_language_files');
remove_integration_function('integrate_redirect', 'sportal_redirect');
remove_integration_function('integrate_whos_online', 'sportal_whos_online');

?>