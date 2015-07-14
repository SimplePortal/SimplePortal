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

function sportal_init($standalone = false)
{
	global $context, $sourcedir, $scripturl, $modSettings, $txt;
	global $settings, $boarddir, $maintenance, $sportal_version;

	$sportal_version = '2.4';

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

		if (!empty($context['linktree']) && $modSettings['sp_portal_mode'] == 1)
			foreach ($context['linktree'] as $key => $tree)
				if (strpos($tree['url'], '#c') !== false && strpos($tree['url'], 'action=forum#c') === false)
					$context['linktree'][$key]['url'] = str_replace('#c', '?action=forum#c', $tree['url']);
	}
	else
		$_GET['action'] = 'portal';

	sportal_init_headers();
	sportal_load_permissions();
	sportal_load_blocks();

	$context['standalone'] = $standalone;
	$context['SPortal']['core_compat'] = $settings['name'] == 'Core Theme';
	$context['SPortal']['on_portal'] = sportal_process_visibility('portal');

	if (!empty($context['template_layers']) && !in_array('portal', $context['template_layers']))
		$context['template_layers'][] = 'portal';
}

/**
 * Deals with the initialization of SimplePortal headers.
 */
function sportal_init_headers()
{
	global $context, $scripturl, $settings, $modSettings;
	static $initialized;

	if (!empty($initialized))
		return;

	$safe_scripturl = $scripturl;
	$current_request = empty($_SERVER['HTTP_HOST']) ? $_SERVER['SERVER_NAME'] : $_SERVER['HTTP_HOST'];

	if (strpos($scripturl, 'www.') !== false && strpos($current_request, 'www.') === false)
		$safe_scripturl = str_replace('://www.', '://', $scripturl);
	elseif (strpos($scripturl, 'www.') === false && strpos($current_request, 'www.') !== false)
		$safe_scripturl = str_replace('://', '://www.', $scripturl);

	$context['html_headers'] .= '
	<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/portal.js?24"></script>
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		var sp_images_url = "' . $settings['sp_images_url'] . '";
		var sp_script_url = "' . $safe_scripturl . '";
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
		if (empty($board) && empty($topic) && empty($_GET['page']) && empty($_GET['article']) && empty($_GET['category']) && $modSettings['sp_portal_mode'] == 1)
		{
			require_once($sourcedir . '/PortalMain.php');
			return 'sportal_main';
		}
		elseif (empty($board) && empty($topic) && !empty($_GET['page']))
		{
			require_once($sourcedir . '/PortalPages.php');
			return 'sportal_page';
		}
		elseif (empty($board) && empty($topic) && !empty($_GET['article']))
		{
			require_once($sourcedir . '/PortalArticles.php');
			return 'sportal_article';
		}
		elseif (empty($board) && empty($topic) && !empty($_GET['category']))
		{
			require_once($sourcedir . '/PortalCategories.php');
			return 'sportal_category';
		}
	}

	return false;
}

function sportal_load_permissions()
{
	global $context, $user_info;

	$profiles = sportal_get_profiles(null, 1);
	$allowed = array();

	foreach ($profiles as $profile)
	{
		$result = false;
		if (!empty($profile['groups_denied']) && count(array_intersect($user_info['groups'], $profile['groups_denied'])) > 0)
			$result = false;
		elseif (!empty($profile['groups_allowed']) && count(array_intersect($user_info['groups'], $profile['groups_allowed'])) > 0)
			$result = true;

		if ($result)
			$allowed[] = $profile['id'];
	}

	$context['SPortal']['permissions'] = array(
		'profiles' => $allowed,
		'query' => empty($allowed) ? '0=1' : 'FIND_IN_SET(%s, \'' . implode(',', $allowed) . '\')',
	);
}

function sportal_load_blocks()
{
	global $context, $modSettings, $options;

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

		$block['style'] = sportal_select_style($block['styles']);

		$context['SPortal']['sides'][$block['column']]['last'] = $block['id'];
		$context['SPortal']['blocks'][$block['column']][] = $block;
	}

	foreach($context['SPortal']['sides'] as $side)
	{
		if (empty($context['SPortal']['blocks'][$side['id']]))
			$context['SPortal']['sides'][$side['id']]['active'] = false;

		$context['SPortal']['sides'][$side['id']]['collapsed'] = $context['user']['is_guest'] ? !empty($_COOKIE['sp_' . $side['name']]) : !empty($options['sp_' . $side['name']]);
	}
}

