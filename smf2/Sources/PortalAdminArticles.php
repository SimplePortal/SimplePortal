<?php

/**
 * @package SimplePortal
 *
 * @author SimplePortal Team
 * @copyright 2014 SimplePortal Team
 * @license BSD 3-clause
 *
 * @version 2.3.7
 */

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
	global $context, $txt, $sourcedir;

	if (!allowedTo('sp_admin'))
		isAllowedTo('sp_manage_articles');

	require_once($sourcedir . '/Subs-PortalAdmin.php');

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

	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $txt['sp-adminCatTitle'],
		'help' => 'sp_ArticlesArea',
		'description' => $txt['sp-adminCatDesc'],
		'tabs' => array(
			'articles' => array(
				'description' => $txt['sp-adminArticleListDesc'],
			),
			'addarticle' => array(
				'description' => $txt['sp-adminArticleAddDesc'],
			),
			'categories' => array(
				'description' => $txt['sp-adminCategoryListDesc'],
			),
			'addcategory' => array(
				'description' => $txt['sp-adminCategoryAddDesc'],
			),
		),
	);

	$subActions[$_REQUEST['sa']]();
}

// Function to 'Show' a list of Articles, and allow 'quick' deletion of them.
function sportal_admin_article_list()
{
	global $txt, $smcFunc, $context, $article_request, $scripturl;

	// Call the template.
	$context['sub_template'] = 'article_list';

	// You clicked the remove button? Naughty boy. :P
	if (!empty($_POST['removeArticles']) && !empty($_POST['remove']) && is_array($_POST['remove']))
	{
		// Are you even allowed to be here?
		checkSession();

		// Sanitize the articles to remove non integers.
		foreach ($_POST['remove'] as $index => $article_id)
			$_POST['remove'][(int) $index] = (int) $article_id;

		// Delete the required articles.
		$smcFunc['db_query']('','
			DELETE FROM {db_prefix}sp_articles
			WHERE id_article IN ({array_int:remove})',
			array(
				'remove' => $_POST['remove'],
			)
		);

		// Fix the category article count.
		fixCategoryArticles();
	}

	// How can we sort the list of articles?
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
			'down' => 'm.poster_name ASC',
			'up' => 'm.poster_name DESC'
		),
		'time' => array(
			'down' => 'm.poster_time ASC',
			'up' => 'm.poster_time DESC'
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
			'class' => 'first_th',
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
			'width' => '17%',
			'label' => $txt['sp-adminColumnTime'],
			'sortable' => true
		),
		'category' => array(
			'width' => '20%',
			'label' => $txt['sp-adminColumnCategory'],
			'sortable' => true
		),
		'approved' => array(
			'width' => '8%',
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

	// Setup the sort links.
	foreach ($context['columns'] as $col => $dummy)
	{
		$context['columns'][$col]['selected'] = $col == $_REQUEST['sort'];
		$context['columns'][$col]['href'] = $scripturl . '?action=admin;area=portalarticles;sa=articles;sort=' . $col;

		if (!isset($_REQUEST['desc']) && $col == $_REQUEST['sort'])
			$context['columns'][$col]['href'] .= ';desc';

		$context['columns'][$col]['link'] = '<a href="' . $context['columns'][$col]['href'] . '">' . $context['columns'][$col]['label'] . '</a>';
	}

	$context['sort_by'] = $_REQUEST['sort'];
	$context['sort_direction'] = !isset($_REQUEST['desc']) ? 'down' : 'up';

	// Count all the articles.
	$request = $smcFunc['db_query']('','
		SELECT COUNT(*)
		FROM {db_prefix}sp_articles AS a
			INNER JOIN {db_prefix}messages AS m ON (m.id_msg = a.id_message)
			INNER JOIN {db_prefix}boards AS b ON (b.id_board = m.id_board)
		WHERE {query_see_board}'
	);
	list ($context['total_articles']) =  $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	// Construct the page index. 20 articles per page.
	$context['page_index'] = constructPageIndex($scripturl . '?action=admin;area=portalarticles;sa=articles;sort=' . $_REQUEST['sort'] . (isset($_REQUEST['desc']) ? ';desc' : ''), $_REQUEST['start'], $context['total_articles'], 20);
	$context['start'] = $_REQUEST['start'];

	// A *small* query to get article info.
	$article_request = $smcFunc['db_query']('','
		SELECT a.id_article, a.id_category, a.id_message, a.approved, c.name as cname, m.id_member, m.poster_name,
			m.poster_time, m.subject, t.id_topic, t.num_replies, t.num_views, b.id_board, b.name as bname, mem.real_name
		FROM {db_prefix}sp_articles AS a
			INNER JOIN {db_prefix}sp_categories AS c ON (c.id_category = a.id_category)
			INNER JOIN {db_prefix}messages AS m ON (m.id_msg = a.id_message)
			INNER JOIN {db_prefix}topics AS t ON (t.id_first_msg = a.id_message)
			INNER JOIN {db_prefix}boards AS b ON (b.id_board = m.id_board)
			LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = m.id_member)
		WHERE {query_see_board}
		ORDER BY {raw:sort}
		LIMIT {int:start}, {int:limit}',
		array(
			'sort' => $sort_methods[$_REQUEST['sort']][$context['sort_direction']],
			'start' => $context['start'],
			'limit' => 20,
		)
	);

	// Call-back...
	$context['get_article'] = 'sportal_admin_articles_callback';
	$context['page_title'] = $txt['sp-adminArticleListName'];
}

