<?php
/**********************************************************************************
* Subs-Portal.php                                                                 *
***********************************************************************************
* SimplePortal                                                                    *
* SMF Modification Project Founded by [SiNaN] (sinan@simplemachines.org)          *
* =============================================================================== *
* Software Version:           SimplePortal 2.3.5                                  *
* Software by:                SimplePortal Team (http://www.simpleportal.net)     *
* Copyright 2008-2009 by:     SimplePortal Team (http://www.simpleportal.net)     *
* Support, News, Updates at:  http://www.simpleportal.net                         *
***********************************************************************************
* This program is free software; you may redistribute it and/or modify it under   *
* the terms of the provided license as published by Simple Machines LLC.          *
*                                                                                 *
* This program is distributed in the hope that it is and will be useful, but      *
* WITHOUT ANY WARRANTIES; without even any implied warranty of MERCHANTABILITY    *
* or FITNESS FOR A PARTICULAR PURPOSE.                                            *
*                                                                                 *
* See the "license.txt" file for details of the Simple Machines license.          *
* The latest version can always be found at http://www.simplemachines.org.        *
**********************************************************************************/

if (!defined('SMF'))
	die('Hacking attempt...');

/*
	void sportal_init()
		// !!!

	void sportal_init_headers()
		// !!!

	string sportal_catch_action()
		// !!!

	array getBlockInfo()
		// !!!

	bool getShowInfo()
		// !!!

	bool sp_loadPermissions()
		// !!!

	bool sp_allowed_to()
		// !!!

	string sp_languageSelect()
		// !!!

	array sp_loadCalendarData()
		// !!!

	mixed sp_loadColors()
		// !!!

	string sp_embed_image()
		// !!!

	array sportal_parse_style()
		// !!!

	array sportal_get_pages()
		// !!!

	mixed sportal_parse_page()
		// !!!

	array sportal_get_shoutbox()
		// !!!

	array sportal_get_shouts()
		// !!!

	void sportal_create_shout()
		// !!!

	void sportal_delete_shout()
		// !!!

	void sportal_update_shoutbox()
		// !!!

	mixed sp_prevent_flood()
		// !!!

	void sportal_load_compat()
		// !!!
*/

function sportal_init($standalone = false)
{
	global $context, $sourcedir, $scripturl, $modSettings, $txt;
	global $settings, $options, $boarddir, $maintenance, $sportal_version;

	$sportal_version = '2.3.5';

	if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'dlattach')
		return;

	if (!$standalone)
	{
		loadTemplate(false, 'portal');

		if (!empty($context['right_to_left']))
			loadTemplate(false, 'portal_rtl');

		if (!empty($_REQUEST['action']) && in_array($_REQUEST['action'], array('admin', 'helpadmin')))
			loadLanguage('SPortalAdmin', sp_languageSelect('SPortalAdmin'));

		if (!isset($settings['sp_images_url']))
		{
			if (file_exists($settings['theme_dir'] . '/images/sp'))
				$settings['sp_images_url'] =  $settings['theme_url'] . '/images/sp';
			else
				$settings['sp_images_url'] =  $settings['default_theme_url'] . '/images/sp';
		}

		if (!empty($context['current_topic']))
		{
			$context['can_add_article'] = allowedTo(array('sp_admin', 'sp_manage_articles', 'sp_add_article'));
			$context['can_remove_article'] = allowedTo(array('sp_admin', 'sp_manage_articles', 'sp_remove_article'));
		}

		$context['SPortal']['core_compat'] = $settings['name'] == 'Core Theme';
		$context['SPortal']['on_portal'] = getShowInfo(0, 'portal', '');
	}

	if (WIRELESS || ($standalone && (isset($_REQUEST['wap']) || isset($_REQUEST['wap2']) || isset($_REQUEST['imode']))) || !empty($settings['disable_sp']) || empty($modSettings['sp_portal_mode']) || ((!empty($modSettings['sp_maintenance']) || !empty($maintenance)) && !allowedTo('admin_forum')) || isset($_GET['debug']) || (empty($modSettings['allow_guestAccess']) && $context['user']['is_guest']))
	{
		$context['disable_sp'] = true;
		if ($standalone)
		{
			$get_string = '';
			foreach ($_GET as $get_var => $get_value)
				$get_string .= $get_var . (!empty($get_value) ? '=' . $get_value : '') . ';';
			redirectexit(substr($get_string, 0, -1));
		}
		return;
	}

	if (!$standalone)
	{
		require_once($sourcedir . '/PortalBlocks.php');

		if (SMF != 'SSI')
			require_once($boarddir . '/SSI.php');

		loadTemplate('Portal');
		loadLanguage('SPortal', sp_languageSelect('SPortal'));

		if (!empty($modSettings['sp_maintenance']) && !allowedTo('sp_admin'))
			$modSettings['sp_portal_mode'] = 0;

		if (empty($modSettings['sp_standalone_url']))
			$modSettings['sp_standalone_url'] = '';

		if ($modSettings['sp_portal_mode'] == 3)
			$context += array(
				'portal_url' => $modSettings['sp_standalone_url'],
				'page_title' => $context['forum_name'],
			);
		else
			$context += array(
				'portal_url' => $scripturl,
			);

		if ($modSettings['sp_portal_mode'] == 1)
			$context['linktree'][0] = array(
				'url' => $scripturl . '?action=forum',
				'name' => $context['forum_name'],
			);

		// If you want to remove Forum link when it is
		// alone, take out the following two comment lines.
		//if (empty($context['linktree'][1]))
		//	$context['linktree'] = array();

		if (!empty($context['linktree']) && $modSettings['sp_portal_mode'] == 1)
			foreach ($context['linktree'] as $key => $tree)
				if (strpos($tree['url'], '#c') !== false && strpos($tree['url'], 'action=forum#c') === false)
					$context['linktree'][$key]['url'] = str_replace('#c', '?action=forum#c', $tree['url']);
	}

	$context['standalone'] = $standalone;

	// Load the headers if necessary.
	sportal_init_headers();

	$context['SPortal']['sides'] = array(
		5 => array(
			'id' => '5',
			'name' => 'header',
			'active' => true,
		),
		1 => array(
			'id' => '1',
			'name' => 'left',
			'active' => !empty($modSettings['showleft']),
		),
		2 => array(
			'id' => '2',
			'name' => 'top',
			'active' => true,
		),
		3 => array(
			'id' => '3',
			'name' => 'bottom',
			'active' => true,
		),
		4 => array(
			'id' => '4',
			'name' => 'right',
			'active' => !empty($modSettings['showright']),
		),
		6 => array(
			'id' => '6',
			'name' => 'footer',
			'active' => true,
		),
	);

	$blocks = getBlockInfo(null, null, true, true, true);
	$context['SPortal']['blocks'] = array();
	foreach ($blocks as $block)
	{
		if (!$context['SPortal']['sides'][$block['column']]['active'])
			continue;

		$block['style'] = sportal_parse_style('explode', $block['style'], true);

		$context['SPortal']['sides'][$block['column']]['last'] = $block['id'];
		$context['SPortal']['blocks'][$block['column']][] = $block;
	}

	foreach($context['SPortal']['sides'] as $side)
	{
		if (empty($context['SPortal']['blocks'][$side['id']]))
			$context['SPortal']['sides'][$side['id']]['active'] = false;

		$context['SPortal']['sides'][$side['id']]['collapsed'] = $context['user']['is_guest'] ? !empty($_COOKIE['sp_' . $side['name']]) : !empty($options['sp_' . $side['name']]);
	}

	if (!empty($context['template_layers']) && !in_array('portal', $context['template_layers']))
		$context['template_layers'][] = 'portal';
}

