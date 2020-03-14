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

	void sportal_redirect()
		// !!!

	void sportal_whos_online()
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

	if ($modSettings['sp_portal_mode'] == 3 && empty($context['standalone']) && empty($context['disable_sp']))
		$context['current_action'] = 'forum';

	if(empty($context['disable_sp']) && ((isset($_GET['board']) || isset($_GET['topic']) || in_array($context['current_action'], array('unread', 'unreadreplies', 'collapse', 'recent', 'stats', 'who'))) && in_array($modSettings['sp_portal_mode'], array(1, 3))))
		$context['current_action'] = 'forum';
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

function sportal_redirect(&$setLocation, &$refresh)
{
	global $scripturl, $context, $modSettings;

	$add = preg_match('~^(ftp|http)[s]?://~', $setLocation) == 0 && substr($setLocation, 0, 6) != 'about:';

	// Set the default redirect location as the forum or the portal.
	if ((empty($setLocation) || $scripturl == $setLocation) && in_array($modSettings['sp_portal_mode'], array(1, 3)))
	{
		// Redirect the user to the forum.
		if (!empty($modSettings['sp_disableForumRedirect']))
			$setLocation = 'action=forum';
		// Redirect the user to the SSI.php standalone portal.
		elseif ($modSettings['sp_portal_mode'] == 3)
		{
			$setLocation = $context['portal_url'];
			$add = false;
		}
	}
	if (WIRELESS)
	{
		// Add the scripturl on if needed.
		if ($add)
			$setLocation = $scripturl . '?' . $setLocation;

		$char = strpos($setLocation, '?') === false ? '?' : ';';

		if (strpos($setLocation, '#') !== false)
			$setLocation = strtr($setLocation, array('#' => $char . WIRELESS_PROTOCOL . '#'));
		else
			$setLocation .= $char . WIRELESS_PROTOCOL;
	}
	elseif ($add)
		$setLocation = $scripturl . ($setLocation != '' ? '?' . $setLocation : '');

	if (!empty($modSettings['queryless_urls']) && (empty($context['server']['is_cgi']) || @ini_get('cgi.fix_pathinfo') == 1 || @get_cfg_var('cgi.fix_pathinfo') == 1) && (!empty($context['server']['is_apache']) || !empty($context['server']['is_lighttpd'])))
	{
		if (defined('SID') && SID != '')
			$setLocation = preg_replace_callback('~^' . preg_quote($scripturl, '/') . '\?(?:' . SID . '(?:;|&|&amp;))((?:page)=[^#]+?)(#[^"]*?)?$~', 'fix_redirect_sid__preg_callback', $setLocation);
		else
			$setLocation = preg_replace_callback('~^' . preg_quote($scripturl, '/') . '\?((?:page)=[^#"]+?)(#[^"]*?)?$~', 'fix_redirect_path__preg_callback', $setLocation);
	}

	// patch malformed setLocation
	preg_match_all('~(((https|http|ftps|ftp)\:\/\/)|(www\.))[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\:[0-9]+)?(\/\S*)?~i', $setLocation, $matches, PREG_PATTERN_ORDER);
	$setLocation = !empty($matches[0]) ? str_replace($scripturl . '??', $scripturl . '?', end($matches[0])) : str_replace($scripturl . '??', $scripturl . '?', $setLocation);
	$setLocation = preg_match('~^(ftp|http)[s]?://~', $setLocation) == 0 && substr($setLocation, 0, 6) != 'about:' ? $scripturl . '?' . $setLocation : $setLocation;

	// Put the session ID in.
	if (defined('SID') && SID != '' && strpos($setLocation, SID) === false && strpos($setLocation, $scripturl) !== false)
		$setLocation = preg_replace('/^' . preg_quote($scripturl, '/') . '(?!\?' . preg_quote(SID, '/') . ')\\??/', $scripturl . '?' . SID . ';', $setLocation);
}

function sportal_whos_online($actions)
{
	global $smcFunc, $scripturl, $modSettings, $txt;

	if ($modSettings['sp_portal_mode'] == 1)
	{
		$txt['who_index'] = sprintf($txt['sp_who_index'], $scripturl);
		$txt['whoall_forum'] = sprintf($txt['sp_who_forum'], $scripturl);
	}
	elseif ($modSettings['sp_portal_mode'] == 3)
		$txt['whoall_portal'] = sprintf($txt['sp_who_index'], $scripturl);

	list($integrate_action, $page_ids) = array('', array());

	if (isset($actions['page']))
	{
		$integrate_action = $txt['who_hidden'];
		$page_ids[$actions['page']][] = $txt['sp_who_page'];
	}

	if (!empty($page_ids))
	{
		$numeric_ids = array();
		$string_ids = array();
		$page_where = array();

		foreach ($page_ids as $page_id => $dummy)
			if (is_numeric($page_id))
				$numeric_ids[] = (int) $page_id;
			else
				$string_ids[] = $page_id;

		if (!empty($numeric_ids))
			$page_where[] = 'id_page IN ({array_int:numeric_ids})';

		if (!empty($string_ids))
			$page_where[] = 'namespace IN ({array_string:string_ids})';

		$result = $smcFunc['db_query']('', '
			SELECT id_page, namespace, title, permission_set, groups_allowed, groups_denied
			FROM {db_prefix}sp_pages
			WHERE ' . implode(' OR ', $page_where) . '
			LIMIT {int:limit}',
			array(
				'numeric_ids' => $numeric_ids,
				'string_ids' => $string_ids,
				'limit' => count($page_ids),
			)
		);
		$page_data = array();
		while ($row = $smcFunc['db_fetch_assoc']($result))
		{
			if (!sp_allowed_to('page', $row['id_page'], $row['permission_set'], $row['groups_allowed'], $row['groups_denied']))
				continue;

			$page_data[] = array(
				'id' => $row['id_page'],
				'namespace' => $row['namespace'],
				'title' => $row['title'],
			);
		}
		$smcFunc['db_free_result']($result);

		if (!empty($page_data))
		{
			foreach ($page_data as $page)
			{
				if (isset($page_ids[$page['id']]))
					foreach ($page_ids[$page['id']] as $k => $session_text)
						$integrate_action = sprintf($session_text, $page['id'], censorText($page['title']), $scripturl);

				if (isset($page_ids[$page['namespace']]))
					foreach ($page_ids[$page['namespace']] as $k => $session_text)
						$integrate_action = sprintf($session_text, $page['namespace'], censorText($page['title']), $scripturl);
			}
		}
	}

	return $integrate_action;
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
	global $scripturl, $modSettings, $user_info, $txt, $language, $helptxt;

	// Load the Simple Portal Help file.
	loadLanguage('SPortalHelp', sp_languageSelect('SPortalHelp'));

	// Load our language files
	loadLanguage('SPortal', '', false);
	$cur_language = isset($user_info['language']) ? $user_info['language'] : $language;
	if ($cur_language !== 'english')
		loadLanguage('SPortal', 'english', false);
}

?>