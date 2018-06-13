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

global $boarddir, $modSettings, $package_cache;

$defaults = array(
	'sp_portal_mode' => 1,
	'sp_disableForumRedirect' => 1,
	'showleft' => 1,
	'showright' => 1,
	'leftwidth' => 200,
	'rightwidth' => 200,
	'sp_adminIntegrationHide' => 1,
	'sp_resize_images' => 1,
	'sp_articles_index_per_page' => 5,
	'sp_articles_index_total' => 20,
	'sp_articles_per_page' => 10,
	'sp_articles_comments_per_page' => 20,
	'sp_mobile_disable' => 1,
	
);

$updates = array(
	'sp_version' => '2.4',
);

foreach ($defaults as $index => $value)
{
	if (!isset($modSettings[$index]))
		$updates[$index] = $value;
}

updateSettings($updates);

$standalone_file = $boarddir . '/PortalStandalone.php';
if (isset($package_cache[$standalone_file]))
	$package_cache[$standalone_file] = str_replace('full/path/to/forum', $boarddir, $package_cache[$standalone_file]);
elseif (file_exists($standalone_file))
{
	$current_data = file_get_contents($standalone_file);
	if (strpos($current_data, 'full/path/to/forum') !== false)
	{
		$fp = fopen($standalone_file, 'w+');
		fwrite($fp, str_replace('full/path/to/forum', $boarddir, $current_data));
		fclose($fp);
	}
}

if (SMF == 'SSI')
	echo 'Settings changes were carried out successfully.';
