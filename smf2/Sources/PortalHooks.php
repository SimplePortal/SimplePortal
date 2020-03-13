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

defined('SMF') OR exit('<b>Hacking attempt...</b>');

/*
	void sportal_array_insert()
		// !!!

	void sportal_actions()
		// !!!

	void sportal_admin_areas()
		// !!!

	void sportal_menu_buttons()
		// !!!

	void sportal_permissions()
		// !!!

	void sportal_buffer()
		// !!!

	void sportal_language_files()
		// !!!
*/

function sportal_array_insert(&$input, $key, $insert, $where = 'before', $strict = false)
{
	$position = array_search($key, array_keys($input), $strict);

	// key not found -> insert as last
	if ($position === false)
	{
		$input = array_merge($input, $insert);
		return;
	}

	if ($where === 'after')
		$position += 1;

	// insert as first
	if ($position === 0)
		$input = array_merge($insert, $input);
	else
		$input = array_merge(
			array_slice($input, 0, $position),
			$insert,
			array_slice($input, $position)
		);
}

function sportal_actions(&$actionArray)
{
	global $context;

	if (empty($context['disable_sp']))
	{
		$actionArray['forum'] = array('BoardIndex.php', 'BoardIndex');
		$actionArray['portal'] = array('PortalMain.php', 'sportal_main');
	}

	if (!isset($_REQUEST['action']) || !($_REQUEST['action'] == 'portal' && isset($_GET['xml'])) && !isset($actionArray[$_REQUEST['action']]))
		unset($_REQUEST['action']);
}

function sportal_admin_areas(&$admin_areas)
{
	global $txt;

	sportal_array_insert($admin_areas, 'members',
		array(
			'portal' => array(
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
						'subsections' => array(
							'articles' => array($txt['sp-adminArticleListName']),
							'addarticle' => array($txt['sp-adminArticleAddName']),
							'categories' => array($txt['sp-adminCategoryListName']),
							'addcategory' => array($txt['sp-adminCategoryAddName']),
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
				),
			),
		)
	);
}

function sportal_menu_buttons(&$menu_buttons)
{
	global $context, $modSettings, $user_info, $txt, $scripturl;

	$menu_buttons['home'] = array(
		'title' => $txt['home'],
		'href' => $modSettings['sp_portal_mode'] == 3 && empty($context['disable_sp']) ? $modSettings['sp_standalone_url'] : $scripturl,
		'show' => true,
		'sub_buttons' => array(
		),
		'is_last' => $context['right_to_left'],
	);

	sportal_array_insert($menu_buttons, 'home',
		array(
			'forum' => array(
				'title' => empty($txt['sp-forum']) ? 'Forum' : $txt['sp-forum'],
				'href' => $scripturl . ($modSettings['sp_portal_mode'] == 1 && empty($context['disable_sp']) ? '?action=forum' : ''),
				'show' => in_array($modSettings['sp_portal_mode'], array(1, 3)) && empty($context['disable_sp']),
				'sub_buttons' => array(
				),
			)
		), 'after', true
	);
}

function sportal_permissions(&$permissionGroups, &$permissionList, &$leftPermissionGroups, &$hiddenPermissions, &$relabelPermissions)
{
	global $context, $modSettings;

	$permissionList['membergroup'] += array(
			'sp_admin' => array(false, 'sp', 'sp'),
			'sp_manage_settings' => array(false, 'sp', 'sp'),
			'sp_manage_blocks' => array(false, 'sp', 'sp'),
			'sp_manage_articles' => array(false, 'sp', 'sp'),
			'sp_manage_pages' => array(false, 'sp', 'sp'),
			'sp_manage_shoutbox' => array(false, 'sp', 'sp'),
			'sp_add_article' => array(false, 'sp', 'sp'),
			'sp_auto_article_approval' => array(false, 'sp', 'sp'),
			'sp_remove_article' => array(false, 'sp', 'sp'),
	);


	$permissionGroups['membergroup']['simple'] += array(
			'sp',
	);

	$permissionGroups['membergroup']['classic'] += array(
			'sp',
	);


	$context['non_guest_permissions'] += array(
		'sp_admin',
		'sp_manage_settings',
		'sp_manage_blocks',
		'sp_manage_articles',
		'sp_manage_pages',
		'sp_manage_shoutbox',
		'sp_add_article',
		'sp_auto_article_approval',
		'sp_remove_article',
	);
}

function sportal_buffer($buffer)
{
	global $modSettings, $scripturl, $context;
	@ini_set('memory_limit', '128M');

	if (function_exists('sp_query_string'))
		$buffer = sp_query_string($buffer);

	// This should work even in 4.2.x, just not CGI without cgi.fix_pathinfo.
	if (!empty($modSettings['queryless_urls']) && (!$context['server']['is_cgi'] || ini_get('cgi.fix_pathinfo') == 1 || @get_cfg_var('cgi.fix_pathinfo') == 1) && ($context['server']['is_apache'] || $context['server']['is_lighttpd'] || $context['server']['is_litespeed']))
	{
		// Let's do something special for session ids!
		if (defined('SID') && SID != '')
			$buffer = preg_replace_callback('~"' . preg_quote($scripturl, '/') . '\?(?:' . SID . '(?:;|&|&amp;))((?:page)=[^#"]+?)(#[^"]*?)?"~', 'sid_insert__preg_callback', $buffer);
		else
			$buffer = preg_replace_callback('~"' . preg_quote($scripturl, '/') . '\?((?:page)=[^#"]+?)(#[^"]*?)?"~', 'pathinfo_insert__preg_callback', $buffer);
	}

	return $buffer;
}

function sportal_language_files()
{
	global $user_info, $txt, $language, $helptxt;

	// Load the Simple Portal Help file.
	loadLanguage('SPortalHelp', sp_languageSelect('SPortalHelp'));

	// Load our language files
	loadLanguage('SPortal', '', false);
	$cur_language = isset($user_info['language']) ? $user_info['language'] : $language;
	if ($cur_language !== 'english')
		loadLanguage('SPortal', 'english', false);
}

?>