/**
 * This function, returns all of the information about particular blocks.
 *
 * @param int $column_id
 * @param int $block_id
 * @param boolean $state
 * @param boolean $show
 * @param boolean $permission
 */
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
	if (!empty($permission))
		$query[] = sprintf($context['SPortal']['permissions']['query'], 'spb.permissions');
	if (!empty($state))
	{
		$query[] = 'spb.state = {int:state}';
		$parameters['state'] = 1;
	}

	$request = $smcFunc['db_query']('','
		SELECT
			spb.id_block, spb.label, spb.type, spb.col, spb.row, spb.permissions, spb.styles,
			spb.visibility, spb.state, spb.force_view, spp.variable, spp.value
		FROM {db_prefix}sp_blocks AS spb
			LEFT JOIN {db_prefix}sp_parameters AS spp ON (spp.id_block = spb.id_block)' . (!empty($query) ? '
		WHERE ' . implode(' AND ', $query) : '') . '
		ORDER BY spb.col, spb.row',
		$parameters
	);

	$return = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		if (!empty($show) && !sportal_check_visibility($row['visibility']))
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
				'permissions' => $row['permissions'],
				'styles' => $row['styles'],
				'visibility' => $row['visibility'],
				'state' => empty($row['state']) ? 0 : 1,
				'force_view' => $row['force_view'],
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