// Deals with the initialization of SimplePortal headers.
function sportal_init_headers()
{
	global $context, $settings, $modSettings;
	static $initialized;

	if (!empty($initialized))
		return;

	$context['html_headers'] .= '
	<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/portal.js?235"></script>
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		var sp_images_url = "' . $settings['sp_images_url'] . '";
		function sp_collapseBlock(id)
		{
			mode = document.getElementById("sp_block_" + id).style.display == "" ? 0 : 1;';

	if ($context['user']['is_guest'])
		$context['html_headers'] .= '
			document.cookie = "sp_block_" + id + "=" + (mode ? 0 : 1);';
	else
		$context['html_headers'] .= '
			smf_setThemeOption("sp_block_" + id, mode ? 0 : 1, null, "' . $context['session_id'] . '", "' . $context['session_var'] . '");';

	$context['html_headers'] .= '
			document.getElementById("sp_collapse_" + id).src = smf_images_url + (mode ? "/collapse.gif" : "/expand.gif");
			document.getElementById("sp_block_" + id).style.display = mode ? "" : "none";
		}';

	if (empty($modSettings['sp_disable_side_collapse']))
	{
		$context['html_headers'] .= '
		function sp_collapseSide(id)
		{
			var sp_sides = new Array();
			sp_sides[1] = "sp_left";
			sp_sides[4] = "sp_right";
			mode = document.getElementById(sp_sides[id]).style.display == "" ? 0 : 1;' . ($context['user']['is_guest'] ? '
			document.cookie = sp_sides[id] + "=" + (mode ? 0 : 1);' : '
			smf_setThemeOption(sp_sides[id], mode ? 0 : 1, null, "' . $context['session_id'] . '");') . '
			document.getElementById("sp_collapse_side" + id).src = sp_images_url + (mode ? "/collapse.png" : "/expand.png");
			document.getElementById(sp_sides[id]).style.display = mode ? "" : "none";' . ($context['browser']['is_ie8'] ? '
			document.getElementById("sp_center").style.width = "100%";' : '') . '
		}';
	}

	if ($modSettings['sp_resize_images'])
	{
		if (!$context['browser']['is_ie'] && !$context['browser']['is_mac_ie'])
			$context['html_headers'] .= '
		window.addEventListener("load", sp_image_resize, false);';
		else
			$context['html_headers'] .= '
		var window_oldSPImageOnload = window.onload;
		window.onload = sp_image_resize;';
	}

	$context['html_headers'] .= '
	// ]]></script>';

	$initialized = true;
}

function sportal_catch_action()
{
	global $sourcedir, $modSettings, $board, $topic, $context;

	if (isset($_GET['about:sinan']))
		return 'BookOfSinan';

	if (empty($context['disable_sp']))
	{
		if (empty($board) && empty($topic) && empty($_GET['page']) && $modSettings['sp_portal_mode'] == 1)
		{
			require_once($sourcedir . '/PortalMain.php');
			return 'sportal_main';
		}
		elseif (empty($board) && empty($topic) && !empty($_GET['page']))
		{
			require_once($sourcedir . '/PortalPages.php');
			return 'sportal_pages';
		}
	}

	return false;
}

// This function, returns all of the information about particular blocks.
function getBlockInfo($column_id = null, $block_id = null, $state = null, $show = null, $permission = null)
{
	global $smcFunc, $context, $settings, $options, $txt;

	$query = array();
	$parameters = array();
	if (!empty($column_id))
	{
		$query[] = 'spb.col = {int:col}';
		$parameters['col'] = !empty($column_id) ? $column_id : 0;
	}
	if (!empty($block_id))
	{
		$query[] = 'spb.id_block = {int:id_block}';
		$parameters['id_block'] = !empty($block_id) ? $block_id : 0;
	}
	if (!empty($state))
	{
		$query[] = 'spb.state = {int:state}';
		$parameters['state'] = 1;
	}

	$request = $smcFunc['db_query']('','
		SELECT
			spb.id_block, spb.label, spb.type, spb.col, spb.row, spb.permission_set,
			spb.groups_allowed, spb.groups_denied, spb.state, spb.force_view, spb.display,
			spb.display_custom, spb.style, spp.variable, spp.value
		FROM {db_prefix}sp_blocks AS spb
			LEFT JOIN {db_prefix}sp_parameters AS spp ON (spp.id_block = spb.id_block)' . (!empty($query) ? '
		WHERE ' . implode(' AND ', $query) : '') . '
		ORDER BY spb.col, spb.row',
		$parameters
	);

	$return = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		if (!empty($show) && !getShowInfo($row['id_block'], $row['display'], $row['display_custom']))
			continue;

		if (!empty($permission) && !sp_allowed_to('block', $row['id_block'], $row['permission_set'], $row['groups_allowed'], $row['groups_denied']))
			continue;

		if (!isset($return[$row['id_block']]))
		{
			$return[$row['id_block']] = array(
				'id' => $row['id_block'],
				'label' => $row['label'],
				'type' => $row['type'],
				'type_text' => !empty($txt['sp_function_' . $row['type'] . '_label']) ? $txt['sp_function_' . $row['type'] . '_label'] : $txt['sp_function_unknown_label'],
				'column' => $row['col'],
				'row' => $row['row'],
				'permission_set' => $row['permission_set'],
				'groups_allowed' => $row['groups_allowed'] !== '' ? explode(',', $row['groups_allowed']) : array(), 
				'groups_denied' => $row['groups_denied'] !== '' ? explode(',', $row['groups_denied']) : array(), 
				'state' => empty($row['state']) ? 0 : 1,
				'force_view' => $row['force_view'],
				'display' => $row['display'],
				'display_custom' => $row['display_custom'],
				'style' => $row['style'],
				'collapsed' => $context['user']['is_guest'] ? !empty($_COOKIE['sp_block_' . $row['id_block']]) : !empty($options['sp_block_' . $row['id_block']]),
				'parameters' => array(),
			);
		}

		if (!empty($row['variable']))
			$return[$row['id_block']]['parameters'][$row['variable']] = $row['value'];
	}
	$smcFunc['db_free_result']($request);

	return $return;
}

