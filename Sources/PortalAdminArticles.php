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

/**
 * Entry point for SimplePortal Article
 * Checks permissions, passes off processing to the appropriate function
 */
function sportal_admin_articles_main()
{
	global $context, $sourcedir, $txt;

	if (!allowedTo('sp_admin'))
		isAllowedTo('sp_manage_articles');

	require_once($sourcedir . '/Subs-PortalAdmin.php');

	loadTemplate('PortalAdminArticles');

	$sub_actions = array(
		'list' => 'sportal_admin_article_list',
		'add' => 'sportal_admin_article_edit',
		'edit' => 'sportal_admin_article_edit',
		'status' => 'sportal_admin_article_status',
		'delete' => 'sportal_admin_article_delete',
	);

	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($sub_actions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'list';

	$context['sub_action'] = $_REQUEST['sa'];

	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $txt['sp_admin_articles_title'],
		'help' => 'sp_ArticlesArea',
		'description' => $txt['sp_admin_articles_desc'],
		'tabs' => array(
			'list' => array(
			),
			'add' => array(
			),
		),
	);

	$sub_actions[$context['sub_action']]();
}

/**
 * Show a listing of articles in the system
 */
function sportal_admin_article_list()
{
	global $smcFunc, $context, $scripturl, $txt;

	if (!empty($_POST['remove_articles']) && !empty($_POST['remove']) && is_array($_POST['remove']))
	{
		checkSession();

		foreach ($_POST['remove'] as $index => $article_id)
			$_POST['remove'][(int) $index] = (int) $article_id;

		$smcFunc['db_query']('','
			DELETE FROM {db_prefix}sp_articles
			WHERE id_article IN ({array_int:articles})',
			array(
				'articles' => $_POST['remove'],
			)
		);
	}

	$sort_methods = array(
		'title' =>  array(
			'down' => 'spa.title ASC',
			'up' => 'spa.title DESC'
		),
		'namespace' =>  array(
			'down' => 'article_namespace ASC',
			'up' => 'article_namespace DESC'
		),
		'category' =>  array(
			'down' => 'spc.name ASC',
			'up' => 'spc.name DESC'
		),
		'author' => array(
			'down' => 'author_name ASC',
			'up' => 'author_name DESC'
		),
		'type' => array(
			'down' => 'spa.type ASC',
			'up' => 'spa.type DESC'
		),
		'date' => array(
			'down' => 'spa.date ASC',
			'up' => 'spa.date DESC'
		),
		'status' => array(
			'down' => 'spa.status ASC',
			'up' => 'spa.status DESC'
		),
	);

	$context['columns'] = array(
		'title' => array(
			'width' => '22%',
			'label' => $txt['sp_admin_articles_col_title'],
			'class' => 'first_th',
			'sortable' => true
		),
		'namespace' => array(
			'width' => '14%',
			'label' => $txt['sp_admin_articles_col_namespace'],
			'sortable' => true
		),
		'category' => array(
			'width' => '10%',
			'label' => $txt['sp_admin_articles_col_category'],
			'sortable' => true
		),
		'author' => array(
			'width' => '10%',
			'label' => $txt['sp_admin_articles_col_author'],
			'sortable' => true
		),
		'type' => array(
			'width' => '8%',
			'label' => $txt['sp_admin_articles_col_type'],
			'sortable' => true
		),
		'date' => array(
			'width' => '19%',
			'label' => $txt['sp_admin_articles_col_date'],
			'sortable' => true
		),
		'status' => array(
			'width' => '6%',
			'label' => $txt['sp_admin_articles_col_status'],
			'sortable' => true
		),
		'actions' => array(
			'width' => '10%',
			'label' => $txt['sp_admin_articles_col_actions'],
			'sortable' => false
		),
	);

	if (!isset($_REQUEST['sort']) || !isset($sort_methods[$_REQUEST['sort']]))
		$_REQUEST['sort'] = 'title';

	foreach ($context['columns'] as $col => $dummy)
	{
		$context['columns'][$col]['selected'] = $col == $_REQUEST['sort'];
		$context['columns'][$col]['href'] = $scripturl . '?action=admin;area=portalarticles;sa=list;sort=' . $col;

		if (!isset($_REQUEST['desc']) && $col == $_REQUEST['sort'])
			$context['columns'][$col]['href'] .= ';desc';

		$context['columns'][$col]['link'] = '<a href="' . $context['columns'][$col]['href'] . '">' . $context['columns'][$col]['label'] . '</a>';
	}

	$context['sort_by'] = $_REQUEST['sort'];
	$context['sort_direction'] = !isset($_REQUEST['desc']) ? 'down' : 'up';

	$request = $smcFunc['db_query']('','
		SELECT COUNT(*)
		FROM {db_prefix}sp_articles'
	);
	list ($total_articles) =  $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	$context['page_index'] = constructPageIndex($scripturl . '?action=admin;area=portalarticles;sa=list;sort=' . $_REQUEST['sort'] . (isset($_REQUEST['desc']) ? ';desc' : ''), $_REQUEST['start'], $total_articles, 20);
	$context['start'] = $_REQUEST['start'];

	$request = $smcFunc['db_query']('','
		SELECT
			spa.id_article, spa.id_category, spc.name, spc.namespace AS category_namespace,
			IFNULL(m.id_member, 0) AS id_author, IFNULL(m.real_name, spa.member_name) AS author_name,
			spa.namespace AS article_namespace, spa.title, spa.type, spa.date, spa.status
		FROM {db_prefix}sp_articles AS spa
			INNER JOIN {db_prefix}sp_categories AS spc ON (spc.id_category = spa.id_category)
			LEFT JOIN {db_prefix}members AS m ON (m.id_member = spa.id_member)
		ORDER BY {raw:sort}
		LIMIT {int:start}, {int:limit}',
		array(
			'sort' => $sort_methods[$_REQUEST['sort']][$context['sort_direction']],
			'start' => $context['start'],
			'limit' => 20,
		)
	);
	$context['articles'] = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$context['articles'][$row['id_article']] = array(
			'id' => $row['id_article'],
			'article_id' => $row['article_namespace'],
			'title' => $row['title'],
			'href' => $scripturl . '?article=' . $row['article_namespace'],
			'link' => '<a href="' . $scripturl . '?article=' . $row['article_namespace'] . '">' . $row['title'] . '</a>',
			'category' => array(
				'id' => $row['id_category'],
				'name' => $row['name'],
				'href' => $scripturl . '?category=' . $row['category_namespace'],
				'link' => '<a href="' . $scripturl . '?category=' . $row['category_namespace'] . '">' . $row['name'] . '</a>',
			),
			'author' => array(
				'id' => $row['id_author'],
				'name' => $row['author_name'],
				'href' => $scripturl . '?action=profile;u=' . $row['id_author'],
				'link' => $row['id_author'] ? ('<a href="' . $scripturl . '?action=profile;u=' . $row['id_author'] . '">' . $row['author_name'] . '</a>') : $row['author_name'],
			),
			'type' => $row['type'],
			'type_text' => $txt['sp_articles_type_'. $row['type']],
			'date' => timeformat($row['date']),
			'status' => $row['status'],
			'status_image' => '<a href="' . $scripturl . '?action=admin;area=portalarticles;sa=status;article_id=' . $row['id_article'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '">' . sp_embed_image(empty($row['status']) ? 'deactive' : 'active', $txt['sp_admin_articles_' . (!empty($row['status']) ? 'de' : '') . 'activate']) . '</a>',
			'actions' => array(
				'edit' => '<a href="' . $scripturl . '?action=admin;area=portalarticles;sa=edit;article_id=' . $row['id_article'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '">' . sp_embed_image('modify') . '</a>',
				'delete' => '<a href="' . $scripturl . '?action=admin;area=portalarticles;sa=delete;article_id=' . $row['id_article'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '" onclick="return confirm(\'', $txt['sp_admin_articles_delete_confirm'], '\');">' . sp_embed_image('delete') . '</a>',
			)
		);
	}
	$smcFunc['db_free_result']($request);

	$context['sub_template'] = 'articles_list';
	$context['page_title'] = $txt['sp_admin_articles_list'];
}

/**
 * Edits an existing or adds a new article to the system
 * Handles the previewing of an article
 */
function sportal_admin_article_edit()
{
	global $smcFunc, $context, $sourcedir, $scripturl, $modSettings, $user_info, $options, $txt;

	require_once($sourcedir . '/Subs-Editor.php');
	require_once($sourcedir . '/Subs-Post.php');

	$context['is_new'] = empty($_REQUEST['article_id']);

	if (!empty($_REQUEST['content_mode']) && $_POST['type'] == 'bbc')
	{
		$_REQUEST['content'] = html_to_bbc($_REQUEST['content']);
		$_REQUEST['content'] = un_htmlspecialchars($_REQUEST['content']);
		$_POST['content'] = $_REQUEST['content'];
	}

	if (!empty($_POST['submit']))
	{
		checkSession();

		if (!$context['is_new'])
		{
			$_REQUEST['article_id'] = (int) $_REQUEST['article_id'];
			$context['article'] = sportal_get_articles($_REQUEST['article_id']);
		}

		if (!isset($_POST['title']) || $smcFunc['htmltrim']($smcFunc['htmlspecialchars']($_POST['title'], ENT_QUOTES)) === '')
			fatal_lang_error('sp_error_article_name_empty', false);

		if (!isset($_POST['namespace']) || $smcFunc['htmltrim']($smcFunc['htmlspecialchars']($_POST['namespace'], ENT_QUOTES)) === '')
			fatal_lang_error('sp_error_article_namespace_empty', false);

		$result = $smcFunc['db_query']('','
			SELECT id_article
			FROM {db_prefix}sp_articles
			WHERE namespace = {string:namespace}
				AND id_article != {int:current}
			LIMIT 1',
			array(
				'limit' => 1,
				'namespace' => $smcFunc['htmlspecialchars']($_POST['namespace'], ENT_QUOTES),
				'current' => (int) $_POST['article_id'],
			)
		);
		list ($has_duplicate) = $smcFunc['db_fetch_row']($result);
		$smcFunc['db_free_result']($result);

		if (!empty($has_duplicate))
			fatal_lang_error('sp_error_article_namespace_duplicate', false);

		if (preg_match('~[^A-Za-z0-9_]+~', $_POST['namespace']) != 0)
			fatal_lang_error('sp_error_article_namespace_invalid_chars', false);

		if (preg_replace('~[0-9]+~', '', $_POST['namespace']) === '')
			fatal_lang_error('sp_error_article_namespace_numeric', false);

		if ($_POST['type'] == 'php' && !empty($_POST['content']) && empty($modSettings['sp_disable_php_validation']))
		{
			$error = sp_validate_php($_POST['content']);

			if ($error)
				fatal_lang_error('error_sp_php_' . $error, false);
		}

		$fields = array(
			'id_category' => 'int',
			'namespace' => 'string',
			'title' => 'string',
			'body' => 'string',
			'type' => 'string',
			'permissions' => 'int',
			'styles' => 'int',
			'status' => 'int',
		);

		$article_info = array(
			'id' => (int) $_POST['article_id'],
			'id_category' => (int) $_POST['category_id'],
			'namespace' => $smcFunc['htmlspecialchars']($_POST['namespace'], ENT_QUOTES),
			'title' => $smcFunc['htmlspecialchars']($_POST['title'], ENT_QUOTES),
			'body' => $smcFunc['htmlspecialchars']($_POST['content'], ENT_QUOTES),
			'type' => in_array($_POST['type'], array('bbc', 'html', 'php')) ? $_POST['type'] : 'bbc',
			'permissions' => (int) $_POST['permissions'],
			'styles' => (int) $_POST['styles'],
			'status' => !empty($_POST['status']) ? 1 : 0,
		);

		if ($article_info['type'] == 'bbc')
			preparsecode($article_info['body']);

		if ($context['is_new'])
		{
			unset($article_info['id']);

			$fields = array_merge($fields, array(
				'id_member' => 'int',
				'member_name' => 'string',
				'date' => 'int',
			));

			$article_info = array_merge($article_info, array(
				'id_member' => $user_info['id'],
				'member_name' => $user_info['name'],
				'date' => time(),
			));

			$smcFunc['db_insert']('',
				'{db_prefix}sp_articles',
				$fields,
				$article_info,
				array('id_article')
			);
			$article_info['id'] = $smcFunc['db_insert_id']('{db_prefix}sp_articles', 'id_article');
		}
		else
		{
			$update_fields = array();
			foreach ($fields as $name => $type)
				$update_fields[] = $name . ' = {' . $type . ':' . $name . '}';

			$smcFunc['db_query']('','
				UPDATE {db_prefix}sp_articles
				SET ' . implode(', ', $update_fields) . '
				WHERE id_article = {int:id}',
				$article_info
			);
		}


		if ($context['is_new'] || $article_info['id_category'] != $context['article']['category']['id'])
		{
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}sp_categories
				SET articles = articles + 1
				WHERE id_category = {int:id}',
				array(
					'id' => $article_info['id_category'],
				)
			);

			if (!$context['is_new'])
			{
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}sp_categories
					SET articles = articles - 1
					WHERE id_category = {int:id}',
					array(
						'id' => $context['article']['category']['id'],
					)
				);
			}
		}

		redirectexit('action=admin;area=portalarticles');
	}

	if (!empty($_POST['preview']))
	{
		if (!$context['is_new'])
		{
			$_REQUEST['article_id'] = (int) $_REQUEST['article_id'];
			$current = sportal_get_articles($_REQUEST['article_id']);
			$author = $current['author'];
			$date = timeformat($current['date']);
			$comments = $current['comments'];
			$views = $current['views'];
		}
		else
		{
			$author = array('link' => '<a href="' . $scripturl .'?action=profile;u=' . $user_info['id'] . '">' . $user_info['name'] . '</a>');
			$date = timeformat(time());
			$comments = $views = 0;
		}

		$context['article'] = array(
			'id' => $_POST['article_id'],
			'article_id' => $_POST['namespace'],
			'category' => sportal_get_categories((int) $_POST['category_id']),
			'author' => $author,
			'title' => $smcFunc['htmlspecialchars']($_POST['title'], ENT_QUOTES),
			'body' => $smcFunc['htmlspecialchars']($_POST['content'], ENT_QUOTES),
			'type' => $_POST['type'],
			'permissions' => $_POST['permissions'],
			'styles' => $_POST['styles'],
			'date' => $date,
			'status' => !empty($_POST['status']),
			'comments' => $comments,
			'views' => $views,
		);

		if ($context['article']['type'] == 'bbc')
			preparsecode($context['article']['body']);

		loadTemplate('PortalArticles');
		$context['preview'] = true;
	}
	elseif ($context['is_new'])
	{
		$context['article'] = array(
			'id' => 0,
			'article_id' => 'article' . mt_rand(1, 5000),
			'category' => array('id' => 0),
			'title' => $txt['sp_articles_default_title'],
			'body' => '',
			'type' => 'bbc',
			'permissions' => 3,
			'styles' => 4,
			'status' => 1,
		);
	}
	else
	{
		$_REQUEST['article_id'] = (int) $_REQUEST['article_id'];
		$context['article'] = sportal_get_articles($_REQUEST['article_id']);
	}

	if ($context['article']['type'] == 'bbc')
		$context['article']['body'] = str_replace(array('"', '<', '>', '&nbsp;'), array('&quot;', '&lt;', '&gt;', ' '), un_preparsecode($context['article']['body']));

	if ($context['article']['type'] != 'bbc')
	{
		$temp_editor = !empty($options['wysiwyg_default']);
		$options['wysiwyg_default'] = false;
	}

	$editor_options = array(
		'id' => 'content',
		'value' => $context['article']['body'],
		'width' => '95%',
		'height' => '200px',
		'preview_type' => 0,
	);
	create_control_richedit($editor_options);
	$context['post_box_name'] = $editor_options['id'];

	if (isset($temp_editor))
		$options['wysiwyg_default'] = $temp_editor;

	$context['article']['permission_profiles'] = sportal_get_profiles(null, 1, 'name');
	$context['article']['style_profiles'] = sportal_get_profiles(null, 2, 'name');
	$context['article']['style'] = sportal_select_style($context['article']['styles']);
	$context['article']['categories'] = sportal_get_categories();

	if (empty($context['article']['permission_profiles']))
		fatal_lang_error('error_sp_no_permission_profiles', false);

	if (empty($context['article']['style_profiles']))
		fatal_lang_error('error_sp_no_style_profiles', false);

	if (empty($context['article']['categories']))
		fatal_lang_error('error_sp_no_category', false);

	$context['page_title'] = $context['is_new'] ? $txt['sp_admin_articles_add'] : $txt['sp_admin_articles_edit'];
	$context['sub_template'] = 'articles_edit';
}