function sportal_process_visibility($query)
{
	global $context, $modSettings;

	if (!empty($_GET['page']) && (empty($context['current_action']) || $context['current_action'] == 'portal'))
		$page_info = sportal_get_pages($_GET['page'], true, true);

	if (!empty($_GET['category']) && (empty($context['current_action']) || $context['current_action'] == 'portal'))
		$category_info = sportal_get_categories($_GET['category'], true, true);

	if (!empty($_GET['article']) && (empty($context['current_action']) || $context['current_action'] == 'portal'))
		$article_info = sportal_get_articles($_GET['article'], true, true);

	$action = !empty($context['current_action']) ? $context['current_action'] : '';
	$sub_action = !empty($context['current_subaction']) ? $context['current_subaction'] : '';
	$board = !empty($context['current_board']) ? 'b' . $context['current_board'] : '';
	$topic = !empty($context['current_topic']) ? 't' . $context['current_topic'] : '';
	$page = !empty($page_info['id']) ? 'p' . $page_info['id'] : '';
	$category = !empty($category_info['id']) ? 'c' . $category_info['id'] : '';
	$article = !empty($article_info['id']) ? 'a' . $article_info['id'] : '';
	$portal = (empty($action) && empty($sub_action) && empty($board) && empty($topic) && empty($page) && empty($category) && empty($article) && SMF != 'SSI' && $modSettings['sp_portal_mode'] == 1) || $action == 'portal' || !empty($context['standalone']) ? true : false;
	$forum = (empty($action) && empty($sub_action) && empty($board) && empty($topic) && empty($page) && empty($category) && empty($article) && SMF != 'SSI' && $modSettings['sp_portal_mode'] != 1) || $action == 'forum';

	$portal_actions = array(
		'articles' => true,
		'start' => true,
		'theme' => true,
		'PHPSESSID' => true,
		'wwwRedirect' => true,
		'www' => true,
		'variant' => true,
		'language' => true,
		'action' => array('portal'),
	);

	$exceptions = array(
		'post' => array('announce', 'editpoll', 'emailuser', 'post2', 'sendtopic'),
		'register' => array('activate', 'coppa'),
		'forum' => array('collapse'),
		'admin' => array('credits', 'theme', 'viewquery', 'viewsmfile'),
		'moderate' => array('groups'),
		'login' => array('reminder'),
		'profile' => array('trackip', 'viewprofile'),
	);

	if (!empty($_GET) && empty($context['standalone']))
	{
		foreach ($_GET as $key => $value)
		{
			if (preg_match('~^news\d+$~', $key))
				continue;

			if (!isset($portal_actions[$key]))
				$portal = false;
			elseif (is_array($portal_actions[$key]) && !in_array($value, $portal_actions[$key]))
				$portal = false;
		}
	}

	foreach ($exceptions as $key => $exception)
	{
		if (in_array($action, $exception))
			$action = $key;
	}

	if (($boundary = strpos($query, '$php')) !== false)
	{
		$code = substr($query, $boundary + 4);

		$variables = array(
			'{$action}' => "'$action'",
			'{$sa}' => "'$sub_action'",
			'{$board}' => "'$board'",
			'{$topic}' => "'$topic'",
			'{$page}' => "'$page'",
			'{$category}' => "'$category'",
			'{$article}' => "'$article'",
			'{$portal}' => $portal,
			'{$forum}' => $forum,
		);

		return eval(str_replace(array_keys($variables), array_values($variables), un_htmlspecialchars($code)) . ';');
	}

	if (!empty($query))
		$query = explode(',', $query);
	else
		return false;

	$special = array();
	$exclude = array();

	foreach ($query as $value)
	{
		if (!isset($value[0]))
			continue;

		$name = '';
		$item = '';

		if ($value[0] == '~')
		{
			if (strpos($value, '|') !== false)
				list ($name, $item) = explode('|', substr($value, 1));
			else
				$name = substr($value, 1);

			if (empty($item))
				$special[$name] = true;
			else
				$special[$name][] = $item;
		}
		elseif ($value[0] == '-')
		{
			if ($value[1] == '~')
			{
				if (strpos($value, '|') !== false)
					list ($name, $item) = explode('|', substr($value, 2));
				else
					$name = substr($value, 2);

				if (empty($item))
					$exclude['special'][$name] = true;
				else
					$exclude['special'][$name][] = $item;
			}
			else
				$exclude['regular'][] = substr($value, 1);
		}
	}

	if (!empty($exclude['regular']) && count(array_intersect(array($action, $page, $board, $category, $article), $exclude['regular'])) > 0)
		return false;

	if (!empty($exclude['special']))
	{
		foreach ($exclude['special'] as $key => $value)
		{
			if (isset($_GET[$key]))
			{
				if (is_array($value) && !in_array($_GET[$key], $value))
					continue;
				else
					return false;
			}
		}
	}

	if (!empty($modSettings['sp_' . $action . 'IntegrationHide']))
		return false;
	elseif (in_array('all', $query))
		return true;
	elseif (($portal && in_array('portal', $query)) || ($forum && in_array('forum', $query)))
		return true;
	elseif (!empty($action) && $action != 'portal' && (in_array('allaction', $query) || in_array($action, $query)))
		return true;
	elseif (!empty($board) && (in_array('allboard', $query) || in_array($board, $query)))
		return true;
	elseif (!empty($page) && (in_array('allpage', $query) || in_array($page, $query)))
		return true;
	elseif (!empty($category) && (in_array('allcategory', $query) || in_array($category, $query)))
		return true;
	elseif (!empty($article) && (in_array('allarticle', $query) || in_array($article, $query)))
		return true;

	foreach ($special as $key => $value)
	{
		if (isset($_GET[$key]))
		{
			if (is_array($value) && !in_array($_GET[$key], $value))
				continue;
			else
				return true;
		}
	}

	return false;
}

