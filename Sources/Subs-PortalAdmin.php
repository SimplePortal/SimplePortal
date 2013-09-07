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

function sportal_admin_state_change()
{
	checkSession('get');

	if (!empty($_REQUEST['block_id']))
		$id = (int) $_REQUEST['block_id'];
	elseif (!empty($_REQUEST['category_id']))
		$id = (int) $_REQUEST['category_id'];
	elseif (!empty($_REQUEST['article_id']))
		$id = (int) $_REQUEST['article_id'];
	else
		fatal_lang_error('error_sp_id_empty', false);

	changeState($_REQUEST['type'], $id);

	if($_REQUEST['type'] == 'block')
	{
		$sides = array(1 => 'left', 2 => 'top', 3 => 'bottom', 4 => 'right');
		$list = !empty($_GET['redirect']) && isset($sides[$_GET['redirect']]) ? $sides[$_GET['redirect']] : 'list';

		redirectexit('action=admin;area=portalblocks;sa=' . $list);
	}
	elseif($_REQUEST['type'] == 'category')
		redirectexit('action=admin;area=portalarticles;sa=categories');
	elseif($_REQUEST['type'] == 'article')
		redirectexit('action=admin;area=portalarticles;sa=articles');
	else
		redirectexit('action=admin;area=portalconfig');
}

function getFunctionInfo($function = null)
{
	global $smcFunc;

	$request = $smcFunc['db_query']('','
		SELECT id_function, name
		FROM {db_prefix}sp_functions' . (!empty($function) ? '
		WHERE name = {string:function}' : '') . '
		ORDER BY function_order',
		array(
			'function' => $function,
		)
	);
	$return = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		if ($row['name'] == 'sp_php' && !allowedTo('admin_forum'))
			continue;

		$return[] = array(
			'id' => $row['id_function'],
			'function' => $row['name'],
		);
	}
	$smcFunc['db_free_result']($request);

	return $return;
}

function fixColumnRows($column_id = null)
{
	global $smcFunc;

	$blockList = getBlockInfo($column_id);
	$blockIds = array();

	foreach($blockList as $block)
		$blockIds[] = $block['id'];

	$counter = 0;

	foreach ($blockIds as $block)
	{
		$counter = $counter + 1;

		$smcFunc['db_query']('','
			UPDATE {db_prefix}sp_blocks
			SET row = {int:counter}
			WHERE id_block = {int:block}',
			array(
				'counter' => $counter,
				'block' => $block,
			)
		);
	}
}

function changeState($type = null, $id = null)
{
	global $smcFunc;

	if ($type == 'block')
		$query = array(
			'column' => 'state',
			'table' => 'blocks',
			'query_id' => 'id_block',
			'id' => $id
		);
	elseif ($type == 'category')
		$query = array(
			'column' => 'publish',
			'table' => 'categories',
			'query_id' => 'id_category',
			'id' => $id
		);
	elseif ($type == 'article')
		$query = array(
			'column' => 'approved',
			'table' => 'articles',
			'query_id' => 'id_article',
			'id' => $id
		);
	else
		return false;

	$request = $smcFunc['db_query']('','
		SELECT {raw:column}
		FROM {db_prefix}sp_{raw:table}
		WHERE {raw:query_id} = {int:id}',
		$query
	);

	list ($state) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	$state = (int) $state;
	$state = $state == 1 ? 0 : 1 ;

	$smcFunc['db_query']('','
		UPDATE {db_prefix}sp_{raw:table}
		SET {raw:column} = {int:state}
		WHERE {raw:query_id} = {int:id}',
		array(
			'table' => $query['table'],
			'column' => $query['column'],
			'state' => $state,
			'query_id' => $query['query_id'],
			'id' => $id,
		)
	);
}

