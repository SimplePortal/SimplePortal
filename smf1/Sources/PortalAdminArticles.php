<?php
/**********************************************************************************
* PortalAdminArticles.php                                                         *
***********************************************************************************
* SimplePortal                                                                    *
* SMF Modification Project Founded by [SiNaN] (sinan@simplemachines.org)          *
* =============================================================================== *
* Software Version:           SimplePortal 2.3.6                                  *
* Software by:                SimplePortal Team (http://www.simpleportal.net)     *
* Copyright 2008-2014 by:     SimplePortal Team (http://www.simpleportal.net)     *
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
	void sportal_admin_articles_main()
		// !!!

	void sportal_admin_article_list()
		// !!!

	array sportal_admin_articles_callback()
		// !!!

	void sportal_admin_article_add()
		// !!!

	void sportal_admin_article_edit()
		// !!!

	void sportal_admin_article_delete()
		// !!!

	void sportal_admin_category_list()
		// !!!

	void sportal_admin_category_add()
		// !!!

	void sportal_admin_category_edit()
		// !!!

	void sportal_admin_category_delete()
		// !!!
*/

function sportal_admin_articles_main()
{
	global $context, $txt, $scripturl, $sourcedir;

	if (!allowedTo('sp_admin'))
		isAllowedTo('sp_manage_articles');

	loadTemplate('PortalAdminArticles');

	$subActions = array(
		'articles' => 'sportal_admin_article_list',
		'addarticle' => 'sportal_admin_article_add',
		'editarticle' => 'sportal_admin_article_edit',
		'deletearticle' => 'sportal_admin_article_delete',
		'categories' => 'sportal_admin_category_list',
		'addcategory' => 'sportal_admin_category_add',
		'editcategory' => 'sportal_admin_category_edit',
		'deletecategory' => 'sportal_admin_category_delete',
		'statechange' => 'sportal_admin_state_change',
	);

	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'articles';

	$context['sub_action'] = $_REQUEST['sa'];

	$context['admin_tabs'] = array(
		'title' => $txt['sp-adminCatTitle'],
		'help' => 'sp_ArticlesArea',
		'description' => $txt['sp-adminCatDesc'],
		'tabs' => array(
			'articles' => array(
				'title' => $txt['sp-adminArticleListName'],
				'description' => $txt['sp-adminArticleListDesc'],
				'href' => $scripturl . '?action=manageportal;area=portalarticles;sa=articles',
				'is_selected' => $_REQUEST['sa'] == 'articles' || $_REQUEST['sa'] == 'editarticle',
			),
			'addarticle' => array(
				'title' => $txt['sp-adminArticleAddName'],
				'description' => $txt['sp-adminArticleAddDesc'],
				'href' => $scripturl . '?action=manageportal;area=portalarticles;sa=addarticle',
				'is_selected' => $_REQUEST['sa'] == 'addarticle',
			),
			'categories' => array(
				'title' => $txt['sp-adminCategoryListName'],
				'description' => $txt['sp-adminCategoryListDesc'],
				'href' => $scripturl . '?action=manageportal;area=portalarticles;sa=categories',
				'is_selected' => $_REQUEST['sa'] == 'categories' || $_REQUEST['sa'] == 'editcategory',
			),
			'addcategory' => array(
				'title' => $txt['sp-adminCategoryAddName'],
				'description' => $txt['sp-adminCategoryAddDesc'],
				'href' => $scripturl . '?action=manageportal;area=portalarticles;sa=addcategory',
				'is_selected' => $_REQUEST['sa'] == 'addcategory',
			),
		),
	);

	$subActions[$_REQUEST['sa']]();
}