// Function to get a block's display/show information.
function getShowInfo($block_id = null, $display = null, $custom = null)
{
	global $smcFunc, $context, $modSettings;
	static $variables;

	// Do we have the display info?
	if ($display === null || $custom === null)
	{
		// Make sure that its an integer.
		$block_id = (int) $block_id;

		// We need an ID.
		if (empty($block_id))
			return false;

		// Get the info.
		$result = $smcFunc['db_query']('','
			SELECT display, display_custom
			FROM {db_prefix}sp_blocks
			WHERE id_block = {int:id_block}
			LIMIT 1',
			array(
				'id_block' => $block_id,
			)
		);
		list ($display, $custom) = $smcFunc['db_fetch_row']($result);
		$smcFunc['db_free_result']($result);
	}

	if (!empty($_GET['page']) && (empty($context['current_action']) || $context['current_action'] == 'portal'))
		$page_info = sportal_get_pages($_GET['page'], true, true);

	// Some variables for ease.
	$action = !empty($context['current_action']) ? $context['current_action'] : '';
	$sub_action = !empty($context['current_subaction']) ? $context['current_subaction'] : '';
	$board = !empty($context['current_board']) ? 'b' . $context['current_board'] : '';
	$topic = !empty($context['current_topic']) ? 't' . $context['current_topic'] : '';
	$page = !empty($page_info['id']) ? 'p' . $page_info['id'] : '';
	$portal = (empty($action) && empty($sub_action) && empty($board) && empty($topic) && SMF != 'SSI' && $modSettings['sp_portal_mode'] == 1) || !empty($context['standalone']) ? true : false;

	// Will hopefully get larger in the future.
	$portal_actions = array(
		'articles' => true,
		'start' => true,
		'theme' => true,
		'PHPSESSID' => true,
		'wwwRedirect' => true,
		'www' => true,
		'variant' => true,
		'language' => true,
	);

	// Set some action exceptions.
	$exceptions = array(
		'post' => array('announce', 'editpoll', 'emailuser', 'post2', 'sendtopic'),
		'register' => array('activate', 'coppa'),
		'forum' => array('collapse'),
		'admin' => array('credits', 'theme', 'viewquery', 'viewsmfile'),
		'moderate' => array('groups'),
		'login' => array('reminder'),
		'profile' => array('trackip', 'viewprofile'),
	);

	// Still, we might not be in portal!
	if (!empty($_GET) && empty($context['standalone']))
		foreach ($_GET as $key => $value)
		{
			if (preg_match('~^news\d+$~', $key))
				continue;

			if (!isset($portal_actions[$key]))
				$portal = false;
			elseif (is_array($portal_actions[$key]) && !in_array($value, $portal_actions[$key]))
				$portal = false;
		}

	// Set the action to more known one.
	foreach ($exceptions as $key => $exception)
		if (in_array($action, $exception))
			$action = $key;

	// Take care of custom actions.
	$special = array();
	$exclude = array();
	if (!empty($custom))
	{
		// Complex display options first...
		if (substr($custom, 0, 4) === '$php')
		{
			if (!isset($variables))
			{
				$variables = array(
					'{$action}' => "'$action'",
					'{$sa}' => "'$sub_action'",
					'{$board}' => "'$board'",
					'{$topic}' => "'$topic'",
					'{$page}' => "'$page'",
					'{$portal}' => $portal,
				);
			}

			return @eval(str_replace(array_keys($variables), array_values($variables), un_htmlspecialchars(substr($custom, 4))) . ';');
		}

		$custom = explode(',', $custom);

		// This is special...
		foreach ($custom as $key => $value)
		{
			$name = '';
			$item = '';

			// Is this a weird action?
			if ($value[0] == '~')
			{
				@list($name, $item) = explode('|', substr($value, 1));

				if (empty($item))
					$special[$name] = true;
				else
					$special[$name][] = $item;
			}

			// Might be excluding something!
			elseif ($value[0] == '-')
			{
				// We still may have weird things...
				if ($value[1] == '~')
				{
					@list($name, $item) = explode('|', substr($value, 2));

					if (empty($item))
						$exclude['special'][$name] = true;
					else
						$exclude['special'][$name][] = $item;
				}
				else
					$exclude['regular'][] = substr($value, 1);
			}
		}

		// Add what we have to main variable.
		if (!empty($display))
			$display = $display . ',' . implode(',', $custom);
		else
			$display = $custom;
	}

	// We don't want to show it on this action/page/board?
	if (!empty($exclude['regular']) && count(array_intersect(array($action, $page, $board), $exclude['regular'])) > 0)
		return false;

	// Maybe we don't want to show it in somewhere special.
	if (!empty($exclude['special']))
		foreach ($exclude['special'] as $key => $value)
			if (isset($_GET[$key]))
				if (is_array($value) && !in_array($_GET[$key], $value))
					continue;
				else
					return false;

	// If no display info and/or integration disabled and we are on portal; show it!
	if ((empty($display) || empty($modSettings['sp_enableIntegration'])) && $portal)
		return true;
	// No display info and/or integration disabled and no portal; no need...
	elseif (empty($display) || empty($modSettings['sp_enableIntegration']))
		return false;
	// Get ready for real action if you haven't yet.
	elseif (!is_array($display))
		$display = explode(',', $display);

	// Did we disable all blocks for this action?
	if (!empty($modSettings['sp_' . $action . 'IntegrationHide']))
		return false;
	// If we will display show the block.
	elseif (in_array('all', $display))
		return true;
	// If we are on portal, show portal blocks; if we are on forum, show forum blocks.
	elseif (($portal && (in_array('portal', $display) || in_array('sportal', $display))) || (!$portal && in_array('sforum', $display)))
		return true;
	elseif (!empty($board) && (in_array('allboard', $display) || in_array($board, $display)))
		return true;
	elseif (!empty($action) && $action != 'portal' && (in_array('allaction', $display) || in_array($action, $display)))
		return true;
	elseif (!empty($page) && (in_array('allpages', $display) || in_array($page, $display)))
		return true;
	elseif (empty($action) && empty($board) && empty($_GET['page']) && !$portal && ($modSettings['sp_portal_mode'] == 2 || $modSettings['sp_portal_mode'] == 3) && in_array('forum', $display))
		return true; 

	// For mods using weird urls...
	foreach ($special as $key => $value)
		if (isset($_GET[$key]))
			if (is_array($value) && !in_array($_GET[$key], $value))
				continue;
			else
				return true;

	// Ummm, no block!
	return false;
}