function sportal_check_visibility($visibility_id)
{
	static $visibilities;

	if (!isset($visibilities))
	{
		$visibilities = sportal_get_profiles(null, 3);
	}

	if (isset($visibilities[$visibility_id]))
	{
		return sportal_process_visibility($visibilities[$visibility_id]['final']);
	}
	else
	{
		return false;
	}
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

	$fix = str_replace('{version}', $sportal_version, '<a href="http://www.simpleportal.net/" target="_blank" class="new_win">SimplePortal {version} &copy; 2008-2014, SimplePortal</a>');

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

/**
 * This is a simple function that return nothing if the language file exist and english if it does not exist
 * This will help to make it possible to load each time the english language!
 *
 * @param string $template_name
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

/**
 * This is a small script to load colors for SPortal.
 *
 * @param array $users
 */
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
			'add' => $txt['sp_add'],
			'items' => $txt['sp_items'],
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

function sportal_select_style($style_id)
{
	static $styles;

	if (!isset($styles))
	{
		$styles = sportal_get_profiles(null, 2);
	}

	if (isset($styles[$style_id]))
	{
		return $styles[$style_id];
	}
	else
	{
		return sportal_parse_style('explode', null, true);
	}
}

function sportal_get_articles($article_id = null, $active = false, $allowed = false, $sort = 'spa.title', $category_id = null)
{
	global $smcFunc, $context, $scripturl, $modSettings, $color_profile;

	$query = array();
	$parameters = array('sort' => $sort);

	if (!empty($article_id) && is_int($article_id))
	{
		$query[] = 'spa.id_article = {int:article_id}';
		$parameters['article_id'] = (int) $article_id;
	}
	elseif (!empty($article_id))
	{
		$query[] = 'spa.namespace = {string:namespace}';
		$parameters['namespace'] = $smcFunc['htmlspecialchars']((string) $article_id, ENT_QUOTES);
	}
	if (!empty($category_id))
	{
		$query[] = 'spa.id_category = {int:category_id}';
		$parameters['category_id'] = (int) $category_id;
	}
	if (!empty($allowed))
	{
		$query[] = sprintf($context['SPortal']['permissions']['query'], 'spa.permissions');
		$query[] = sprintf($context['SPortal']['permissions']['query'], 'spc.permissions');
	}
	if (!empty($active))
	{
		$query[] = 'spa.status = {int:article_status}';
		$parameters['article_status'] = 1;
		$query[] = 'spc.status = {int:category_status}';
		$parameters['category_status'] = 1;
	}

	$request = $smcFunc['db_query']('','
		SELECT
			spa.id_article, spa.id_category, spc.name, spc.namespace AS category_namespace,
			IFNULL(m.id_member, 0) AS id_author, IFNULL(m.real_name, spa.member_name) AS author_name,
			spa.namespace AS article_namespace, spa.title, spa.body, spa.type, spa.date, spa.status,
			spa.permissions AS article_permissions, spc.permissions AS category_permissions,
			spa.styles, spa.views, spa.comments, m.avatar, a.id_attach, a.attachment_type, a.filename
		FROM {db_prefix}sp_articles AS spa
			INNER JOIN {db_prefix}sp_categories AS spc ON (spc.id_category = spa.id_category)
			LEFT JOIN {db_prefix}members AS m ON (m.id_member = spa.id_member)
			LEFT JOIN {db_prefix}attachments AS a ON (a.id_member = m.id_member)' . (!empty($query) ? '
		WHERE ' . implode(' AND ', $query) : '') . '
		ORDER BY {raw:sort}',
		$parameters
	);
	$return = array();
	$member_ids = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		if (!empty($row['id_author']))
			$member_ids[$row['id_author']] = $row['id_author'];

		if ($modSettings['avatar_action_too_large'] == 'option_html_resize' || $modSettings['avatar_action_too_large'] == 'option_js_resize')
		{
			$avatar_width = !empty($modSettings['avatar_max_width_external']) ? ' width="' . $modSettings['avatar_max_width_external'] . '"' : '';
			$avatar_height = !empty($modSettings['avatar_max_height_external']) ? ' height="' . $modSettings['avatar_max_height_external'] . '"' : '';
		}
		else
		{
			$avatar_width = '';
			$avatar_height = '';
		}

		$return[$row['id_article']] = array(
			'id' => $row['id_article'],
			'category' => array(
				'id' => $row['id_category'],
				'category_id' => $row['category_namespace'],
				'name' => $row['name'],
				'href' => $scripturl . '?category=' . $row['category_namespace'],
				'link' => '<a href="' . $scripturl . '?category=' . $row['category_namespace'] . '">' . $row['name'] . '</a>',
				'permissions' => $row['category_permissions'],
			),
			'author' => array(
				'id' => $row['id_author'],
				'name' => $row['author_name'],
				'href' => $scripturl . '?action=profile;u=' . $row['id_author'],
				'link' => $row['id_author'] ? ('<a href="' . $scripturl . '?action=profile;u=' . $row['id_author'] . '">' . $row['author_name'] . '</a>') : $row['author_name'],
				'avatar' => array(
					'name' => $row['avatar'],
					'image' => $row['avatar'] == '' ? ($row['id_attach'] > 0 ? '<img src="' . (empty($row['attachment_type']) ? $scripturl . '?action=dlattach;attach=' . $row['id_attach'] . ';type=avatar' : $modSettings['custom_avatar_url'] . '/' . $row['filename']) . '" alt="" class="avatar" border="0" />' : '') : (stristr($row['avatar'], 'http://') ? '<img src="' . $row['avatar'] . '"' . $avatar_width . $avatar_height . ' alt="" class="avatar" border="0" />' : '<img src="' . $modSettings['avatar_url'] . '/' . htmlspecialchars($row['avatar']) . '" alt="" class="avatar" border="0" />'),
					'href' => $row['avatar'] == '' ? ($row['id_attach'] > 0 ? (empty($row['attachment_type']) ? $scripturl . '?action=dlattach;attach=' . $row['id_attach'] . ';type=avatar' : $modSettings['custom_avatar_url'] . '/' . $row['filename']) : '') : (stristr($row['avatar'], 'http://') ? $row['avatar'] : $modSettings['avatar_url'] . '/' . $row['avatar']),
					'url' => $row['avatar'] == '' ? '' : (stristr($row['avatar'], 'http://') ? $row['avatar'] : $modSettings['avatar_url'] . '/' . $row['avatar'])
				),
			),
			'article_id' => $row['article_namespace'],
			'title' => $row['title'],
			'href' => $scripturl . '?article=' . $row['article_namespace'],
			'link' => '<a href="' . $scripturl . '?article=' . $row['article_namespace'] . '">' . $row['title'] . '</a>',
			'body' => $row['body'],
			'type' => $row['type'],
			'date' => $row['date'],
			'permissions' => $row['article_permissions'],
			'styles' => $row['styles'],
			'views' => $row['views'],
			'comments' => $row['comments'],
			'status' => $row['status'],
		);
	}
	$smcFunc['db_free_result']($request);

	if (!empty($member_ids) && sp_loadColors($member_ids) !== false)
	{
		foreach ($return as $key => $value)
		{
			if (!empty($color_profile[$value['author']['id']]['link']))
				$return[$key]['author']['link'] = $color_profile[$value['author']['id']]['link'];
		}
	}

	return !empty($article_id) ? current($return) : $return;
}

