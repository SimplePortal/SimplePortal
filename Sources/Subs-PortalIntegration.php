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

if (!defined('SMF'))
	die('Hacking attempt...');

function sp_integrate_actions(&$actions)
{
	global $context;

	if (!empty($context['disable_sp']))
		return;

	$actions['forum'] = array('BoardIndex.php', 'BoardIndex');
	$actions['portal'] = array('PortalMain.php', 'sportal_main');
}

function sp_integrate_admin_areas(&$admin_areas)
{
	global $txt;

	$temp = $admin_areas;
	$admin_areas = array();

	foreach ($temp as $area => $data)
	{
		$admin_areas[$area] = $data;

		if ($area == 'layout')
		{
			$admin_areas['portal'] = array(
				'title' => $txt['sp-adminCatTitle'],
				'permission' => array('sp_admin', 'sp_manage_settings', 'sp_manage_blocks', 'sp_manage_articles', 'sp_manage_pages', 'sp_manage_shoutbox', 'sp_manage_profiles'),
				'areas' => array(
					'portalconfig' => array(
						'label' => $txt['sp-adminConfiguration'],
						'file' => 'PortalAdminMain.php',
						'function' => 'sportal_admin_config_main',
						'icon' => 'configuration.png',
						'permission' => array('sp_admin', 'sp_manage_settings'),
						'subsections' => array(
							'information' => array($txt['sp-info_title']),
							'generalsettings' => array($txt['sp-adminGeneralSettingsName']),
							'blocksettings' => array($txt['sp-adminBlockSettingsName']),
							'articlesettings' => array($txt['sp-adminArticleSettingsName']),
						),
					),
					'portalblocks' => array(
						'label' => $txt['sp-blocksBlocks'],
						'file' => 'PortalAdminBlocks.php',
						'function' => 'sportal_admin_blocks_main',
						'icon' => 'blocks.png',
						'permission' => array('sp_admin', 'sp_manage_blocks'),
						'subsections' => array(
							'list' => array($txt['sp-adminBlockListName']),
							'add' => array($txt['sp-adminBlockAddName']),
							'header' => array($txt['sp-positionHeader']),
							'left' => array($txt['sp-positionLeft']),
							'top' => array($txt['sp-positionTop']),
							'bottom' => array($txt['sp-positionBottom']),
							'right' => array($txt['sp-positionRight']),
							'footer' => array($txt['sp-positionFooter']),
						),
					),
					'portalarticles' => array(
						'label' => $txt['sp_admin_articles_title'],
						'file' => 'PortalAdminArticles.php',
						'function' => 'sportal_admin_articles_main',
						'icon' => 'articles.png',
						'permission' => array('sp_admin', 'sp_manage_articles'),
						'subsections' => array(
							'list' => array($txt['sp_admin_articles_list']),
							'add' => array($txt['sp_admin_articles_add']),
						),
					),
					'portalcategories' => array(
						'label' => $txt['sp_admin_categories_title'],
						'file' => 'PortalAdminCategories.php',
						'function' => 'sportal_admin_categories_main',
						'icon' => 'categories.png',
						'permission' => array('sp_admin', 'sp_manage_articles'),
						'subsections' => array(
							'list' => array($txt['sp_admin_categories_list']),
							'add' => array($txt['sp_admin_categories_add']),
						),
					),
					'portalpages' => array(
						'label' => $txt['sp_admin_pages_title'],
						'file' => 'PortalAdminPages.php',
						'function' => 'sportal_admin_pages_main',
						'icon' => 'pages.png',
						'permission' => array('sp_admin', 'sp_manage_pages'),
						'subsections' => array(
							'list' => array($txt['sp_admin_pages_list']),
							'add' => array($txt['sp_admin_pages_add']),
						),
					),
					'portalshoutbox' => array(
						'label' => $txt['sp_admin_shoutbox_title'],
						'file' => 'PortalAdminShoutbox.php',
						'function' => 'sportal_admin_shoutbox_main',
						'icon' => 'shoutbox.png',
						'permission' => array('sp_admin', 'sp_manage_shoutbox'),
						'subsections' => array(
							'list' => array($txt['sp_admin_shoutbox_list']),
							'add' => array($txt['sp_admin_shoutbox_add']),
						),
					),
					'portalmenus' => array(
						'label' => $txt['sp_admin_menus_title'],
						'file' => 'PortalAdminMenus.php',
						'function' => 'sportal_admin_menus_main',
						'icon' => 'menus.png',
						'permission' => array('sp_admin', 'sp_manage_menus'),
						'subsections' => array(
							'listmainitem' => array($txt['sp_admin_menus_main_item_list']),
							'addmainitem' => array($txt['sp_admin_menus_main_item_add']),
							'listcustommenu' => array($txt['sp_admin_menus_custom_menu_list']),
							'addcustommenu' => array($txt['sp_admin_menus_custom_menu_add']),
						),
					),
					'portalprofiles' => array(
						'label' => $txt['sp_admin_profiles_title'],
						'file' => 'PortalAdminProfiles.php',
						'function' => 'sportal_admin_profiles_main',
						'icon' => 'profiles.png',
						'permission' => array('sp_admin', 'sp_manage_profiles'),
						'subsections' => array(
							'listpermission' => array($txt['sp_admin_permission_profiles_list']),
							'addpermission' => array($txt['sp_admin_permission_profiles_add']),
							'liststyle' => array($txt['sp_admin_style_profiles_list']),
							'addstyle' => array($txt['sp_admin_style_profiles_add']),
							'listvisibility' => array($txt['sp_admin_visibility_profiles_list']),
							'addvisibility' => array($txt['sp_admin_visibility_profiles_add']),
						),
					),
				),
			);
		}
	}
}

function sp_integrate_load_permissions(&$permission_groups, &$permission_list, &$left_permission_groups, &$hidden_permissions, &$relabel_permissions)
{
	global $context;

	$permission_groups['membergroup']['simple'][] = 'sp';
	$permission_groups['membergroup']['classic'][] = 'sp';

	$permission_list['membergroup'] = array_merge($permission_list['membergroup'], array(
		'sp_admin' => array(false, 'sp', 'sp'),
		'sp_manage_settings' => array(false, 'sp', 'sp'),
		'sp_manage_blocks' => array(false, 'sp', 'sp'),
		'sp_manage_articles' => array(false, 'sp', 'sp'),
		'sp_manage_pages' => array(false, 'sp', 'sp'),
		'sp_manage_shoutbox' => array(false, 'sp', 'sp'),
		'sp_manage_menus' => array(false, 'sp', 'sp'),
		'sp_manage_profiles' => array(false, 'sp', 'sp'),
	));

	$left_permission_groups[] = 'sp';

	$context['non_guest_permissions'] = array_merge($context['non_guest_permissions'], array(
		'sp_admin',
		'sp_manage_settings',
		'sp_manage_blocks',
		'sp_manage_articles',
		'sp_manage_pages',
		'sp_manage_shoutbox',
		'sp_manage_menus',
		'sp_manage_profiles',
	));
}