function sp_allowed_to($type, $id, $set = null, $allowed = null, $denied = null)
{
	global $smcFunc, $user_info;
	static $cache, $types;

	if (!isset($types))
	{
		$types = array(
			'block' => array(
				'table' => 'blocks',
				'id' => 'id_block',
			),
			'page' => array(
				'table' => 'pages',
				'id' => 'id_page',
			),
			'shoutbox' => array(
				'table' => 'shoutboxes',
				'id' => 'id_shoutbox',
			),
		);
	}

	if (empty($id) || empty($type) || !isset($types[$type]))
		return false;

	if (!isset($set, $allowed, $denied))
	{
		$request = $smcFunc['db_query']('','
			SELECT permission_set, groups_allowed, groups_denied
			FROM {db_prefix}sp_{raw:table}
			WHERE {raw:id} = {int:id_item}
			LIMIT {int:limit}',
			array(
				'table' => $types[$type]['table'],
				'id' => $types[$type]['id'],
				'id_item' => $id,
				'limit' => 1,
			)
		);
		list ($set, $allowed, $denied) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);
	}

	$result = false;
	$cache_name = md5(implode(':', array($set, $allowed, $denied)));

	if (isset($cache[$cache_name]))
		$result = $cache[$cache_name];
	else
	{
		switch ($set)
		{
			case 3:
				$result = true;
				break;
			case 2:
				$result = empty($user_info['is_guest']);
				break;
			case 1:
				$result = !empty($user_info['is_guest']);
				break;
			case 0:
				if (!empty($denied) && count(array_intersect($user_info['groups'], explode(',', $denied))) > 0)
					$result = false;
				elseif (!empty($allowed) && count(array_intersect($user_info['groups'], explode(',', $allowed))) > 0)
					$result = true;
				break;
			default:
				break;
		}

		$cache[$cache_name] = $result;
	}

	return $result;
}

function BookOfSinan()
{
	global $context, $scripturl;

	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"', $context['right_to_left'] ? ' dir="rtl"' : '', '>
	<head>
		<title>The Book of Sinan, ', @$_GET['verse'] == '3:17' ? '3:17' : '1:15', '</title>
		<style type="text/css">
			em
			{
				font-size: 1.3em;
				line-height: 0;
			}
		</style>
		<meta name="robots" content="noindex" />
	</head>
	<body style="background-color: #161F4E; color: #FFFFFF; font-style: italic; font-family: serif;">
		<div style="margin-top: 12%; font-size: 1.1em; line-height: 1.4; text-align: center;">';
	if (@$_GET['verse'] == '3:17')
		echo '
			...And suddenly the <em id="dream" name="Blue Dream">dream</em> was over. Whether this was an <em>end</em> or a new <em>start</em>, however, was a <em id="mystery" name="MysteriousGate">mystery</em> to all...
		</div>';
	else
		echo '
			...It all started with a noob. He united <em>simplicity</em> with <em>power</em>, and achieved the <em>ultimate power of simplicity</em>.<br />This power lead him to the domination of World of <em>Blocks</em>...
		</div>';
	echo '
		<div style="margin-top: 2ex; font-size: 2em; text-align: right;">';
	if (@$_GET['verse'] == '3:17')
		echo '
			from <span style="font-family: Georgia, serif;"><strong><a href="http://www.bluedream.info/aboutsinan.php" style="color: white; text-decoration: none; cursor: text;">The Book of Sinan</a></strong>, 3:17</span>';
	else
		echo '
			from <span style="font-family: Georgia, serif;"><strong><a href="', $scripturl, '?about:sinan;verse=3:17" style="color: white; text-decoration: none; cursor: text;">The Book of Sinan</a></strong>, 1:15</span>';
	echo '
		</div>
	</body>
</html>';

	obExit(false);
}