function sportal_get_categories($category_id = null, $active = false, $allowed = false, $sort = 'name')
{
	global $smcFunc, $context, $scripturl;

	$query = array();
	$parameters = array('sort' => $sort);

	if (!empty($category_id) && is_int($category_id))
	{
		$query[] = 'id_category = {int:category_id}';
		$parameters['category_id'] = (int) $category_id;
	}
	elseif (!empty($category_id))
	{
		$query[] = 'namespace = {string:namespace}';
		$parameters['namespace'] = $smcFunc['htmlspecialchars']((string) $category_id, ENT_QUOTES);
	}
	if (!empty($allowed))
		$query[] = sprintf($context['SPortal']['permissions']['query'], 'permissions');
	if (!empty($active))
	{
		$query[] = 'status = {int:status}';
		$parameters['status'] = 1;
	}

	$request = $smcFunc['db_query']('','
		SELECT
			id_category, namespace, name, description,
			permissions, articles, status
		FROM {db_prefix}sp_categories' . (!empty($query) ? '
		WHERE ' . implode(' AND ', $query) : '') . '
		ORDER BY {raw:sort}',
		$parameters
	);
	$return = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$return[$row['id_category']] = array(
			'id' => $row['id_category'],
			'category_id' => $row['namespace'],
			'name' => $row['name'],
			'href' => $scripturl . '?category=' . $row['namespace'],
			'link' => '<a href="' . $scripturl . '?category=' . $row['namespace'] . '">' . $row['name'] . '</a>',
			'description' => $row['description'],
			'permissions' => $row['permissions'],
			'articles' => $row['articles'],
			'status' => $row['status'],
		);
	}
	$smcFunc['db_free_result']($request);

	return !empty($category_id) ? current($return) : $return;
}