/**
 * Update an articles active status
 */
function sportal_admin_article_status()
{
	global $smcFunc;

	checkSession('get');

	$article_id = !empty($_REQUEST['article_id']) ? (int) $_REQUEST['article_id'] : 0;

	$smcFunc['db_query']('', '
		UPDATE {db_prefix}sp_articles
		SET status = CASE WHEN status = {int:is_active} THEN 0 ELSE 1 END
		WHERE id_article = {int:id}',
		array(
			'is_active' => 1,
			'id' => $article_id,
		)
	);

	redirectexit('action=admin;area=portalarticles');
}

/**
 * Remove an article from the system
 */
function sportal_admin_article_delete()
{
	global $smcFunc;

	checkSession('get');

	$article_id = !empty($_REQUEST['article_id']) ? (int) $_REQUEST['article_id'] : 0;
	$article_info = sportal_get_articles($article_id);

	$smcFunc['db_query']('','
		DELETE FROM {db_prefix}sp_articles
		WHERE id_article = {int:id}',
		array(
			'id' => $article_id,
		)
	);

	$smcFunc['db_query']('', '
		UPDATE {db_prefix}sp_categories
		SET articles = articles - 1
		WHERE id_category = {int:id}',
		array(
			'id' => $article_info['category']['id'],
		)
	);

	redirectexit('action=admin;area=portalarticles');
}