function sp_query_string($tourniquet)
{
	global $sportal_version, $context, $modSettings;

	$fix = str_replace('{version}', $sportal_version, '<a href="http://www.simpleportal.net/" target="_blank" class="new_win">SimplePortal {version} &copy; 2008-2012, SimplePortal</a>');

	if ((SMF == 'SSI' && empty($context['standalone'])) || empty($context['template_layers']) || WIRELESS || empty($modSettings['sp_portal_mode']) || strpos($tourniquet, $fix) !== false)
		return $tourniquet;

	$finds = array(
		', Simple Machines LLC</a>',
		', <a href="http://www.simplemachines.org" title="Simple Machines" target="_blank" class="new_win">Simple Machines</a>',
		'class="copywrite"',
		'class="copyright"',
	);
	$replaces = array(
		', Simple Machines LLC</a><br />' . $fix,
		', <a href="http://www.simplemachines.org" title="Simple Machines" target="_blank" class="new_win">Simple Machines</a><br />' . $fix,
		'class="copywrite" style="line-height: 1em;"',
		'class="copyright" style="line-height: 1.5em;"',
	);

	$tourniquet = str_replace($finds, $replaces, $tourniquet);

	if (strpos($tourniquet, $fix) === false)
	{
		$fix = '<div style="text-align: center; width: 100%; font-size: x-small; margin-bottom: 5px;">' . $fix . '</div></body></html>';
		$tourniquet = preg_replace('~</body>\s*</html>~', $fix, $tourniquet);
	}

	return $tourniquet;
}

/*
This is a simple function that return nothing if the language file exist and english if it not exists
This will help to make it possible to load each time the english language!
*/
function sp_languageSelect($template_name)
{
	global $user_info, $language, $settings, $context;
	global $sourcedir;
	static $already_loaded = array();

	if(isset($already_loaded[$template_name]))
		return $already_loaded[$template_name];

	$lang = isset($user_info['language']) ? $user_info['language'] : $language;

	// Make sure we have $settings - if not we're in trouble and need to find it!
	if (empty($settings['default_theme_dir']))
	{
		require_once($sourcedir . '/ScheduledTasks.php');
		loadEssentialThemeData();
	}

	// For each file open it up and write it out!
	$allTemplatesExists = array();
	foreach (explode('+', $template_name) as $template)
	{
		// Obviously, the current theme is most important to check.
		$attempts = array(
			array($settings['theme_dir'], $template, $lang, $settings['theme_url']),
			array($settings['theme_dir'], $template, $language, $settings['theme_url']),
		);

		// Do we have a base theme to worry about?
		if (isset($settings['base_theme_dir']))
		{
			$attempts[] = array($settings['base_theme_dir'], $template, $lang, $settings['base_theme_url']);
			$attempts[] = array($settings['base_theme_dir'], $template, $language, $settings['base_theme_url']);
		}

		// Fallback on the default theme if necessary.
		$attempts[] = array($settings['default_theme_dir'], $template, $lang, $settings['default_theme_url']);
		$attempts[] = array($settings['default_theme_dir'], $template, $language, $settings['default_theme_url']);

		// Try to find the language file.
		$allTemplatesExists[$template] = false;
		$already_loaded[$template] = 'english';
		foreach ($attempts as $k => $file) {
			if (file_exists($file[0] . '/languages/' . $file[1] . '.' . $file[2] . '.php'))
			{
				$already_loaded[$template] = '';
				$allTemplatesExists[$template] = true;
				break;
			}
		}
	}
	//So all need to be true that it work ;)
	foreach($allTemplatesExists as $exist)
		if(!$exist)
		{
			$already_loaded[$template_name] = 'english';
			return 'english';
		}

	//Everthing is fine, let's go back :D
	$already_loaded[$template_name] = '';
	return '';
}

function sp_loadCalendarData($type, $low_date, $high_date = false)
{
	global $sourcedir;
	static $loaded;

	if(!isset($loaded))
	{
		require_once($sourcedir . '/Subs-Calendar.php');

		$loaded = array(
			'getEvents' => 'getEventRange',
			'getBirthdays' => 'getBirthdayRange',
			'getHolidays' => 'getHolidayRange',
		);
	}

	if (!empty($loaded[$type]))
		return $loaded[$type]($low_date, ($high_date === false ? $low_date : $high_date));
	else
		return array();
}

// This is a small script to load colors for SPortal.
function sp_loadColors($users = array())
{
	global $color_profile, $smcFunc, $scripturl, $modSettings;

	// This is for later, if you like to disable colors ;)
	if (!empty($modSettings['sp_disableColor']))
		return false;

	// Can't just look for no users. :P
	if (empty($users))
		return false;

	// MemberColorLink compatible, cache more data, handle also some special member color link colors
	if (!empty($modSettings['MemberColorLinkInstalled']))
	{
		$colorData = load_onlineColors($users);
		
		// This happen only on not existing Members... but given ids...
		if(empty($colorData))
			return false;
 
		$loaded_ids = array_keys($colorData);

		foreach($loaded_ids as $id)
		{
			if (!empty($id) && !isset($color_profile[$id]['link']))
			{
				$color_profile[$id]['link'] = $colorData[$id]['colored_link'];
				$color_profile[$id]['colored_name'] = $colorData[$id]['colored_name'];
			}
		}
		return empty($loaded_ids) ? false : $loaded_ids;
	}

	// Make sure it's an array.
	$users = !is_array($users) ? array($users) : array_unique($users);

	//Check up the array :)
	foreach($users as $k => $u)
	{
		$u = (int) $u;

		if (empty($u))
			unset($users[$k]);
		else
			$users[$k] = $u;
	}

	$loaded_ids = array();
	// Is this a totally new variable?
	if (empty($color_profile))
		$color_profile = array();
	// Otherwise, we will need to do some reformating of the old data.
	else
	{
		foreach ($users as $k => $u)
			if (isset($color_profile[$u]))
			{
				$loaded_ids[] = $u;
				unset($users[$k]);
			}
	}

	// Make sure that we have some users.
	if (empty($users))
		return empty($loaded_ids) ? false : $loaded_ids;

	// Correct array pointer for the user
	reset($users);
	// Load the data.
	$request = $smcFunc['db_query']('','
		SELECT
			mem.id_member, mem.member_name, mem.real_name, mem.id_group,
			mg.online_color AS member_group_color, pg.online_color AS post_group_color
		FROM {db_prefix}members AS mem
			LEFT JOIN {db_prefix}membergroups AS pg ON (pg.id_group = mem.id_post_group)
			LEFT JOIN {db_prefix}membergroups AS mg ON (mg.id_group = mem.id_group)
		WHERE mem.id_member '.((count($users) == 1) ? '= {int:current}' : 'IN ({array_int:users})'),
		array(
			'users'	=> $users,
			'current' => (int) current($users),
		)
	);

	// Go through each of the users.
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$loaded_ids[] = $row['id_member'];
		$color_profile[$row['id_member']] = $row;
		$onlineColor = !empty($row['member_group_color']) ? $row['member_group_color'] : $row['post_group_color'];
		$color_profile[$row['id_member']]['color'] = $onlineColor;
		$color_profile[$row['id_member']]['link'] = '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '"' . (!empty($onlineColor) ? ' style="color: ' . $onlineColor . ';"' : '') . '>' . $row['real_name'] . '</a>';
		$color_profile[$row['id_member']]['colored_name'] = (!empty($onlineColor) ? '<span style="color: ' . $onlineColor . ';">' : '' ) . $row['real_name'] . (!empty($onlineColor) ? '</span>' : '');
	}
	$smcFunc['db_free_result']($request);

	// Return the necessary data.
	return empty($loaded_ids) ? false : $loaded_ids;
}