function sportal_get_comments($article_id = null)
{
	global $smcFunc, $scripturl, $user_info, $modSettings, $color_profile;

	$request = $smcFunc['db_query']('', '
		SELECT
			spc.id_comment, IFNULL(spc.id_member, 0) AS id_author,
			IFNULL(m.real_name, spc.member_name) AS author_name,
			spc.body, spc.log_time, m.avatar, a.id_attach,
			a.attachment_type, a.filename
		FROM {db_prefix}sp_comments AS spc
			LEFT JOIN {db_prefix}members AS m ON (m.id_member = spc.id_member)
			LEFT JOIN {db_prefix}attachments AS a ON (a.id_member = m.id_member)
		WHERE spc.id_article = {int:article_id}
		ORDER BY spc.id_comment',
		array(
			'article_id' => (int) $article_id,
		)
	);
	$return = array();
	$member_ids = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		if (!empty($row['id_author']))
			$member_ids[$row['id_author']] = $row['id_author'];

		if ($modSettings['avatar_action_too_large'] == 'option_html_resize' || $modSettings['avatar_action_too_large'] == 'option_js_resize')
		{
			$avatar_width = !empty($modSettings['avatar_max_width_external']) ? ' width="' . $modSettings['avatar_max_width_external'] . '"' : '';
			$avatar_height = !empty($modSettings['avatar_max_height_external']) ? ' height="' . $modSettings['avatar_max_height_external'] . '"' : '';
		}
		else
		{
			$avatar_width = '';
			$avatar_height = '';
		}

		$return[$row['id_comment']] = array(
			'id' => $row['id_comment'],
			'body' => parse_bbc($row['body']),
			'time' => timeformat($row['log_time']),
			'author' => array(
				'id' => $row['id_author'],
				'name' => $row['author_name'],
				'href' => $scripturl . '?action=profile;u=' . $row['id_author'],
				'link' => $row['id_author'] ? ('<a href="' . $scripturl . '?action=profile;u=' . $row['id_author'] . '">' . $row['author_name'] . '</a>') : $row['author_name'],
				'avatar' => array(
					'name' => $row['avatar'],
					'image' => $row['avatar'] == '' ? ($row['id_attach'] > 0 ? '<img src="' . (empty($row['attachment_type']) ? $scripturl . '?action=dlattach;attach=' . $row['id_attach'] . ';type=avatar' : $modSettings['custom_avatar_url'] . '/' . $row['filename']) . '" alt="" class="avatar" border="0" />' : '') : (stristr($row['avatar'], 'http://') ? '<img src="' . $row['avatar'] . '"' . $avatar_width . $avatar_height . ' alt="" class="avatar" border="0" />' : '<img src="' . $modSettings['avatar_url'] . '/' . htmlspecialchars($row['avatar']) . '" alt="" class="avatar" border="0" />'),
					'href' => $row['avatar'] == '' ? ($row['id_attach'] > 0 ? (empty($row['attachment_type']) ? $scripturl . '?action=dlattach;attach=' . $row['id_attach'] . ';type=avatar' : $modSettings['custom_avatar_url'] . '/' . $row['filename']) : '') : (stristr($row['avatar'], 'http://') ? $row['avatar'] : $modSettings['avatar_url'] . '/' . $row['avatar']),
					'url' => $row['avatar'] == '' ? '' : (stristr($row['avatar'], 'http://') ? $row['avatar'] : $modSettings['avatar_url'] . '/' . $row['avatar'])
				),
			),
			'can_moderate' => allowedTo('sp_admin') || allowedTo('sp_manage_articles') || (!$user_info['is_guest'] && $user_info['id'] == $row['id_author']),
		);
	}
	$smcFunc['db_free_result']($request);

	if (!empty($member_ids) && sp_loadColors($member_ids) !== false)
	{
		foreach ($return as $key => $value)
		{
			if (!empty($color_profile[$value['author']['id']]['link']))
				$return[$key]['author']['link'] = $color_profile[$value['author']['id']]['link'];
		}
	}

	return $return;
}

function sportal_create_comment($article_id, $body)
{
	global $smcFunc, $user_info;

	$smcFunc['db_insert']('',
		'{db_prefix}sp_comments',
		array(
			'id_article' => 'int',
			'id_member' => 'int',
			'member_name' => 'string',
			'log_time' => 'int',
			'body' => 'string',
		),
		array(
			$article_id,
			$user_info['id'],
			$user_info['name'],
			time(),
			$body,
		),
		array('id_comment')
	);

	sportal_recount_comments($article_id);
}

