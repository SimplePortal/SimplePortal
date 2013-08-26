<?php

/**
 * @package SimplePortal
 *
 * @author SimplePortal Team
 * @copyright 2013 SimplePortal Team
 * @license BSD 3-clause 
 *
 * @version 2.4
 */

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
{
	$_GET['debug'] = 'Blue Dream!';
	require_once(dirname(__FILE__) . '/SSI.php');
}
elseif (!defined('SMF'))
	exit('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

global $context;

$hooks = array(
	'integrate_pre_include' => array('$sourcedir/Subs-PortalIntegration.php', '$sourcedir/Subs-Portal.php'),
	'integrate_load_theme' => array('sportal_init'),
	'integrate_actions' => array('sp_integrate_actions'),
	'integrate_admin_areas' => array('sp_integrate_admin_areas'),
	'integrate_load_permissions' => array('sp_integrate_load_permissions'),
	'integrate_buffer' => array('sp_query_string'),
);

$integration_function = empty($context['uninstalling']) ? 'add_integration_function' : 'remove_integration_function';
foreach ($hooks as $hook => $functions)
{
	foreach ($functions as $function)
		$integration_function($hook, $function);
}

if (SMF == 'SSI')
	echo 'Integration changes were carried out successfully.';

?>