function sp_embed_image($name, $alt = '', $width = null, $height = null, $title = true, $id = null)
{
	global $modSettings, $settings, $txt;
	static $default_alt, $randomizer, $randomizer2;

	if (!isset($default_alt))
	{
		$default_alt = array(
			'dot' => $txt['sp-dot'],
			'stars' => $txt['sp-star'],
			'arrow' => $txt['sp-arrow'],
			'modify' => $txt['modify'],
			'delete' => $txt['delete'],
			'delete_small' => $txt['delete'],
			'history' => $txt['sp_shoutbox_history'],
			'refresh' => $txt['sp_shoutbox_refresh'],
			'smiley' => $txt['sp_shoutbox_smiley'],
			'style' => $txt['sp_shoutbox_style'],
			'bin' => $txt['sp_shoutbox_prune'],
			'move' => $txt['sp_move'],
		);
	}

	if (!isset($randomizer)	|| $randomizer > 7)
		$randomizer	= 0;
	$randomizer++;

	if (empty($alt) && isset($default_alt[$name]))
		$alt = $default_alt[$name];

	if ($title === true)
		$title = !empty($alt) ? $alt : '';

	if (empty($alt))
		$alt = $name;

	if (in_array($name, array('dot', 'star')) && empty($modSettings['sp_disable_random_bullets']))
		$name .= $randomizer;

	$image = '<img src="' . $settings['sp_images_url'] . '/' . $name . '.png" alt="' . $alt . '"' . (!empty($title) ? ' title="' . $title . '"' : '') . (!empty($width) ? ' width="' . $width . '"' : '') . (!empty($height) ? ' height="' . $height . '"' : '') . (!empty($id) ? ' id="' . $id . '"' : '') . ' />';

	return $image;
}

function sportal_parse_style($action, $setting = '', $process = false)
{
	global $smcFunc;
	static $process_cache;

	if ($action == 'implode')
	{
		$style = '';
		$style_parameters = array(
			'title_default_class',
			'title_custom_class',
			'title_custom_style',
			'body_default_class',
			'body_custom_class',
			'body_custom_style',
			'no_title',
			'no_body',
		);

		foreach ($style_parameters as $parameter)
			if (isset($_POST[$parameter]))
				$style .= $parameter . '~' . $smcFunc['htmlspecialchars']($smcFunc['htmltrim']($_POST[$parameter]), ENT_QUOTES) . '|';
			else
				$style .= $parameter . '~|';

		if (!empty($style))
			$style = substr($style, 0, -1);
	}
	elseif ($action == 'explode')
	{
		if (!empty($setting))
		{
			$temp = explode('|', $setting);
			$style = array();

			foreach ($temp as $item)
			{
				list ($key, $value) = explode('~', $item);
				$style[$key] = $value;
			}
		}
		else
		{
			$style = array(
				'title_default_class' => 'catbg',
				'title_custom_class' => '',
				'title_custom_style' => '',
				'body_default_class' => 'windowbg',
				'body_custom_class' => '',
				'body_custom_style' => '',
				'no_title' => false,
				'no_body' => false,
			);
		}

		if ($process && !isset($process_cache[$setting]))
		{
			if (empty($style['no_title']))
			{
				$style['title']['class'] = $style['title_default_class'];

				if (!empty($style['title_custom_class']))
					$style['title']['class'] .= ' ' . $style['title_custom_class'];

				$style['title']['style'] = $style['title_custom_style'];
			}

			if (empty($style['no_body']))
				$style['body']['class'] = $style['body_default_class'];
			else
				$style['body']['class'] = '';

			if (!empty($style['body_custom_class']))
				$style['body']['class'] .= ' ' . $style['body_custom_class'];

			$style['body']['style'] = $style['body_custom_style'];

			$process_cache[$setting] = $style;
		}
		elseif ($process)
			$style = $process_cache[$setting];
	}

	return $style;
}

function sportal_get_pages($page_id = null, $active = false, $allowed = false)
{
	global $smcFunc;

	$query = array();
	$parameters = array();

	if (!empty($page_id) && is_numeric($page_id))
	{
		$query[] = 'id_page = {int:page_id}';
		$parameters['page_id'] = (int) $page_id;
	}
	elseif (!empty($page_id))
	{
		$query[] = 'namespace = {string:namespace}';
		$parameters['namespace'] = $page_id;
	}
	if (!empty($active))
	{
		$query[] = 'status = {int:status}';
		$parameters['status'] = 1;
	}

	$request = $smcFunc['db_query']('','
		SELECT
			id_page, namespace, title, body, type, permission_set,
			groups_allowed, groups_denied, views, style, status
		FROM {db_prefix}sp_pages' . (!empty($query) ? '
		WHERE ' . implode(' AND ', $query) : '') . '
		ORDER BY title',
		$parameters
	);
	$return = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		if (!empty($allowed) && !sp_allowed_to('page', $row['id_page'], $row['permission_set'], $row['groups_allowed'], $row['groups_denied']))
			continue;

		$return[$row['id_page']] = array(
			'id' => $row['id_page'],
			'page_id' => $row['namespace'],
			'title' => $row['title'],
			'body' => $row['body'],
			'type' => $row['type'],
			'permission_set' => $row['permission_set'],
			'groups_allowed' => $row['groups_allowed'] !== '' ? explode(',', $row['groups_allowed']) : array(), 
			'groups_denied' => $row['groups_denied'] !== '' ? explode(',', $row['groups_denied']) : array(), 
			'views' => $row['views'],
			'style' => $row['style'],
			'status' => $row['status'],
		);
	}
	$smcFunc['db_free_result']($request);

	return !empty($page_id) ? current($return) : $return;
}