function sp_validate_php($code)
{
	global $boardurl, $boarddir, $sourcedir, $modSettings;

	$id = time();
	$token = md5(mt_rand() . session_id() . (string) microtime() . $modSettings['rand_seed']);
	$error = false;
	$filename = 'sp_tmp_' . $id . '.php';

	$code = trim($code);
	if (substr($code, 0, 5) == '<?php')
		$code = substr($code, 5);
	if (substr($code, -2) == '?>')
		$code = substr($code, 0, -2);

	require_once($sourcedir . '/Subs-Package.php');

	$content = '<?php

if (empty($_GET[\'token\']) || $_GET[\'token\'] !== \'' . $token . '\')
	exit();

require_once(\'' . $boarddir . '/SSI.php\');

' . $code . '

?>';

	$fp = fopen($boarddir . '/' . $filename, 'w');
	fwrite($fp, $content);
	fclose($fp);

	if (!file_exists($boarddir . '/' . $filename))
		return false;

	$result = fetch_web_data($boardurl . '/' . $filename . '?token=' . $token);

	if ($result === false)
		$error = 'database';
	elseif (preg_match('~ <b>(\d+)</b><br( /)?' . '>$~i', $result) != 0)
		$error = 'syntax';

	unlink($boarddir . '/' . $filename);

	return $error;
}

/**
 * This will file the $context['member_groups'] to the given options
 *
 * @param type $selectedGroups - all groups who should be shown as selcted, if you like to check all than insert an 'all'
 *								 You can also Give the function a string with '2,3,4'
 * @param type $show -  'normal' => will show all groups, and add a guest and regular member (Standard)
 *						'post' => will load only post groups
 *						'master' => will load only not postbased groups
 * @param type $contextName - where the datas should stored in the $context
 * @param type $subContext
 */
function sp_loadMemberGroups($selectedGroups = array(), $show = 'normal', $contextName = 'member_groups', $subContext = 'SPortal')
{
	global $context, $smcFunc, $txt;

	// Some additional Language stings are needed
	loadLanguage('ManageBoards');

	// Make sure its empty
	if (!empty($subContext))
		$context[$subContext][$contextName] = array();
	else
		$context[$contextName] = array();

	// Preseting some things :)
	if (!is_array($selectedGroups))
		$checked = strtolower($selectedGroups) == 'all';
	else
		$checked = false;

	if (!$checked && isset($selectedGroups) && $selectedGroups === '0')
		$selectedGroups = array(0);
	elseif (!$checked && !empty($selectedGroups))
	{
		if (!is_array($selectedGroups))
			$selectedGroups = explode(',', $selectedGroups);

		// Remove all strings, i will only allowe ids :P
		foreach ($selectedGroups as $k => $i)
			$selectedGroups[$k] = (int) $i;

		$selectedGroups = array_unique($selectedGroups);
	}
	else
		$selectedGroups = array();

	// Okay let's checkup the show function
	$show_option = array(
		'normal' => 'id_group != 3',
		'moderator' => 'id_group != 1 AND id_group != 3',
		'post' => 'min_posts != -1',
		'master' => 'min_posts = -1 AND id_group != 3',
	);

	$show = strtolower($show);

	if (!isset($show_option[$show]))
		$show = 'normal';

	// Guest and Members are added manually. Only on normal ond master View =)
	if ($show == 'normal' || $show == 'master' || $show == 'moderator')
	{
		if ($show != 'moderator')
		{
			$context[$contextName][-1] = array(
				'id' => -1,
				'name' => $txt['membergroups_guests'],
				'checked' => $checked || in_array(-1, $selectedGroups),
				'is_post_group' => false,
			);
		}
		$context[$contextName][0] = array(
			'id' => 0,
			'name' => $txt['membergroups_members'],
			'checked' => $checked || in_array(0, $selectedGroups),
			'is_post_group' => false,
		);
	}

	// Load membergroups.
	$request = $smcFunc['db_query']('', '
		SELECT group_name, id_group, min_posts
		FROM {db_prefix}membergroups
		WHERE {raw:show}
		ORDER BY min_posts, id_group != {int:global_moderator}, group_name',
		array(
			'show' => $show_option[$show],
			'global_moderator' => 2,
		)
	);
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$context[$contextName][(int) $row['id_group']] = array(
			'id' => $row['id_group'],
			'name' => trim($row['group_name']),
			'checked' => $checked || in_array($row['id_group'], $selectedGroups),
			'is_post_group' => $row['min_posts'] != -1,
		);
	}
	$smcFunc['db_free_result']($request);
}

function sp_load_membergroups()
{
	global $smcFunc, $txt;

	loadLanguage('ManageBoards');

	$groups = array(
		-1 => $txt['parent_guests_only'],
		0 => $txt['parent_members_only'],
	);

	$request = $smcFunc['db_query']('', '
		SELECT group_name, id_group, min_posts
		FROM {db_prefix}membergroups
		WHERE id_group != {int:moderator_group}
		ORDER BY min_posts, group_name',
		array(
			'moderator_group' => 3,
		)
	);
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$groups[(int) $row['id_group']] = trim($row['group_name']);
	$smcFunc['db_free_result']($request);

	return $groups;
}
