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

function sportal_admin_categories_main()
{
	global $context, $sourcedir, $txt;

	if (!allowedTo('sp_admin'))
		isAllowedTo('sp_manage_articles');

	require_once($sourcedir . '/Subs-PortalAdmin.php');

	loadTemplate('PortalAdminCategories');

	$sub_actions = array(
		'list' => 'sportal_admin_category_list',
		'add' => 'sportal_admin_category_edit',
		'edit' => 'sportal_admin_category_edit',
		'status' => 'sportal_admin_category_status',
		'delete' => 'sportal_admin_category_delete',
	);

	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($sub_actions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'list';

	$context['sub_action'] = $_REQUEST['sa'];

	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $txt['sp_admin_categories_title'],
		'help' => 'sp_CategoriesArea',
		'description' => $txt['sp_admin_categories_desc'],
		'tabs' => array(
			'list' => array(
			),
			'add' => array(
			),
		),
	);

	$sub_actions[$context['sub_action']]();
}

function sportal_admin_category_list()
{
	global $smcFunc, $context, $scripturl, $txt;

	if (!empty($_POST['remove_categories']) && !empty($_POST['remove']) && is_array($_POST['remove']))
	{
		checkSession();

		foreach ($_POST['remove'] as $index => $category_id)
			$_POST['remove'][(int) $index] = (int) $category_id;

		$smcFunc['db_query']('','
			DELETE FROM {db_prefix}sp_categories
			WHERE id_category IN ({array_int:categories})',
			array(
				'categories' => $_POST['remove'],
			)
		);

		$smcFunc['db_query']('','
			DELETE FROM {db_prefix}sp_articles
			WHERE id_category IN ({array_int:categories})',
			array(
				'categories' => $_POST['remove'],
			)
		);
	}

	$sort_methods = array(
		'name' =>  array(
			'down' => 'name ASC',
			'up' => 'name DESC'
		),
		'namespace' =>  array(
			'down' => 'namespace ASC',
			'up' => 'namespace DESC'
		),
		'articles' =>  array(
			'down' => 'articles ASC',
			'up' => 'articles DESC'
		),
		'status' => array(
			'down' => 'status ASC',
			'up' => 'status DESC'
		),
	);

	$context['columns'] = array(
		'name' => array(
			'width' => '40%',
			'label' => $txt['sp_admin_categories_col_name'],
			'class' => 'first_th',
			'sortable' => true
		),
		'namespace' => array(
			'width' => '30%',
			'label' => $txt['sp_admin_categories_col_namespace'],
			'sortable' => true
		),
		'articles' => array(
			'width' => '10%',
			'label' => $txt['sp_admin_categories_col_articles'],
			'sortable' => true
		),
		'status' => array(
			'width' => '10%',
			'label' => $txt['sp_admin_categories_col_status'],
			'sortable' => true
		),
		'actions' => array(
			'width' => '10%',
			'label' => $txt['sp_admin_categories_col_actions'],
			'sortable' => false
		),
	);

	if (!isset($_REQUEST['sort']) || !isset($sort_methods[$_REQUEST['sort']]))
		$_REQUEST['sort'] = 'name';

	foreach ($context['columns'] as $col => $dummy)
	{
		$context['columns'][$col]['selected'] = $col == $_REQUEST['sort'];
		$context['columns'][$col]['href'] = $scripturl . '?action=admin;area=portalcategories;sa=list;sort=' . $col;

		if (!isset($_REQUEST['desc']) && $col == $_REQUEST['sort'])
			$context['columns'][$col]['href'] .= ';desc';

		$context['columns'][$col]['link'] = '<a href="' . $context['columns'][$col]['href'] . '">' . $context['columns'][$col]['label'] . '</a>';
	}

	$context['sort_by'] = $_REQUEST['sort'];
	$context['sort_direction'] = !isset($_REQUEST['desc']) ? 'down' : 'up';

	$request = $smcFunc['db_query']('','
		SELECT COUNT(*)
		FROM {db_prefix}sp_categories'
	);
	list ($total_categories) =  $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	$context['page_index'] = constructPageIndex($scripturl . '?action=admin;area=portalcategories;sa=list;sort=' . $_REQUEST['sort'] . (isset($_REQUEST['desc']) ? ';desc' : ''), $_REQUEST['start'], $total_categories, 20);
	$context['start'] = $_REQUEST['start'];

	$request = $smcFunc['db_query']('','
		SELECT id_category, name, namespace, articles, status
		FROM {db_prefix}sp_categories
		ORDER BY {raw:sort}
		LIMIT {int:start}, {int:limit}',
		array(
			'sort' => $sort_methods[$_REQUEST['sort']][$context['sort_direction']],
			'start' => $context['start'],
			'limit' => 20,
		)
	);
	$context['categories'] = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$context['categories'][$row['id_category']] = array(
			'id' => $row['id_category'],
			'category_id' => $row['namespace'],
			'name' => $row['name'],
			'href' => $scripturl . '?category=' . $row['namespace'],
			'link' => '<a href="' . $scripturl . '?category=' . $row['namespace'] . '">' . $row['name'] . '</a>',
			'articles' => $row['articles'],
			'status' => $row['status'],
			'status_image' => '<a href="' . $scripturl . '?action=admin;area=portalcategories;sa=status;category_id=' . $row['id_category'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '">' . sp_embed_image(empty($row['status']) ? 'deactive' : 'active', $txt['sp_admin_categories_' . (!empty($row['status']) ? 'de' : '') . 'activate']) . '</a>',
			'actions' => array(
				'edit' => '<a href="' . $scripturl . '?action=admin;area=portalcategories;sa=edit;category_id=' . $row['id_category'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '">' . sp_embed_image('modify') . '</a>',
				'delete' => '<a href="' . $scripturl . '?action=admin;area=portalcategories;sa=delete;category_id=' . $row['id_category'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '" onclick="return confirm(\'', $txt['sp_admin_categories_delete_confirm'], '\');">' . sp_embed_image('delete') . '</a>',
			)
		);
	}
	$smcFunc['db_free_result']($request);

	$context['sub_template'] = 'categories_list';
	$context['page_title'] = $txt['sp_admin_categories_list'];
}

function sportal_admin_category_edit()
{
	global $smcFunc, $context, $sourcedir, $modSettings, $options, $txt;

	$context['is_new'] = empty($_REQUEST['category_id']);

	if (!empty($_POST['submit']))
	{
		checkSession();

		if (!isset($_POST['name']) || $smcFunc['htmltrim']($smcFunc['htmlspecialchars']($_POST['name'], ENT_QUOTES)) === '')
			fatal_lang_error('sp_error_category_name_empty', false);

		if (!isset($_POST['namespace']) || $smcFunc['htmltrim']($smcFunc['htmlspecialchars']($_POST['namespace'], ENT_QUOTES)) === '')
			fatal_lang_error('sp_error_category_namespace_empty', false);

		$result = $smcFunc['db_query']('','
			SELECT id_category
			FROM {db_prefix}sp_categories
			WHERE namespace = {string:namespace}
				AND id_category != {int:current}
			LIMIT 1',
			array(
				'limit' => 1,
				'namespace' => $smcFunc['htmlspecialchars']($_POST['namespace'], ENT_QUOTES),
				'current' => (int) $_POST['category_id'],
			)
		);
		list ($has_duplicate) = $smcFunc['db_fetch_row']($result);
		$smcFunc['db_free_result']($result);

		if (!empty($has_duplicate))
			fatal_lang_error('sp_error_category_namespace_duplicate', false);

		if (preg_match('~[^A-Za-z0-9_]+~', $_POST['namespace']) != 0)
			fatal_lang_error('sp_error_category_namespace_invalid_chars', false);

		if (preg_replace('~[0-9]+~', '', $_POST['namespace']) === '')
			fatal_lang_error('sp_error_category_namespace_numeric', false);

		$permission_set = 0;
		$groups_allowed = $groups_denied = '';

		if (!empty($_POST['permission_set']))
			$permission_set = (int) $_POST['permission_set'];
		elseif (!empty($_POST['membergroups']) && is_array($_POST['membergroups']))
		{
			$groups_allowed = $groups_denied = array();

			foreach ($_POST['membergroups'] as $id => $value)
			{
				if ($value == 1)
					$groups_allowed[] = (int) $id;
				elseif ($value == -1)
					$groups_denied[] = (int) $id;
			}

			$groups_allowed = implode(',', $groups_allowed);
			$groups_denied = implode(',', $groups_denied);
		}

		$fields = array(
			'namespace' => 'string',
			'name' => 'string',
			'description' => 'string',
			'permission_set' => 'int',
			'groups_allowed' => 'string',
			'groups_denied' => 'string',
			'status' => 'int',
		);

		$category_info = array(
			'id' => (int) $_POST['category_id'],
			'namespace' => $smcFunc['htmlspecialchars']($_POST['namespace'], ENT_QUOTES),
			'name' => $smcFunc['htmlspecialchars']($_POST['name'], ENT_QUOTES),
			'description' => $smcFunc['htmlspecialchars']($_POST['description'], ENT_QUOTES),
			'permission_set' => $permission_set,
			'groups_allowed' => $groups_allowed,
			'groups_denied' => $groups_denied,
			'status' => !empty($_POST['status']) ? 1 : 0,
		);

		if ($context['is_new'])
		{
			unset($category_info['id']);

			$smcFunc['db_insert']('',
				'{db_prefix}sp_categories',
				$fields,
				$category_info,
				array('id_category')
			);
			$category_info['id'] = $smcFunc['db_insert_id']('{db_prefix}sp_categories', 'id_category');
		}
		else
		{
			$update_fields = array();
			foreach ($fields as $name => $type)
				$update_fields[] = $name . ' = {' . $type . ':' . $name . '}';

			$smcFunc['db_query']('','
				UPDATE {db_prefix}sp_categories
				SET ' . implode(', ', $update_fields) . '
				WHERE id_category = {int:id}',
				$category_info
			);
		}

		redirectexit('action=admin;area=portalcategories');
	}

	if ($context['is_new'])
	{
		$context['category'] = array(
			'id' => 0,
			'category_id' => 'category' . mt_rand(1, 5000),
			'name' => $txt['sp_categories_default_name'],
			'description' => '',
			'permission_set' => 3,
			'groups_allowed' => array(),
			'groups_denied' => array(),
			'status' => 1,
		);
	}
	else
	{
		$_REQUEST['category_id'] = (int) $_REQUEST['category_id'];
		$context['category'] = sportal_get_categories($_REQUEST['category_id']);
	}

	$context['category']['groups'] = sp_load_membergroups();

	$context['page_title'] = $context['is_new'] ? $txt['sp_admin_categories_add'] : $txt['sp_admin_categories_edit'];
	$context['sub_template'] = 'categories_edit';
}

function sportal_admin_category_status()
{
	global $smcFunc;

	checkSession('get');

	$category_id = !empty($_REQUEST['category_id']) ? (int) $_REQUEST['category_id'] : 0;

	$smcFunc['db_query']('', '
		UPDATE {db_prefix}sp_categories
		SET status = CASE WHEN status = {int:is_active} THEN 0 ELSE 1 END
		WHERE id_category = {int:id}',
		array(
			'is_active' => 1,
			'id' => $category_id,
		)
	);

	redirectexit('action=admin;area=portalcategories');
}

function sportal_admin_category_delete()
{
	global $smcFunc;

	checkSession('get');

	$category_id = !empty($_REQUEST['category_id']) ? (int) $_REQUEST['category_id'] : 0;

	$smcFunc['db_query']('','
		DELETE FROM {db_prefix}sp_categories
		WHERE id_category = {int:id}',
		array(
			'id' => $category_id,
		)
	);

	$smcFunc['db_query']('','
		DELETE FROM {db_prefix}sp_articles
		WHERE id_category = {int:id}',
		array(
			'id' => $category_id,
		)
	);

	redirectexit('action=admin;area=portalcategories');
}

?>