// Call-back for getting a row of article data.
function sportal_admin_articles_callback($reset = false)
{
	global $scripturl, $article_request, $txt, $context, $smcFunc;

	if ($article_request == false)
		return false;

	if (!($row = $smcFunc['db_fetch_assoc']($article_request)))
		return false;

	// Build up the array.
	$output = array(
		'article' => array(
			'id' => $row['id_article'],
			'approved' => $row['approved'],
		),
		'category' => array(
			'id' => $row['id_category'],
			'name' => '<a href="' . $scripturl . '?action=admin;area=portalarticles;sa=editcategory;category_id=' . $row['id_category'] . '">'.$row['cname'].'</a>',
		),
		'message' => array(
			'id' => $row['id_message'],
			'subject' => $row['subject'],
			'time' => timeformat($row['poster_time'], '%H:%M:%S, %d/%m/%y'),
		),
		'poster' => array(
			'id' => $row['id_member'],
			'name' => $row['poster_name'],
			'link' => !empty($row['id_member']) ? '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['real_name'] . '</a>' : $row['poster_name'],
		),
		'topic' => array(
			'id' => $row['id_topic'],
			'replies' => $row['num_replies'],
			'views' => $row['num_views'],
			'link' => '<a href="' . $scripturl . '?topic=' . $row['id_topic'] . '.0">' . $row['subject'] . '</a>',
		),
		'board' => array(
			'id' => $row['id_board'],
			'name' => $row['bname'],
			'link' => '<a href="' . $scripturl . '?board=' . $row['id_board'] . '.0">' . $row['bname'] . '</a>',
		),
		'edit' => '<a href="' . $scripturl . '?action=admin;area=portalarticles;sa=editarticle;article_id=' . $row['id_article'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '">' . sp_embed_image('modify') . '</a>',
		'delete' => '<a href="' . $scripturl . '?action=admin;area=portalarticles;sa=deletearticle;article_id=' . $row['id_article'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '" onclick="return confirm(\'' . $txt['sp-articlesDeleteConfirm'] . '\');">' . sp_embed_image('delete') . '</a>'
	);

	return $output;
}

// Function for adding articles.
function sportal_admin_article_add()
{
	global $txt, $context, $scripturl, $smcFunc, $modSettings;

	// Are we ready?
	if(empty($_POST['createArticle']) || empty($_POST['articles']))
	{
		// List all the categories.
		$context['list_categories'] = getCategoryInfo();

		// Do we have any category to add?
		if(empty($context['list_categories']))
			fatal_error($txt['error_sp_no_category'] . '<br />' . sprintf($txt['error_sp_no_category_sp_moderator'], $scripturl . '?action=admin;area=portalarticles;sa=addcategory'), false);

		// Which board to show?
		if(isset($_REQUEST['targetboard']))
			$_REQUEST['targetboard'] = (int) $_REQUEST['targetboard'];
		else
		{
			// Find one yourself.
			$request = $smcFunc['db_query']('','
				SELECT b.id_board
				FROM {db_prefix}boards AS b
				WHERE b.redirect = \'\'
					AND {query_see_board}
				ORDER BY b.id_board DESC
				LIMIT 1'
			);
			list ($_REQUEST['targetboard']) = $smcFunc['db_fetch_row']($request);
			$smcFunc['db_free_result']($request);
		}

		$context['target_board'] = $_REQUEST['targetboard'];

		// Get the total topic count.
		$request = $smcFunc['db_query']('','
			SELECT COUNT(*)
			FROM {db_prefix}topics as t
				INNER JOIN {db_prefix}boards AS b ON (b.id_board = t.id_board)
				LEFT JOIN {db_prefix}sp_articles as a ON (a.id_message = t.id_first_msg)
			WHERE t.id_board = {int:targetboard}
				AND IFNULL(a.id_article, 0) = 0
				AND {query_see_board}',
			array(
				'targetboard' => $_REQUEST['targetboard'],
			)
		);
		list ($topiccount) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);

		// Create the page index.
		$context['page_index'] = constructPageIndex($scripturl . '?action=admin;area=portalarticles;sa=addarticle;targetboard=' . $_REQUEST['targetboard'] . ';board=' . $_REQUEST['targetboard'] . '.%d', $_REQUEST['start'], $topiccount, $modSettings['defaultMaxTopics'], true);

		// Get some info about the boards and categories.
		$request = $smcFunc['db_query']('','
			SELECT b.id_board, b.name AS bName, c.name AS cName
			FROM {db_prefix}boards AS b
				LEFT JOIN {db_prefix}categories AS c ON (c.id_cat = b.id_cat)
			WHERE b.redirect = \'\'
				AND {query_see_board}'
		);
		$context['boards'] = array();
		while ($row = $smcFunc['db_fetch_assoc']($request))
			$context['boards'][] = array(
				'id' => $row['id_board'],
				'name' => $row['bName'],
				'category' => $row['cName']
			);
		$smcFunc['db_free_result']($request);

		// Time to get the topic data.
		$request = $smcFunc['db_query']('','
			SELECT t.id_topic, m.subject, m.id_member, IFNULL(mem.real_name, m.poster_name) AS poster_name, m.id_msg
			FROM {db_prefix}topics AS t
				INNER JOIN {db_prefix}messages AS m ON (m.id_msg = t.id_first_msg)
				INNER JOIN {db_prefix}boards AS b ON (b.id_board = m.id_board)
				LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = m.id_member)
				LEFT JOIN {db_prefix}sp_articles as a ON (a.id_message = t.id_first_msg)
			WHERE IFNULL(a.id_article, 0) = {int:article}
				AND t.id_board = {int:targetboard}
				AND {query_see_board}
			ORDER BY ' . (!empty($modSettings['enableStickyTopics']) ? 't.is_sticky DESC, ' : '') . 't.id_last_msg DESC
			LIMIT {int:start}, {int:max}',
			array(
				'article' => 0,
				'targetboard' => $_REQUEST['targetboard'],
				'start' => $_REQUEST['start'],
				'max' => $modSettings['defaultMaxTopics'],
			)
		);
		$context['topics'] = array();
		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			censorText($row['subject']);

			$context['topics'][] = array(
				'id' => $row['id_topic'],
				'msg_id' => $row['id_msg'],
				'poster' => array(
					'id' => $row['id_member'],
					'name' => $row['poster_name'],
					'href' => empty($row['id_member']) ? '' : $scripturl . '?action=profile;u=' . $row['id_member'],
					'link' => empty($row['id_member']) ? $row['poster_name'] : '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '" target="_blank">' . $row['poster_name'] . '</a>'
				),
				'subject' => $row['subject'],
				'js_subject' => addcslashes(addslashes($row['subject']), '/')
			);
		}
		$smcFunc['db_free_result']($request);

		// Set the page title and sub-template.
		$context['page_title'] = $txt['sp-articlesAdd'];
		$context['sub_template'] = 'article_add';
	}
	else
	{
		// But can you?
		checkSession();

		// Are they integer?
		foreach ($_POST['articles'] as $index => $article_id)
			$_POST['articles'][(int) $index] = (int) $article_id;

		// Add all of them.
		foreach($_POST['articles'] as $article) {

			// Set them. They have their own IDs.
			$articleOptions = array(
				'id_category' => !empty($_POST['category']) ? (int) $_POST['category'] : 0,
				'id_message' => $article,
				'approved' => 1,
			);

			// A tricky function.
			createArticle($articleOptions);
		}

		// Time to go back.
		redirectexit('action=admin;area=portalarticles;sa=articles');
	}
}