function sportal_admin_article_list()
{
	global $txt, $db_prefix, $context, $article_request, $scripturl, $user_info;

	// Call the template.
	$context['sub_template'] = 'article_list';

	// You clicked to the remove button? Naughty boy. :P
	if (!empty($_POST['removeArticles']) && !empty($_POST['remove']) && is_array($_POST['remove']))
	{
		// But can you?
		checkSession();

		// Are they integer?
		foreach ($_POST['remove'] as $index => $articleid)
			$_POST['remove'][(int) $index] = (int) $articleid;

		// Delete 'em all.
		db_query("
			DELETE FROM {$db_prefix}sp_articles
			WHERE ID_ARTICLE IN (" . implode(', ', $_POST['remove']) . ')
			LIMIT ' . count($_POST['remove']), __FILE__, __LINE__);

		//Fix the category article counts.
		fixCategoryArticles();
	}

	// How can we sort the list?
	$sort_methods = array(
		'topic' =>  array(
			'down' => 'm.subject ASC',
			'up' => 'm.subject DESC'
		),
		'board' => array(
			'down' => 'b.name ASC',
			'up' => 'b.name DESC'
		),
		'poster' => array(
			'down' => 'm.posterName ASC',
			'up' => 'm.posterName DESC'
		),
		'time' => array(
			'down' => 'm.posterTime ASC',
			'up' => 'm.posterTime DESC'
		),
		'category' => array(
			'down' => 'c.name ASC',
			'up' => 'c.name DESC'
		),
		'approved' => array(
			'down' => 'a.approved ASC',
			'up' => 'a.approved DESC'
		),
	);

	// Columns to show.
	$context['columns'] = array(
		'topic' => array(
			'width' => '20%',
			'label' => $txt['sp-adminColumnTopic'],
			'sortable' => true
		),
		'board' => array(
			'width' => '20%',
			'label' => $txt['sp-adminColumnBoard'],
			'sortable' => true
		),
		'poster' => array(
			'width' => '10%',
			'label' => $txt['sp-adminColumnPoster'],
			'sortable' => true
		),
		'time' => array(
			'width' => '20%',
			'label' => $txt['sp-adminColumnTime'],
			'sortable' => true
		),
		'category' => array(
			'width' => '20%',
			'label' => $txt['sp-adminColumnCategory'],
			'sortable' => true
		),
		'approved' => array(
			'width' => '5%',
			'label' => $txt['sp-adminColumnApproved'],
			'sortable' => true,
		),
		'actions' => array(
			'width' => '5%',
			'label' => $txt['sp-adminColumnAction'],
			'sortable' => false
		)
	);

	// Default sort is according to the topic.
	if (!isset($_REQUEST['sort']) || !isset($sort_methods[$_REQUEST['sort']]))
		$_REQUEST['sort'] = 'topic';

	// Set the sort links.
	foreach ($context['columns'] as $col => $dummy)
	{
		$context['columns'][$col]['selected'] = $col == $_REQUEST['sort'];
		$context['columns'][$col]['href'] = $scripturl . '?action=manageportal;area=portalarticles;sa=articles;sort=' . $col;

		if (!isset($_REQUEST['desc']) && $col == $_REQUEST['sort'])
			$context['columns'][$col]['href'] .= ';desc';

		$context['columns'][$col]['link'] = '<a href="' . $context['columns'][$col]['href'] . '">' . $context['columns'][$col]['label'] . '</a>';
	}

	$context['sort_by'] = $_REQUEST['sort'];
	$context['sort_direction'] = !isset($_REQUEST['desc']) ? 'down' : 'up';

	// Count all the articles.
	$request = db_query("
		SELECT COUNT(*)
		FROM {$db_prefix}sp_articles AS a
			INNER JOIN {$db_prefix}messages AS m ON (m.ID_MSG = a.ID_MESSAGE)
			INNER JOIN {$db_prefix}boards AS b ON (b.ID_BOARD = m.ID_BOARD)
		WHERE $user_info[query_see_board]", __FILE__, __LINE__);
	list ($totalArticles) = mysql_fetch_row($request);
	mysql_free_result($request);

	// Construct the page index. 20 articles per page.
	$context['page_index'] = constructPageIndex($scripturl . '?action=manageportal;area=portalarticles;sa=articles;sort=' . $_REQUEST['sort'] . (isset($_REQUEST['desc']) ? ';desc' : ''), $_REQUEST['start'], $totalArticles, 20);
	$context['start'] = $_REQUEST['start'];

	// A *small* query to get article info.
	$article_request = db_query("
		SELECT a.ID_ARTICLE, a.ID_CATEGORY, a.ID_MESSAGE, a.approved, c.name as cname, m.ID_MEMBER, m.posterName,
			m.posterTime, m.subject, t.ID_TOPIC, t.numReplies, t.numViews, b.ID_BOARD, b.name as bname, mem.realName
		FROM {$db_prefix}sp_articles AS a
			INNER JOIN {$db_prefix}sp_categories AS c ON (c.ID_CATEGORY = a.ID_CATEGORY)
			INNER JOIN {$db_prefix}messages AS m ON (m.ID_MSG = a.ID_MESSAGE)
			INNER JOIN {$db_prefix}topics AS t ON (t.ID_FIRST_MSG = a.ID_MESSAGE)
			INNER JOIN {$db_prefix}boards AS b ON (b.ID_BOARD = m.ID_BOARD)
			LEFT JOIN {$db_prefix}members AS mem ON (mem.ID_MEMBER = m.ID_MEMBER)
		WHERE $user_info[query_see_board]
		ORDER BY " . $sort_methods[$_REQUEST['sort']][$context['sort_direction']] . "
		LIMIT $context[start], 20", __FILE__, __LINE__);

	// Call-back...
	$context['get_article'] = 'sportal_admin_articles_callback';
	$context['page_title'] = $txt['sp-adminArticleListName'];
}

// Call-back for getting a row of article data.
function sportal_admin_articles_callback($reset = false)
{
	global $scripturl, $article_request, $txt, $context, $settings;

	if ($article_request == false)
		return false;

	if (!($row = mysql_fetch_assoc($article_request)))
		return false;

	// Build up the array.
	$output = array(
		'article' => array(
			'id' => $row['ID_ARTICLE'],
			'approved' => $row['approved'],
		),
		'category' => array(
			'id' => $row['ID_CATEGORY'],
			'name' => '<a href="' . $scripturl . '?action=manageportal;area=portalarticles;sa=editcategory;category_id=' . $row['ID_CATEGORY'] . '">' . $row['cname'] . '</a>',
		),
		'message' => array(
			'id' => $row['ID_MESSAGE'],
			'subject' => $row['subject'],
			'time' => timeformat($row['posterTime'], '%H:%M:%S, %d/%m/%y'),
		),
		'poster' => array(
			'id' => $row['ID_MEMBER'],
			'name' => $row['posterName'],
			'link' => (empty($row['ID_MEMBER']) ? $row['posterName'] : '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '">' . $row['realName'] . '</a>'),
		),
		'topic' => array(
			'id' => $row['ID_TOPIC'],
			'replies' => $row['numReplies'],
			'views' => $row['numViews'],
			'link' => '<a href="' . $scripturl . '?topic=' . $row['ID_TOPIC'] . '.0">' . $row['subject'] . '</a>',
		),
		'board' => array(
			'id' => $row['ID_BOARD'],
			'name' => $row['bname'],
			'link' => '<a href="' . $scripturl . '?board=' . $row['ID_BOARD'] . '.0">' . $row['bname'] . '</a>',
		),
		'edit' => '<a href="' . $scripturl . '?action=manageportal;area=portalarticles;sa=editarticle;article_id=' . $row['ID_ARTICLE'] . ';sesc=' . $context['session_id'] . '">' . sp_embed_image('modify') . '</a>',
		'delete' => '<a href="' . $scripturl . '?action=manageportal;area=portalarticles;sa=deletearticle;article_id=' . $row['ID_ARTICLE'] . ';sesc=' . $context['session_id'] . '" onclick="return confirm(\''.$txt['sp-articlesDeleteConfirm'].'\');">' . sp_embed_image('delete') . '</a>'
	);

	return $output;
}

// Function for adding articles.
function sportal_admin_article_add()
{
	global $txt, $context, $scripturl, $db_prefix, $modSettings, $user_info;

	// Are we ready?
	if(empty($_POST['createArticle']) || empty($_POST['articles'])) {

		// List all the categories.
		$context['list_categories'] = getCategoryInfo();

		if(empty($context['list_categories']))
			fatal_error($txt['error_sp_no_category'] . '<br />' . sprintf($txt['error_sp_no_category_sp_moderator'], $scripturl . '?action=manageportal;area=portalarticles;sa=addcategory'), false);

		// Which board to show?
		if(isset($_REQUEST['targetboard']))
			$_REQUEST['targetboard'] = (int) $_REQUEST['targetboard'];
		else {

			// Find one yourself.
			$request = db_query("
				SELECT b.ID_BOARD
				FROM {$db_prefix}boards AS b
				WHERE $user_info[query_see_board]
				ORDER BY b.ID_BOARD DESC
				LIMIT 1", __FILE__, __LINE__);
			list ($_REQUEST['targetboard']) = mysql_fetch_row($request);
			mysql_free_result($request);
		}

		$context['target_board'] = $_REQUEST['targetboard'];

		// Get the total topic count.
		$request = db_query("
			SELECT COUNT(*)
			FROM {$db_prefix}topics as t
				INNER JOIN {$db_prefix}boards AS b ON (b.ID_BOARD = t.ID_BOARD)
				LEFT JOIN {$db_prefix}sp_articles as a ON (a.ID_MESSAGE = t.ID_FIRST_MSG)
			WHERE b.ID_BOARD = $_REQUEST[targetboard]
				AND IFNULL(a.ID_ARTICLE, 0) = 0
				AND $user_info[query_see_board]", __FILE__, __LINE__);
		list ($topiccount) = mysql_fetch_row($request);
		mysql_free_result($request);

		// Create the page index.
		$context['page_index'] = constructPageIndex($scripturl . '?action=manageportal;area=portalarticles;sa=addarticle;targetboard=' . $_REQUEST['targetboard'] . ';board=' . $_REQUEST['targetboard'] . '.%d', $_REQUEST['start'], $topiccount, $modSettings['defaultMaxTopics'], true);

		// Get some info about the boards and categories.
		$request = db_query("
			SELECT b.ID_BOARD, b.name AS bName, c.name AS cName
			FROM {$db_prefix}boards AS b
				LEFT JOIN {$db_prefix}categories AS c ON (c.ID_CAT = b.ID_CAT)
			WHERE $user_info[query_see_board]", __FILE__, __LINE__);
		$context['boards'] = array();
		while ($row = mysql_fetch_assoc($request))
			$context['boards'][] = array(
				'id' => $row['ID_BOARD'],
				'name' => $row['bName'],
				'category' => $row['cName']
			);
		mysql_free_result($request);

		// Time to get the topic data.
		$request = db_query("
			SELECT t.ID_TOPIC, m.subject, m.ID_MEMBER, IFNULL(mem.realName, m.posterName) AS posterName, m.ID_MSG
			FROM ({$db_prefix}topics AS t, {$db_prefix}messages AS m)
				INNER JOIN {$db_prefix}boards AS b ON (b.ID_BOARD = m.ID_BOARD)
				LEFT JOIN {$db_prefix}members AS mem ON (mem.ID_MEMBER = m.ID_MEMBER)
				LEFT JOIN {$db_prefix}sp_articles as a ON (a.ID_MESSAGE = t.ID_FIRST_MSG)
			WHERE m.ID_MSG = t.ID_FIRST_MSG
				AND IFNULL(a.ID_ARTICLE, 0) = 0
				AND t.ID_BOARD = $_REQUEST[targetboard]
				AND $user_info[query_see_board]
			ORDER BY " . (!empty($modSettings['enableStickyTopics']) ? 't.isSticky DESC, ' : '') . "t.ID_LAST_MSG DESC
			LIMIT $_REQUEST[start], $modSettings[defaultMaxTopics]", __FILE__, __LINE__);
		$context['topics'] = array();
		while ($row = mysql_fetch_assoc($request))
		{
			censorText($row['subject']);

			$context['topics'][] = array(
				'id' => $row['ID_TOPIC'],
				'msg_id' => $row['ID_MSG'],
				'poster' => array(
					'id' => $row['ID_MEMBER'],
					'name' => $row['posterName'],
					'href' => empty($row['ID_MEMBER']) ? '' : $scripturl . '?action=profile;u=' . $row['ID_MEMBER'],
					'link' => empty($row['ID_MEMBER']) ? $row['posterName'] : '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '" target="_blank">' . $row['posterName'] . '</a>'
				),
				'subject' => $row['subject'],
				'js_subject' => addcslashes(addslashes($row['subject']), '/')
			);
		}
		mysql_free_result($request);

		// Set the page title and sub-template.
		$context['page_title'] = $txt['sp-articlesAdd'];
		$context['sub_template'] = 'article_add';
	}
	else {

		// But can you?
		checkSession();

		// Are they integer?
		foreach ($_POST['articles'] as $index => $articleid)
			$_POST['articles'][(int) $index] = (int) $articleid;

		// Add all of them.
		foreach($_POST['articles'] as $article) {

			// Set them. They have their own IDs.
			$articleOptions = array(
				'ID_CATEGORY' => !empty($_POST['category']) ? (int) $_POST['category'] : 0,
				'ID_MESSAGE' => $article,
				'approved' => 1,
			);

			// A tricky function.
			createArticle($articleOptions);
		}

		// Time to go back.
		redirectexit('action=manageportal;area=portalarticles;sa=articles');
	}
}

// Function for editing an article.
function sportal_admin_article_edit()
{
	global $txt, $db_prefix, $context;
	global $func;

	// Seems that we aren't ready.
	if(empty($_POST['add_article'])) {

		// Check it as we just accept integer.
		$_REQUEST['article_id'] = (int)$_REQUEST['article_id'];

		// Do we know the one to be edited?
		if(empty($_REQUEST['article_id']))
			fatal_lang_error('error_sp_id_empty', false);

		// Get the article info.
		$context['article_info'] = getArticleInfo($_REQUEST['article_id']);
		$context['article_info'] = $context['article_info'][0];

		// List all the categories.
		$context['list_categories'] = getCategoryInfo();

		// Call the right template.
		$context['page_title'] = $txt['sp-articlesEdit'];
		$context['sub_template'] = 'article_edit';
	}
	else {

		// Verify the session.
		checkSession();

		// A small array.
		$articleInfo = array(
			'category' => $_POST['category'],
			'approved' => empty($_POST['approved']) ? '0' : '1',
		);

		// Set the fields to change.
		$category_fields = array();
		$category_fields[] = "ID_CATEGORY = '$articleInfo[category]'";
		$category_fields[] = "approved = '$articleInfo[approved]'";

		// Do it please.
		db_query("
			UPDATE {$db_prefix}sp_articles
			SET " . implode(', ', $category_fields) . "
			WHERE ID_ARTICLE = $_POST[article_id]
			LIMIT 1", __FILE__, __LINE__);

		// Fix the article counts.
		fixCategoryArticles();

		// I wanna go back to the list. :)
		redirectexit('action=manageportal;area=portalarticles;sa=articles');
	}
}

// Deleting an article...
function sportal_admin_article_delete()
{
	global $db_prefix, $context;

	// Check if he can?
	checkSession('get');

	// We just accept integers.
	$_REQUEST['article_id'] = (int)$_REQUEST['article_id'];

	// Can't delete without an ID.
	if(empty($_REQUEST['article_id']))
		fatal_lang_error('error_sp_id_empty', false);

	// Life is short... Delete it.
	db_query("
		DELETE FROM {$db_prefix}sp_articles
		WHERE ID_ARTICLE = '$_REQUEST[article_id]'
		LIMIT 1", __FILE__, __LINE__);

	// Fix the article counts.
	fixCategoryArticles();

	// Again comes the list.
	redirectexit('action=manageportal;area=portalarticles;sa=articles');
}

// Gets the category list.
function sportal_admin_category_list()
{
	global $txt, $context;

	// Category list columns.
	$context['columns'] = array(
		'picture' => array(
			'width' => '35%',
			'label' => $txt['sp-adminColumnPicture'],
		),
		'name' => array(
			'width' => '45%',
			'label' => $txt['sp-adminColumnName'],
		),
		'articles' => array(
			'width' => '5%',
			'label' => $txt['sp-adminColumnArticles'],
		),
		'publish' => array(
			'width' => '5%',
			'label' => $txt['sp-adminColumnPublish'],
		),
		'action' => array(
			'width' => '10%',
			'label' => $txt['sp-adminColumnAction'],
		),
	);

	// Get all the categories.
	$context['categories'] = getCategoryInfo();

	// Call the sub template.
	$context['sub_template'] = 'category_list';
	$context['page_title'] = $txt['sp-adminCategoryListName'];
}

// Function for adding a category.
function sportal_admin_category_add()
{
	global $txt, $db_prefix, $context, $func;

	// Not now...
	if(empty($_POST['add_category'])) {

		// Just we need the template.
		$context['sub_template'] = 'category_add';
		$context['page_title'] = $txt['sp-categoriesAdd'];
	}
	else {

		// Session check.
		checkSession();

		// Category name can't be empty.
		if (empty($_POST['category_name']))
			fatal_lang_error('error_sp_name_empty', false);

		// A small info array.
		$categoryInfo = array(
			'name' => addslashes($func['htmlspecialchars'](stripslashes($_POST['category_name']), ENT_QUOTES)),
			'picture' => addslashes($func['htmlspecialchars'](stripslashes($_POST['picture_url']), ENT_QUOTES)),
			'publish' => empty($_POST['show_on_index']) ? '0' : '1',
		);

		// Here we go!
		db_query("
			INSERT INTO {$db_prefix}sp_categories
				(name, picture, articles, publish)
			VALUES ('$categoryInfo[name]', '$categoryInfo[picture]', 0, $categoryInfo[publish])", __FILE__, __LINE__);

		// Return back to the category list.
		redirectexit('action=manageportal;area=portalarticles;sa=categories');
	}
}

// Handles the category edit issue.
function sportal_admin_category_edit()
{
	global $txt, $db_prefix, $context, $func;

	// Time to edit? Noo!
	if(empty($_POST['add_category'])) {

		// Be sure you made it an integer.
		$_REQUEST['category_id'] = (int)$_REQUEST['category_id'];

		// Show you ID.
		if(empty($_REQUEST['category_id']))
			fatal_lang_error('error_sp_id_empty', false);

		// Get the category info. You need in template.
		$context['category_info'] = getCategoryInfo($_REQUEST['category_id']);
		$context['category_info'] = $context['category_info'][0];

		// Call the right sub template.
		$context['sub_template'] = 'category_edit';
		$context['page_title'] = $txt['sp-categoriesEdit'];
	}
	else {

		// Again.
		checkSession();

		// Why empty? :S
		if (empty($_POST['category_name']))
			fatal_lang_error('error_sp_name_empty', false);

		// Array for the db.
		$categoryInfo = array(
			'name' => addslashes($func['htmlspecialchars'](stripslashes($_POST['category_name']), ENT_QUOTES)),
			'picture' => addslashes($func['htmlspecialchars'](stripslashes($_POST['picture_url']), ENT_QUOTES)),
			'publish' => empty($_POST['show_on_index']) ? '0' : '1',
		);

		// What to change?
		$category_fields = array();
		$category_fields[] = "name = '$categoryInfo[name]'";
		$category_fields[] = "picture = '$categoryInfo[picture]'";
		$category_fields[] = "publish = '$categoryInfo[publish]'";

		// Go on.
		db_query("
			UPDATE {$db_prefix}sp_categories
			SET " . implode(', ', $category_fields) . "
			WHERE ID_CATEGORY = $_POST[category_id]
			LIMIT 1", __FILE__, __LINE__);

		// Take him back to the list.
		redirectexit('action=manageportal;area=portalarticles;sa=categories');
	}
}

// Does more than deleting...
function sportal_admin_category_delete()
{
	global $db_prefix, $context, $txt;

	// Check if the ID is set.
	if(!empty($_REQUEST['category_id'])) {

		// Be sure you made it an integer.
		$_REQUEST['category_id'] = (int) $_REQUEST['category_id'];

		// Do you know which one to delete?
		if(empty($_REQUEST['category_id']))
			fatal_lang_error('error_sp_id_empty', false);

		// Get the category info. You need in template.
		$context['category_info'] = getCategoryInfo($_REQUEST['category_id']);
		$context['category_info'] = $context['category_info'][0];

		// Also get the category list.
		$context['list_categories'] = getCategoryInfo();

		// If we have one, that is itself. Delete it.
		if(count($context['list_categories']) < 2)
			$context['list_categories'] = array();
	}

	if(empty($_REQUEST['category_id']) && empty($_POST['category_id']))
		fatal_lang_error('error_sp_id_empty', false);

	// No need if category has no articles. But articles are executed if there isn't any other category. :P
	if(empty($_POST['delete_category']) && !empty($context['category_info']['articles'])) {

		// Call the right sub template.
		$context['sub_template'] = 'category_delete';
		$context['page_title'] = $txt['sp-categoriesDelete'];
	}
	elseif(!empty($_POST['delete_category'])) {

		// Again.
		checkSession();

		// Are we going to move something?
		if(!empty($_POST['category_move']) && !empty($_POST['category_move_to'])) {

			// We just need an integer.
			$_POST['category_move_to'] = (int) $_POST['category_move_to'];

			// These are the lucky ones, move them.
			db_query("
				UPDATE {$db_prefix}sp_articles
				SET ID_CATEGORY = '$_POST[category_move_to]'
				WHERE ID_CATEGORY = '$_POST[category_id]'", __FILE__, __LINE__);

			// Fix the article counts.
			fixCategoryArticles();
		}
		else {

			// Kill 'em all. (It's not the Metallica album. :P)
			db_query("
				DELETE FROM {$db_prefix}sp_articles
				WHERE ID_CATEGORY = '$_POST[category_id]'", __FILE__, __LINE__);
		}

		// Everybody will die one day...
		db_query("
			DELETE FROM {$db_prefix}sp_categories
			WHERE ID_CATEGORY = '$_POST[category_id]'
			LIMIT 1", __FILE__, __LINE__);

		// Return to the list.
		redirectexit('action=manageportal;area=portalarticles;sa=categories');
	}
	else {

		// Again.
		checkSession('get');

		// Just delete the category.
		db_query("
			DELETE FROM {$db_prefix}sp_categories
			WHERE ID_CATEGORY = '$_REQUEST[category_id]'
			LIMIT 1", __FILE__, __LINE__);

		// Fix the article counts.
		fixCategoryArticles();

		// Return to the list.
		redirectexit('action=manageportal;area=portalarticles;sa=categories');
	}
}

?>