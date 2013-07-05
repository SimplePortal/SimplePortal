<?php

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
				'permission' => array('sp_admin', 'sp_manage_settings', 'sp_manage_blocks', 'sp_manage_articles', 'sp_manage_pages', 'sp_manage_shoutbox'),
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
						'label' => $txt['sp-adminColumnArticles'],
						'file' => 'PortalAdminArticles.php',
						'function' => 'sportal_admin_articles_main',
						'icon' => 'articles.png',
						'permission' => array('sp_admin', 'sp_manage_articles'),
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
				),
			);
		}
	}
}

?>