function sportal_modify_comment($comment_id, $body)
{
	global $smcFunc;

	$smcFunc['db_query']('', '
		UPDATE {db_prefix}sp_comments
		SET body = {text:body}
		WHERE id_comment = {int:comment_id}',
		array(
			'comment_id' => $comment_id,
			'body' => $body,
		)
	);
}

function sportal_delete_comment($article_id, $comment_id)
{
	global $smcFunc;

	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}sp_comments
		WHERE id_comment = {int:comment_id}',
		array(
			'comment_id' => $comment_id,
		)
	);

	sportal_recount_comments($article_id);
}

function sportal_recount_comments($article_id)
{
	global $smcFunc;

	$request = $smcFunc['db_query']('','
		SELECT COUNT(*)
		FROM {db_prefix}sp_comments
		WHERE id_article = {int:article_id}
		LIMIT {int:limit}',
		array(
			'article_id' => $article_id,
			'limit' => 1,
		)
	);
	list ($comments) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	$smcFunc['db_query']('', '
		UPDATE {db_prefix}sp_articles
		SET comments = {int:comments}
		WHERE id_article = {int:article_id}',
		array(
			'article_id' => $article_id,
			'comments' => $comments,
		)
	);
}

function sportal_get_pages($page_id = null, $active = false, $allowed = false, $sort = 'title')
{
	global $smcFunc, $context, $scripturl;

	$query = array();
	$parameters = array('sort' => $sort);

	if (!empty($page_id) && is_int($page_id))
	{
		$query[] = 'id_page = {int:page_id}';
		$parameters['page_id'] = (int) $page_id;
	}
	elseif (!empty($page_id))
	{
		$query[] = 'namespace = {string:namespace}';
		$parameters['namespace'] = $smcFunc['htmlspecialchars']((string) $page_id, ENT_QUOTES);
	}
	if (!empty($allowed))
		$query[] = sprintf($context['SPortal']['permissions']['query'], 'permissions');
	if (!empty($active))
	{
		$query[] = 'status = {int:status}';
		$parameters['status'] = 1;
	}

	$request = $smcFunc['db_query']('','
		SELECT
			id_page, namespace, title, body, type,
			permissions, views, styles, status
		FROM {db_prefix}sp_pages' . (!empty($query) ? '
		WHERE ' . implode(' AND ', $query) : '') . '
		ORDER BY {raw:sort}',
		$parameters
	);
	$return = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$return[$row['id_page']] = array(
			'id' => $row['id_page'],
			'page_id' => $row['namespace'],
			'title' => $row['title'],
			'href' => $scripturl . '?page=' . $row['namespace'],
			'link' => '<a href="' . $scripturl . '?page=' . $row['namespace'] . '">' . $row['title'] . '</a>',
			'body' => $row['body'],
			'type' => $row['type'],
			'permissions' => $row['permissions'],
			'views' => $row['views'],
			'styles' => $row['styles'],
			'status' => $row['status'],
		);
	}
	$smcFunc['db_free_result']($request);

	return !empty($page_id) ? current($return) : $return;
}

function sportal_parse_content($body, $type)
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

function sportal_get_custom_menus($menu_id = null, $sort = 'id_menu')
{
	global $smcFunc, $txt;

	$query = array();
	$parameters = array('sort' => $sort);

	if (isset($menu_id))
	{
		$query[] = 'id_menu = {int:menu_id}';
		$parameters['menu_id'] = (int) $menu_id;
	}

	$request = $smcFunc['db_query']('','
		SELECT id_menu, name
		FROM {db_prefix}sp_custom_menus' . (!empty($query) ? '
		WHERE ' . implode(' AND ', $query) : '') . '
		ORDER BY {raw:sort}',
		$parameters
	);
	$return = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$return[$row['id_menu']] = array(
			'id' => $row['id_menu'],
			'name' => $row['name'],
		);
	}
	$smcFunc['db_free_result']($request);

	return !empty($menu_id) ? current($return) : $return;
}

