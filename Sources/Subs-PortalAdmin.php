<?php
/**********************************************************************************
* Subs-PortalAdmin.php                                                            *
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
	void sportal_admin_state_change()
		// !!!

	array getFunctionInfo()
		// !!!

	array getArticleInfo()
		// !!!

	void createArticle()
		// !!!

	array getCategoryInfo()
		// !!!

	void fixCategoryArticles()
		// !!!

	void fixColumnRows()
		// !!!

	void changeBlockRow()
		// !!!

	void changeState()
		// !!!

	string sp_validate_php()
		// !!!

	void sp_loadMembergroups()
		// !!!
*/

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

function getArticleInfo($article_id = null, $category_id = null, $message_id = null, $approved = null)
{
	global $smcFunc;

	$query = array();
	if (!empty($article_id))
		$query[] = "id_article = {int:article_id}";
	if (!empty($category_id))
		$query[] = "id_category = {int:category_id}";
	if (!empty($message_id))
		$query[] = "id_message = {int:message_id}";
	if (!empty($approved))
		$query[] = "approved = '{int:approved}'";

	$request = $smcFunc['db_query']('','
		SELECT a.id_article, a.id_category, a.id_message, a.approved, c.name
		FROM {db_prefix}sp_articles as a
			LEFT JOIN {db_prefix}sp_categories AS c ON (c.id_category = a.id_category)' . (!empty($query) ? '
		WHERE ' . implode(' AND ', $query) : '') .'
		ORDER BY id_article',
		array(
			'article_id' => $article_id,
			'category_id' => $category_id,
			'message_id' => $message_id,
			'approved' => 1,
		)
	);
	$return = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$return[] = array(
			'article' => array(
				'id' => $row['id_article'],
				'approved' => $row['approved'],
			),
			'category' => array(
				'id' => $row['id_category'],
				'name' => $row['name'],
			),
			'message' => array(
				'id' => $row['id_message'],
			),
		);
	}
	$smcFunc['db_free_result']($request);

	return $return;
}

function createArticle($articleOptions)
{
	global $smcFunc;

	$articleOptions['id_category'] = !empty($articleOptions['id_category']) ? (int) $articleOptions['id_category'] : 0;
	$articleOptions['id_message'] = !empty($articleOptions['id_message']) ? (int) $articleOptions['id_message'] : 0;
	$articleOptions['approved'] = !empty($articleOptions['approved']) ? (int) $articleOptions['approved'] : 0;

	$request = $smcFunc['db_query']('','
		SELECT id_message
		FROM {db_prefix}sp_articles
		WHERE id_message = {int:id_message}',
		array(
			'id_message' => $articleOptions['id_message'],
		)
	);
	list ($exists) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	if (empty($articleOptions['id_category']) || empty($articleOptions['id_message']) || $exists)
		return false;

	$smcFunc['db_insert']('normal', '{db_prefix}sp_articles',
		array('id_category' => 'int', 'id_message' => 'int', 'approved' => 'int'),
		array($articleOptions['id_category'], $articleOptions['id_message'], $articleOptions['approved']),
		array('id_article')
	);

	$smcFunc['db_query']('','
		UPDATE {db_prefix}sp_categories
		SET articles = articles + 1
		WHERE id_category = {int:id_category}',
		array(
			'id_category' => $articleOptions['id_category'],
		)
	);
}

function getCategoryInfo($category_id = null, $publish = false)
{
	global $scripturl, $context, $smcFunc, $settings, $txt;

	$query = array();
	$parameters = array();
	if (!empty($category_id))
	{
		$query[] = 'id_category = {int:category_id}';
		$parameters['category_id'] = $category_id;
	}
	if (!empty($publish))
	{
		$query[] = 'publish = {int:publish}';
		$parameters['publish'] = 1;
	}

	$request = $smcFunc['db_query']('','
		SELECT id_category, name, picture, articles, publish
		FROM {db_prefix}sp_categories' . (!empty($query) ? '
		WHERE ' . implode(' AND ', $query) : '') . '
		ORDER BY id_category',
		$parameters
	);
	$return = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$return[] = array(
			'id' => $row['id_category'],
			'name' => $row['name'],
			'picture' => array (
				'href' => $row['picture'],
				'image' => '<img src="' . $row['picture'] . '" alt="' . $row['name'] . '" width="75" />',
			),
			'articles' => $row['articles'],
			'publish' => $row['publish'],
		);
	}
	$smcFunc['db_free_result']($request);

	return $return;
}

function fixCategoryArticles()
{
	global $smcFunc;

	$categoryList = getCategoryInfo();
	$categoryIds = array();

	foreach ($categoryList as $category)
		$categoryIds[] = $category['id'];

	foreach ($categoryIds as $category)
	{
		$article_count = 0;

		$request = $smcFunc['db_query']('','
			SELECT COUNT(*)
			FROM {db_prefix}sp_articles
			WHERE id_category = {int:id_category}',
			array(
				'id_category' => $category,
			)
		);
		list ($article_count) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);

		$smcFunc['db_query']('','
			UPDATE {db_prefix}sp_categories
			SET articles = {int:article_count}
			WHERE id_category = {int:id_category}',
			array(
				'article_count' => $article_count,
				'id_category' => $category,
			)
		);
	}
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

/*
	void sp_loadMemberGroups(Array $selectedGroups = array, Array $removeGroups = array(), string $show = 'normal', string $contextName = 'member_groups')
	This will file the $context['member_groups'] to the given options
	$selectedGroups means all groups who should be shown as selcted, if you like to check all than insert an 'all'
		You can also Give the function a string with '2,3,4'
	$removeGroups this group id should not shown in the list
	$show have follow options
		'normal' => will show all groups, and add a guest and regular member (Standard)
		'post' => will load only post groups
		'master' => will load only not postbased groups
	$contextName where the datas should stored in the $context.
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

?>