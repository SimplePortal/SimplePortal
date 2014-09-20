<?php

/**
 * @package SimplePortal
 *
 * @author SimplePortal Team
 * @copyright 2014 SimplePortal Team
 * @license BSD 3-clause
 *
 * @version 2.3.6
 */

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

		redirectexit('action=manageportal;area=portalblocks;sa=' . $list);
	}
	elseif($_REQUEST['type'] == 'category')
		redirectexit('action=manageportal;area=portalarticles;sa=categories');
	elseif($_REQUEST['type'] == 'article')
		redirectexit('action=manageportal;area=portalarticles;sa=articles');
	else
		redirectexit('action=manageportal');
}

function getFunctionInfo($function = null)
{
	global $db_prefix;

	$request = db_query("
		SELECT ID_FUNCTION, name
		FROM {$db_prefix}sp_functions" . (!empty($function) ? "
			WHERE name = '$function'" : "") . "
		ORDER BY function_order", __FILE__, __LINE__);
	$return = array();
	while ($row = mysql_fetch_assoc($request))
	{
		if ($row['name'] == 'sp_php' && !allowedTo('admin_forum'))
			continue;

		$return[] = array(
			'id' => $row['ID_FUNCTION'],
			'function' => $row['name'],
		);
	}
	mysql_free_result($request);

	return $return;
}

function getArticleInfo($article_id = null, $category_id = null, $message_id = null, $approved = null)
{
	global $db_prefix;

	$query = array();
	if(!empty($article_id))
		$query[] = "ID_ARTICLE = '$article_id'";
	if(!empty($category_id))
		$query[] = "ID_CATEGORY = '$category_id'";
	if(!empty($message_id))
		$query[] = "ID_MESSAGE = '$message_id'";
	if(!empty($approved))
		$query[] = "approved = '1'";

	$request = db_query("
		SELECT a.ID_ARTICLE, a.ID_CATEGORY, a.ID_MESSAGE, a.approved, c.name
		FROM {$db_prefix}sp_articles as a
			LEFT JOIN {$db_prefix}sp_categories AS c ON (c.ID_CATEGORY = a.ID_CATEGORY)" . (!empty($query) ? "
		WHERE " . implode(' AND ', $query) : "") . "
		ORDER BY ID_ARTICLE", __FILE__, __LINE__);
	$return = array();
	while ($row = mysql_fetch_assoc($request))
	{
		$return[] = array(
			'article' => array(
				'id' => $row['ID_ARTICLE'],
				'approved' => $row['approved'],
			),
			'category' => array(
				'id' => $row['ID_CATEGORY'],
				'name' => $row['name'],
			),
			'message' => array(
				'id' => $row['ID_MESSAGE'],
			),
		);
	}
	mysql_free_result($request);

	return $return;
}

function createArticle($articleOptions)
{
	global $db_prefix;

	$articleOptions['ID_CATEGORY'] = !empty($articleOptions['ID_CATEGORY']) ? (int) $articleOptions['ID_CATEGORY'] : 0;
	$articleOptions['ID_MESSAGE'] = !empty($articleOptions['ID_MESSAGE']) ? (int) $articleOptions['ID_MESSAGE'] : 0;
	$articleOptions['approved'] = !empty($articleOptions['approved']) ? (int) $articleOptions['approved'] : 0;

	$request = db_query("
		SELECT ID_MESSAGE
		FROM {$db_prefix}sp_articles
		WHERE ID_MESSAGE = '$articleOptions[ID_MESSAGE]'
		LIMIT 1", __FILE__, __LINE__);
	list ($exists) = mysql_fetch_row($request);
	mysql_free_result($request);

	if (empty($articleOptions['ID_CATEGORY']) || empty($articleOptions['ID_MESSAGE']) || $exists)
		return false;

	db_query("
		INSERT INTO {$db_prefix}sp_articles
			(ID_CATEGORY, ID_MESSAGE, approved)
		VALUES ('$articleOptions[ID_CATEGORY]', '$articleOptions[ID_MESSAGE]', $articleOptions[approved])", __FILE__, __LINE__);

	db_query("
		UPDATE {$db_prefix}sp_categories
		SET articles = articles + 1
		WHERE ID_CATEGORY = $articleOptions[ID_CATEGORY]
		LIMIT 1", __FILE__, __LINE__);
}

function getCategoryInfo($category_id = null, $publish = false)
{
	global $scripturl, $context, $db_prefix, $settings, $txt;

	$query = array();
	if(!empty($category_id))
		$query[] = "ID_CATEGORY = '$category_id'";
	if(!empty($publish))
		$query[] = "publish = 1";

	$request = db_query("
		SELECT ID_CATEGORY, name, picture, articles, publish
		FROM {$db_prefix}sp_categories" . (!empty($query) ? "
		WHERE " . implode(' AND ', $query) : "") . "
		ORDER BY ID_CATEGORY", __FILE__, __LINE__);
	$return = array();
	while ($row = mysql_fetch_assoc($request))
	{
		$return[] = array(
			'id' => $row['ID_CATEGORY'],
			'name' => $row['name'],
			'picture' => array (
				'href' => $row['picture'],
				'image' => '<img src="' . $row['picture'] . '" alt="' . $row['name'] . '" width="75" />',
			),
			'articles' => $row['articles'],
			'publish' => $row['publish'],
		);
	}
	mysql_free_result($request);

	return $return;
}

function fixCategoryArticles()
{
	global $db_prefix;

	$categoryList = getCategoryInfo();
	$category_ids = array();

	foreach ($categoryList as $category)
		$category_ids[] = $category['id'];

	foreach($category_ids as $category)
	{
		$article_count = 0;

		$result = db_query("
			SELECT COUNT(*)
			FROM {$db_prefix}sp_articles
			WHERE ID_CATEGORY = $category", __FILE__, __LINE__);
		list ($article_count) = mysql_fetch_row($result);
		mysql_free_result($result);

		db_query("
			UPDATE {$db_prefix}sp_categories
			SET articles = $article_count
			WHERE ID_CATEGORY = $category
			LIMIT 1", __FILE__, __LINE__);
	}
}

function fixColumnRows($column_id = null)
{
	global $db_prefix;

	$blockList = getBlockInfo($column_id);
	$block_ids = array();

	foreach ($blockList as $block)
		$block_ids[] = $block['id'];

	$counter = 0;

	foreach($block_ids as $block)
	{
		$counter = $counter + 1;

		db_query("
			UPDATE {$db_prefix}sp_blocks
			SET row = $counter
			WHERE ID_BLOCK = $block
			LIMIT 1", __FILE__, __LINE__);
	}
}

function changeState($type = null, $id = null)
{
	global $db_prefix;

	if ($type == 'block')
		$query = array(
			'column' => 'state',
			'table' => 'blocks',
			'id' => 'ID_BLOCK'
		);
	elseif ($type == 'category')
		$query = array(
			'column' => 'publish',
			'table' => 'categories',
			'id' => 'ID_CATEGORY'
		);
	elseif ($type == 'article')
		$query = array(
			'column' => 'approved',
			'table' => 'articles',
			'id' => 'ID_ARTICLE'
		);
	else
		return false;

	$request = db_query("
		SELECT $query[column]
		FROM {$db_prefix}sp_$query[table]
		WHERE $query[id] = $id
		LIMIT 1", __FILE__, __LINE__);
	list ($state) = mysql_fetch_row($request);
	mysql_free_result($request);

	$state = (int) $state;
	$state = $state == 1 ? 0 : 1 ;

	db_query("
		UPDATE {$db_prefix}sp_$query[table]
		SET $query[column] = $state
		WHERE $query[id] = $id
		LIMIT 1", __FILE__, __LINE__);
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
function sp_loadMemberGroups($selectedGroups = array(), $show = 'normal', $contextName = 'member_groups')
{
	global $db_prefix, $context, $smcFunc, $txt;

	// Some additional Language stings are needed
	loadLanguage('ManageBoards');

	// Make sure its empty
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
		'normal' => 'ID_GROUP != 3',
		'moderator' => 'ID_GROUP != 1 AND ID_GROUP != 3',
		'post' => 'minPosts != -1',
		'master' => 'minPosts = -1 AND ID_GROUP != 3',
	);
	$show = strtolower($show);

	if (!isset($show_option[$show]))
		$show = 'normal';

	// Guest and Members are added manually. Only on normal ond master View =)
	if($show == 'normal' || $show == 'master' || $show == 'moderator')
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
	$request = db_query("
		SELECT groupName, ID_GROUP, minPosts
		FROM {$db_prefix}membergroups
		WHERE ".$show_option[$show]."
		ORDER BY minPosts, ID_GROUP != 2, groupName",__FILE__, __LINE__);
	while ($row = mysql_fetch_assoc($request))
	{
		$context[$contextName][(int) $row['ID_GROUP']] = array(
			'id' => $row['ID_GROUP'],
			'name' => trim($row['groupName']),
			'checked' => $checked || in_array($row['ID_GROUP'], $selectedGroups),
			'is_post_group' => $row['minPosts'] != -1,
		);
	}
	mysql_free_result($request);
}

function sp_load_membergroups()
{
	global $db_prefix, $txt;

	loadLanguage('ManageBoards');

	$groups = array(
		-1 => $txt['membergroups_guests'],
		0 => $txt['membergroups_members'],
	);

	$request = db_query("
		SELECT groupName, ID_GROUP, minPosts
		FROM {$db_prefix}membergroups
		WHERE ID_GROUP != 3
		ORDER BY minPosts, groupName",__FILE__, __LINE__);
	while ($row = mysql_fetch_assoc($request))
		$groups[(int) $row['ID_GROUP']] = trim($row['groupName']);
	mysql_free_result($request);

	return $groups;
}

?>