function sportal_get_menu_items($item_id = null, $sort = 'id_item')
{
	global $smcFunc, $txt;

	$query = array();
	$parameters = array('sort' => $sort);

	if (isset($item_id))
	{
		$query[] = 'id_item = {int:item_id}';
		$parameters['item_id'] = (int) $item_id;
	}

	$request = $smcFunc['db_query']('','
		SELECT id_item, id_menu, namespace, title
		FROM {db_prefix}sp_menu_items' . (!empty($query) ? '
		WHERE ' . implode(' AND ', $query) : '') . '
		ORDER BY {raw:sort}',
		$parameters
	);
	$return = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$return[$row['id_item']] = array(
			'id' => $row['id_item'],
			'id_menu' => $row['id_menu'],
			'namespace' => $row['namespace'],
			'title' => $row['title'],
		);
	}
	$smcFunc['db_free_result']($request);

	return !empty($item_id) ? current($return) : $return;
}

function sportal_get_profiles($profile_id = null, $type = null, $sort = 'id_profile')
{
	global $smcFunc, $txt;

	$query = array();
	$parameters = array('sort' => $sort);

	if (isset($profile_id))
	{
		$query[] = 'id_profile = {int:profile_id}';
		$parameters['profile_id'] = (int) $profile_id;
	}
	if (isset($type))
	{
		$query[] = 'type = {int:type}';
		$parameters['type'] = (int) $type;
	}

	$request = $smcFunc['db_query']('','
		SELECT id_profile, type, name, value
		FROM {db_prefix}sp_profiles' . (!empty($query) ? '
		WHERE ' . implode(' AND ', $query) : '') . '
		ORDER BY {raw:sort}',
		$parameters
	);
	$return = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$return[$row['id_profile']] = array(
			'id' => $row['id_profile'],
			'name' => $row['name'],
			'label' => isset($txt['sp_admin_profiles' . substr($row['name'], 1)]) ? $txt['sp_admin_profiles' . substr($row['name'], 1)] : $row['name'],
			'type' => $row['type'],
			'value' => $row['value'],
		);

		if ($row['type'] == 1)
		{
			list ($groups_allowed, $groups_denied) = explode('|',  $row['value']);

			$return[$row['id_profile']] = array_merge($return[$row['id_profile']], array(
				'groups_allowed' => $groups_allowed !== '' ? explode(',', $groups_allowed) : array(),
				'groups_denied' => $groups_denied !== '' ? explode(',', $groups_denied) : array(),
			));
		}
		elseif ($row['type'] == 2)
		{
			$return[$row['id_profile']] = array_merge($return[$row['id_profile']], sportal_parse_style('explode', $row['value'], true));
		}
		elseif ($row['type'] == 3)
		{
			list ($selections, $query) = explode('|',  $row['value']);

			$return[$row['id_profile']] = array_merge($return[$row['id_profile']], array(
				'selections' => explode(',', $selections),
				'query' => $query,
				'final' => implode(',', array($selections, $query)),
			));
		}
	}
	$smcFunc['db_free_result']($request);

	return !empty($profile_id) ? current($return) : $return;
}

function sportal_get_shoutbox($shoutbox_id = null, $active = false, $allowed = false)
{
	global $smcFunc, $context;

	$query = array();
	$parameters = array();

	if ($shoutbox_id !== null)
	{
		$query[] = 'id_shoutbox = {int:shoutbox_id}';
		$parameters['shoutbox_id'] = $shoutbox_id;
	}
	if (!empty($allowed))
		$query[] = sprintf($context['SPortal']['permissions']['query'], 'permissions');
	if (!empty($active))
	{
		$query[] = 'status = {int:status}';
		$parameters['status'] = 1;
	}

	$request = $smcFunc['db_query']('','
		SELECT
			id_shoutbox, name, permissions, moderator_groups, warning,
			allowed_bbc, height, num_show, num_max, refresh, reverse,
			caching, status, num_shouts, last_update
		FROM {db_prefix}sp_shoutboxes' . (!empty($query) ? '
		WHERE ' . implode(' AND ', $query) : '') . '
		ORDER BY name',
		$parameters
	);
	$return = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$return[$row['id_shoutbox']] = array(
			'id' => $row['id_shoutbox'],
			'name' => $row['name'],
			'permissions' => $row['permissions'],
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
		'spacp' => 5,
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