function sportal_parse_page($body, $type)
{
	if (strtolower($body) === 'el psy congroo')
		echo 'Steins;Gate';
	elseif ($type == 'bbc')
		echo parse_bbc($body);
	elseif ($type == 'html')
		echo un_htmlspecialchars($body);
	elseif ($type == 'php')
	{
		$body = trim(un_htmlspecialchars($body));
		$body = trim($body, '<?php');
		$body = trim($body, '?>');
		eval($body);
	}
}

function sportal_get_shoutbox($shoutbox_id = null, $active = false, $allowed = false)
{
	global $smcFunc;

	$query = array();
	$parameters = array();

	if ($shoutbox_id !== null)
	{
		$query[] = 'id_shoutbox = {int:shoutbox_id}';
		$parameters['shoutbox_id'] = $shoutbox_id;
	}
	if (!empty($active))
	{
		$query[] = 'status = {int:status}';
		$parameters['status'] = 1;
	}

	$request = $smcFunc['db_query']('','
		SELECT
			id_shoutbox, name, permission_set, groups_allowed, groups_denied,
			moderator_groups, warning, allowed_bbc, height, num_show, num_max,
			refresh, reverse, caching, status, num_shouts, last_update
		FROM {db_prefix}sp_shoutboxes' . (!empty($query) ? '
		WHERE ' . implode(' AND ', $query) : '') . '
		ORDER BY name',
		$parameters
	);
	$return = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		if (!empty($allowed) && !sp_allowed_to('shoutbox', $row['id_shoutbox'], $row['permission_set'], $row['groups_allowed'], $row['groups_denied']))
			continue;

		$return[$row['id_shoutbox']] = array(
			'id' => $row['id_shoutbox'],
			'name' => $row['name'],
			'permission_set' => $row['permission_set'],
			'groups_allowed' => $row['groups_allowed'] !== '' ? explode(',', $row['groups_allowed']) : array(), 
			'groups_denied' => $row['groups_denied'] !== '' ? explode(',', $row['groups_denied']) : array(), 
			'moderator_groups' => $row['moderator_groups'] !== '' ? explode(',', $row['moderator_groups']) : array(),
			'warning' => $row['warning'],
			'allowed_bbc' => explode(',', $row['allowed_bbc']),
			'height' => $row['height'],
			'num_show' => $row['num_show'],
			'num_max' => $row['num_max'],
			'refresh' => $row['refresh'],
			'reverse' => $row['reverse'],
			'caching' => $row['caching'],
			'status' => $row['status'],
			'num_shouts' => $row['num_shouts'],
			'last_update' => $row['last_update'],
		);
	}
	$smcFunc['db_free_result']($request);

	return !empty($shoutbox_id) ? current($return) : $return;
}