// Function for editing an article.
function sportal_admin_article_edit()
{
	global $txt, $smcFunc, $context;

	// Seems that we aren't ready.
	if(empty($_POST['add_article']))
	{
		// Check it as we just accept integer.
		$_REQUEST['article_id'] = (int) $_REQUEST['article_id'];

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
	else
	{
		// Verify the session.
		checkSession();

		// A small array.
		$articleInfo = array(
			'category' => $_POST['category'],
			'approved' => empty($_POST['approved']) ? '0' : '1',
		);

		// Do it please.
		$smcFunc['db_query']('','
			UPDATE {db_prefix}sp_articles
			SET id_category = {int:category}, approved = {int:approved}
			WHERE id_article = {int:id}',
			array(
				'id' => $_POST['article_id'],
				'category' => $articleInfo['category'],
				'approved' => $articleInfo['approved'],
			)
		);

		// Fix the article counts.
		fixCategoryArticles();

		// I wanna go back to the list. :)
		redirectexit('action=admin;area=portalarticles;sa=articles');
	}
}

// Deleting an article...
function sportal_admin_article_delete()
{
	global $smcFunc;

	// Check if he can?
	checkSession('get');

	// We just accept integers.
	$_REQUEST['article_id'] = (int) $_REQUEST['article_id'];

	// Can't delete without an ID.
	if(empty($_REQUEST['article_id']))
		fatal_lang_error('error_sp_id_empty', false);

	// Life is short... Delete it.
	$smcFunc['db_query']('','
		DELETE FROM {db_prefix}sp_articles
		WHERE id_article = {int:id}',
		array(
			'id' => $_REQUEST['article_id'],
		)
	);

	// Fix the article counts.
	fixCategoryArticles();

	// Again comes the list.
	redirectexit('action=admin;area=portalarticles;sa=articles');
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
			'class' => 'first_th',
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
			'class' => 'last_th',
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
	global $txt, $smcFunc, $context;

	// Not actually adding a category? Show the add category page.
	if(empty($_POST['edit_category']))
	{
		// Just we need the template.
		$context['sub_template'] = 'category_edit';
		$context['page_title'] = $txt['sp-categoriesAdd'];
		$context['category_action'] = 'add';
	}
	// Adding a category? Lets do this thang! ;D
	else
	{
		// Session check.
		checkSession();

		// Category name can't be empty.
		if (empty($_POST['category_name']))
			fatal_lang_error('error_sp_name_empty', false);

		// A small info array.
		$categoryInfo = array(
			'name' => $smcFunc['htmlspecialchars']($_POST['category_name'], ENT_QUOTES),
			'picture' => $smcFunc['htmlspecialchars']($_POST['picture_url'], ENT_QUOTES),
			'publish' => empty($_POST['show_on_index']) ? '0' : '1',
		);

		// Insert the category data.
		$smcFunc['db_insert']('normal', '{db_prefix}sp_categories',
			// Columns to insert.
			array(
				'name' => 'string',
				'picture' => 'string',
				'articles' => 'int',
				'publish' => 'int'
			),
			// Data to put in.
			array(
				'name' => $categoryInfo['name'],
				'picture' => $categoryInfo['picture'],
				'articles' => 0,
				'publish' => $categoryInfo['publish']
			),
			// We had better tell SMF about the key, even though I can't remember why? ;)
			array('id_category')
		);

		// Return back to the category list.
		redirectexit('action=admin;area=portalarticles;sa=categories');
	}
}

// Handles the category edit issue.
function sportal_admin_category_edit()
{
	global $txt, $smcFunc, $context;

	// Not Time to edit? Show the cagegory edit page.
	if(empty($_POST['edit_category']))
	{
		// Be sure you made it an integer.
		$_REQUEST['category_id'] = (int) $_REQUEST['category_id'];

		// Show you ID.
		if(empty($_REQUEST['category_id']))
			fatal_lang_error('error_sp_id_empty', false);

		// Get the category info. You need in template.
		$context['category_info'] = getCategoryInfo($_REQUEST['category_id']);
		$context['category_info'] = $context['category_info'][0];

		// Call the right sub template.
		$context['sub_template'] = 'category_edit';
		$context['page_title'] = $txt['sp-categoriesEdit'];
		$context['category_action'] = 'edit';
	}
	// Perform the actual edits.
	else
	{
		// Again.
		checkSession();

		// Why empty? :S
		if (empty($_POST['category_name']))
			fatal_lang_error('error_sp_name_empty', false);

		// Array for the db.
		$categoryInfo = array(
			'name' => $smcFunc['htmlspecialchars']($_POST['category_name'], ENT_QUOTES),
			'picture' => $smcFunc['htmlspecialchars']($_POST['picture_url'], ENT_QUOTES),
			'publish' => empty($_POST['show_on_index']) ? '0' : '1',
		);

		// What to change?
		$category_fields = array();
		$category_fields[] = "name = {string:name}";
		$category_fields[] = "picture = {string:picture}";
		$category_fields[] = "publish = {int:publish}";

		// Go on.
		$smcFunc['db_query']('','
			UPDATE {db_prefix}sp_categories
			SET ' . implode(', ', $category_fields) . '
			WHERE id_category = {int:id}',
			array(
				'id' => $_POST['category_id'],
				'name' => $categoryInfo['name'],
				'picture' => $categoryInfo['picture'],
				'publish' => $categoryInfo['publish'],
			)
		);

		// Take him back to the list.
		redirectexit('action=admin;area=portalarticles;sa=categories');
	}
}

// Does more than deleting...
function sportal_admin_category_delete()
{
	global $smcFunc, $context, $txt;

	// Is an id set? If yes, then we need to get some category information.
	if(!empty($_REQUEST['category_id']))
	{
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

	// No need to delete articles if category has no articles. But articles are executed if there isn't any other category. :P
	if(empty($_POST['delete_category']) && !empty($context['category_info']['articles']))
	{
		// Call the right sub template.
		$context['sub_template'] = 'category_delete';
		$context['page_title'] = $txt['sp-categoriesDelete'];
	}
	elseif(!empty($_POST['delete_category']))
	{
		// Again.
		checkSession();

		// Are we going to move something?
		if(!empty($_POST['category_move']) && !empty($_POST['category_move_to'])) {

			// We just need an integer.
			$_POST['category_move_to'] = (int) $_POST['category_move_to'];

			// These are the lucky ones, move them.
			$smcFunc['db_query']('','
				UPDATE {db_prefix}sp_articles
				SET id_category = {int:category_move_to}
				WHERE id_category = {int:category_id}',
				array(
					'category_move_to' => $_POST['category_move_to'],
					'category_id' => $_POST['category_id'],
				)
			);

			// Fix the article counts.
			fixCategoryArticles();
		}
		else
		{
			// Kill 'em all. (It's not the Metallica album. :P)
			$smcFunc['db_query']('','
				DELETE FROM {db_prefix}sp_articles
				WHERE id_category = {int:category_id}',
				array(
					'category_id' => $_POST['category_id'],
				)
			);
		}

		// Everybody will die one day...
		$smcFunc['db_query']('','
			DELETE FROM {db_prefix}sp_categories
			WHERE id_category = {int:category_id}',
			array(
				'category_id' => $_POST['category_id'],
			)
		);

		// Return to the list.
		redirectexit('action=admin;area=portalarticles;sa=categories');
	}
	else
	{
		// Again.
		checkSession('get');

		// Just delete the category.
		$smcFunc['db_query']('','
			DELETE FROM {db_prefix}sp_categories
			WHERE id_category = {int:category_id}',
			array(
				'category_id' => $_REQUEST['category_id'],
			)
		);

		// Fix the article counts.
		fixCategoryArticles();

		// Return to the list.
		redirectexit('action=admin;area=portalarticles;sa=categories');
	}
}

?>