function sportal_get_shouts($shoutbox, $parameters)
{
	global $smcFunc, $scripturl, $context, $user_info, $modSettings, $options, $txt;

	$shoutbox = !empty($shoutbox) ? (int) $shoutbox : 0;
	$start = !empty($parameters['start']) ? (int) $parameters['start'] : 0;
	$limit = !empty($parameters['limit']) ? (int) $parameters['limit'] : 20;
	$bbc = !empty($parameters['bbc']) ? $parameters['bbc'] : array();
	$reverse = !empty($parameters['reverse']);
	$cache = !empty($parameters['cache']);
	$can_delete = !empty($parameters['can_moderate']);

	if (!empty($start) || !$cache || ($shouts = cache_get_data('shoutbox_shouts-' . $shoutbox, 240)) === null)
	{
		$request = $smcFunc['db_query']('', '
			SELECT
				sh.id_shout, sh.body, IFNULL(mem.id_member, 0) AS id_member,
				IFNULL(mem.real_name, sh.member_name) AS member_name, sh.log_time,
				mg.online_color AS member_group_color, pg.online_color AS post_group_color
			FROM {db_prefix}sp_shouts AS sh
				LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = sh.id_member)
				LEFT JOIN {db_prefix}membergroups AS pg ON (pg.id_group = mem.id_post_group)
				LEFT JOIN {db_prefix}membergroups AS mg ON (mg.id_group = mem.id_group)
			WHERE sh.id_shoutbox = {int:id_shoutbox}
			ORDER BY sh.id_shout DESC
			LIMIT {int:start}, {int:limit}',
			array(
				'id_shoutbox' => $shoutbox,
				'start' => $start,
				'limit' => $limit,
			)
		);
		$shouts = array();
		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			// Disable the aeva mod for the shoutbox.
			$context['aeva_disable'] = true;
			$online_color = !empty($row['member_group_color']) ? $row['member_group_color'] : $row['post_group_color'];
			$shouts[$row['id_shout']] = array(
				'id' => $row['id_shout'],
				'author' => array(
					'id' => $row['id_member'],
					'name' => $row['member_name'],
					'link' => $row['id_member'] ? ('<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '" title="' . $txt['on'] . ' ' . strip_tags(timeformat($row['log_time'])) . '"' . (!empty($online_color) ? ' style="color: ' . $online_color . ';"' : '') . '>' . $row['member_name'] . '</a>') : $row['member_name'],
					'color' => $online_color,
				),
				'time' => $row['log_time'],
				'text' => parse_bbc($row['body'], true, '', $bbc),
			);
		}
		$smcFunc['db_free_result']($request);

		if (empty($start) && $cache)
			cache_put_data('shoutbox_shouts-' . $shoutbox, $shouts, 240);
	}

	foreach ($shouts as $shout)
	{
		if (preg_match('~^@(.+?): ~' . ($context['utf8'] ? 'u' : ''), $shout['text'], $target) && $smcFunc['strtolower']($target[1]) !== $smcFunc['strtolower']($user_info['name']) && $shout['author']['id'] != $user_info['id'] && !$user_info['is_admin'])
		{
			unset($shouts[$shout['id']]);
			continue;
		}

		$shouts[$shout['id']] += array(
			'is_me' => preg_match('~^<div\sclass="meaction">\* ' . preg_quote($shout['author']['name'], '~') . '.+</div>$~', $shout['text']) != 0,
			'delete_link' => $can_delete ? '<a href="' . $scripturl . '?action=portal;sa=shoutbox;shoutbox_id=' . $shoutbox . ';delete=' . $shout['id'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '">' . sp_embed_image('delete_small') . '</a> ' : '',
			'delete_link_js' => $can_delete ? '<a href="' . $scripturl . '?action=portal;sa=shoutbox;shoutbox_id=' . $shoutbox . ';delete=' . $shout['id'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '" onclick="sp_delete_shout(' . $shoutbox . ', ' . $shout['id'] . ', \'' . $context['session_var'] . '\', \'' . $context['session_id'] . '\'); return false;">' . sp_embed_image('delete_small') . '</a> ' : '',
		);

		$shouts[$shout['id']]['text'] = str_replace(':jade:', '<img src="http://www.simpleportal.net/sp/cheerleader.gif" alt="Jade!" />', $shouts[$shout['id']]['text']);
		$shouts[$shout['id']]['time'] = timeformat($shouts[$shout['id']]['time']);
		$shouts[$shout['id']]['text'] = preg_replace('~(</?)div([^<]*>)~', '$1span$2', $shouts[$shout['id']]['text']);
		$shouts[$shout['id']]['text'] = preg_replace('~<a([^>]+>)([^<]+)</a>~', '<a$1' . $txt['sp_link'] . '</a>', $shouts[$shout['id']]['text']);
		$shouts[$shout['id']]['text'] = censorText($shouts[$shout['id']]['text']);

		if (!empty($modSettings['enable_buddylist']) && !empty($options['posts_apply_ignore_list']) && in_array($shout['author']['id'], $context['user']['ignoreusers']))
			$shouts[$shout['id']]['text'] = '<a href="#toggle" id="ignored_shout_link_' . $shout['id'] . '" onclick="sp_show_ignored_shout(' . $shout['id'] . '); return false;">[' . $txt['sp_shoutbox_show_ignored'] . ']</a><span id="ignored_shout_' . $shout['id'] . '" style="display: none;">' . $shouts[$shout['id']]['text'] . '</span>';
	}

	if ($reverse)
		$shouts = array_reverse($shouts);

	return $shouts;
}

function sportal_create_shout($shoutbox, $shout)
{
	global $smcFunc, $user_info;

	if ($user_info['is_guest'])
		return false;

	if (empty($shoutbox))
		return false;

	if (trim(strip_tags(parse_bbc($shout, false), '<img>')) === '')
		return false;

	$smcFunc['db_insert']('',
		'{db_prefix}sp_shouts',
		array(
			'id_shoutbox' => 'int',
			'id_member' => 'int',
			'member_name' => 'string',
			'log_time' => 'int',
			'body' => 'string',
		),
		array(
			$shoutbox['id'],
			$user_info['id'],
			$user_info['name'],
			time(),
			$shout,
		),
		array('id_shout')
	);

	$shoutbox['num_shouts']++;
	if ($shoutbox['num_shouts'] > $shoutbox['num_max'])
	{
		$request = $smcFunc['db_query']('','
			SELECT id_shout
			FROM {db_prefix}sp_shouts
			WHERE id_shoutbox = {int:shoutbox}
			ORDER BY log_time
			LIMIT {int:limit}',
			array(
				'shoutbox' => $shoutbox['id'],
				'limit' => $shoutbox['num_shouts'] - $shoutbox['num_max'],
			)
		);
		$old_shouts = array();
		while ($row = $smcFunc['db_fetch_assoc']($request))
			$old_shouts[] = $row['id_shout'];
		$smcFunc['db_free_result']($request);

		sportal_delete_shout($shoutbox['id'], $old_shouts, true);
	}
	else
		sportal_update_shoutbox($shoutbox['id'], true);
}

function sportal_delete_shout($shoutbox_id, $shouts, $prune = false)
{
	global $smcFunc;

	if (!is_array($shouts))
		$shouts = array($shouts);

	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}sp_shouts
		WHERE id_shout IN ({array_int:shouts})',
		array(
			'shouts' => $shouts,
		)
	);

	sportal_update_shoutbox($shoutbox_id, $prune ? count($shouts) - 1 : count($shouts));
}

function sportal_update_shoutbox($shoutbox_id, $num_shouts = 0)
{
	global $smcFunc;

	$smcFunc['db_query']('', '
		UPDATE {db_prefix}sp_shoutboxes
		SET last_update = {int:time}' . ($num_shouts === 0 ? '' : ',
			num_shouts = {raw:shouts}') . '
		WHERE id_shoutbox = {int:shoutbox}',
		array(
			'shoutbox' => $shoutbox_id,
			'time' => time(),
			'shouts' => $num_shouts === true ? 'num_shouts + 1' : 'num_shouts - ' . $num_shouts,
		)
	);

	cache_put_data('shoutbox_shouts-' . $shoutbox_id, null, 240);
}

function sp_prevent_flood($type, $fatal = true)
{
	global $smcFunc, $modSettings, $user_info, $txt;

	$limits = array(
		'spsbp' => 2,
	);

	if (!allowedTo('admin_forum'))
		$time_limit = isset($limits[$type]) ? $limits[$type] : $modSettings['spamWaitTime'];
	else
		$time_limit = 2;

	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}log_floodcontrol
		WHERE log_time < {int:log_time}
			AND log_type = {string:log_type}',
		array(
			'log_time' => time() - $time_limit,
			'log_type' => $type,
		)
	);

	$smcFunc['db_insert']('replace',
		'{db_prefix}log_floodcontrol',
		array('ip' => 'string-16', 'log_time' => 'int', 'log_type' => 'string'),
		array($user_info['ip'], time(), $type),
		array('ip', 'log_type')
	);

	if ($smcFunc['db_affected_rows']() != 1)
	{
		if ($fatal)
			fatal_lang_error('error_sp_flood_' . $type, false, array($time_limit));
		else
			return isset($txt['error_sp_flood_' . $type]) ? sprintf($txt['error_sp_flood_' . $type], $time_limit) : true;
	}

	return false;
}

?>