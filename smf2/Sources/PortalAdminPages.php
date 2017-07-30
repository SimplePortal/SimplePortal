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
	void sportal_admin_pages_main()
		// !!!

	void sportal_admin_page_list()
		// !!!

	void sportal_admin_page_edit()
		// !!!

	void sportal_admin_page_delete()
		// !!!

	void sportal_admin_page_status()
		// !!!
*/

function sportal_admin_pages_main()
{
	global $context, $txt, $sourcedir;

	if (!allowedTo('sp_admin'))
		isAllowedTo('sp_manage_pages');

	require_once($sourcedir . '/Subs-PortalAdmin.php');

	loadTemplate('PortalAdminPages');

	$subActions = array(
		'list' => 'sportal_admin_page_list',
		'add' => 'sportal_admin_page_edit',
		'edit' => 'sportal_admin_page_edit',
		'delete' => 'sportal_admin_page_delete',
		'status' => 'sportal_admin_page_status',
	);

	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'list';

	$context['sub_action'] = $_REQUEST['sa'];

	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $txt['sp_admin_pages_title'],
		'help' => 'sp_PagesArea',
		'description' => $txt['sp_admin_pages_desc'],
		'tabs' => array(
			'list' => array(
			),
			'add' => array(
			),
		),
	);

	$subActions[$_REQUEST['sa']]();
}

function sportal_admin_page_list()
{
	global $txt, $smcFunc, $context, $scripturl;

	if (!empty($_POST['remove_pages']) && !empty($_POST['remove']) && is_array($_POST['remove']))
	{
		checkSession();

		foreach ($_POST['remove'] as $index => $page_id)
			$_POST['remove'][(int) $index] = (int) $page_id;

		$smcFunc['db_query']('','
			DELETE FROM {db_prefix}sp_pages
			WHERE id_page IN ({array_int:pages})',
			array(
				'pages' => $_POST['remove'],
			)
		);
	}

	$sort_methods = array(
		'title' =>  array(
			'down' => 'title ASC',
			'up' => 'title DESC'
		),
		'namespace' =>  array(
			'down' => 'namespace ASC',
			'up' => 'namespace DESC'
		),
		'type' => array(
			'down' => 'type ASC',
			'up' => 'type DESC'
		),
		'views' => array(
			'down' => 'views ASC',
			'up' => 'views DESC'
		),
		'status' => array(
			'down' => 'status ASC',
			'up' => 'status DESC'
		),
	);

	$context['columns'] = array(
		'title' => array(
			'width' => '45%',
			'label' => $txt['sp_admin_pages_col_title'],
			'class' => 'first_th',
			'sortable' => true
		),
		'namespace' => array(
			'width' => '25%',
			'label' => $txt['sp_admin_pages_col_namespace'],
			'sortable' => true
		),
		'type' => array(
			'width' => '8%',
			'label' => $txt['sp_admin_pages_col_type'],
			'sortable' => true
		),
		'views' => array(
			'width' => '6%',
			'label' => $txt['sp_admin_pages_col_views'],
			'sortable' => true
		),
		'status' => array(
			'width' => '6%',
			'label' => $txt['sp_admin_pages_col_status'],
			'sortable' => true
		),
		'actions' => array(
			'width' => '10%',
			'label' => $txt['sp_admin_pages_col_actions'],
			'sortable' => false
		),
	);

	if (!isset($_REQUEST['sort']) || !isset($sort_methods[$_REQUEST['sort']]))
		$_REQUEST['sort'] = 'title';

	foreach ($context['columns'] as $col => $dummy)
	{
		$context['columns'][$col]['selected'] = $col == $_REQUEST['sort'];
		$context['columns'][$col]['href'] = $scripturl . '?action=admin;area=portalpages;sa=list;sort=' . $col;

		if (!isset($_REQUEST['desc']) && $col == $_REQUEST['sort'])
			$context['columns'][$col]['href'] .= ';desc';

		$context['columns'][$col]['link'] = '<a href="' . $context['columns'][$col]['href'] . '">' . $context['columns'][$col]['label'] . '</a>';
	}

	$context['sort_by'] = $_REQUEST['sort'];
	$context['sort_direction'] = !isset($_REQUEST['desc']) ? 'down' : 'up';

	$request = $smcFunc['db_query']('','
		SELECT COUNT(*)
		FROM {db_prefix}sp_pages'
	);
	list ($total_pages) =  $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	$context['page_index'] = constructPageIndex($scripturl . '?action=admin;area=portalpages;sa=list;sort=' . $_REQUEST['sort'] . (isset($_REQUEST['desc']) ? ';desc' : ''), $_REQUEST['start'], $total_pages, 20);
	$context['start'] = $_REQUEST['start'];

	$request = $smcFunc['db_query']('','
		SELECT id_page, namespace, title, type, views, status
		FROM {db_prefix}sp_pages
		ORDER BY {raw:sort}
		LIMIT {int:start}, {int:limit}',
		array(
			'sort' => $sort_methods[$_REQUEST['sort']][$context['sort_direction']],
			'start' => $context['start'],
			'limit' => 20,
		)
	);
	$context['pages'] = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$context['pages'][$row['id_page']] = array(
			'id' => $row['id_page'],
			'page_id' => $row['namespace'],
			'title' => $row['title'],
			'href' => $scripturl . '?page=' . $row['namespace'],
			'link' => '<a href="' . $scripturl . '?page=' . $row['namespace'] . '">' . $row['title'] . '</a>',
			'type' => $row['type'],
			'type_text' => $txt['sp_pages_type_'. $row['type']],
			'views' => $row['views'],
			'status' => $row['status'],
			'status_image' => '<a href="' . $scripturl . '?action=admin;area=portalpages;sa=status;page_id=' . $row['id_page'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '">' . sp_embed_image(empty($row['status']) ? 'deactive' : 'active', $txt['sp_admin_pages_' . (!empty($row['status']) ? 'de' : '') . 'activate']) . '</a>',
			'actions' => array(
				'edit' => '<a href="' . $scripturl . '?action=admin;area=portalpages;sa=edit;page_id=' . $row['id_page'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '">' . sp_embed_image('modify') . '</a>',
				'delete' => '<a href="' . $scripturl . '?action=admin;area=portalpages;sa=delete;page_id=' . $row['id_page'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '" onclick="return confirm(\'', $txt['sp_admin_pages_delete_confirm'], '\');">' . sp_embed_image('delete') . '</a>',
			)
		);
	}
	$smcFunc['db_free_result']($request);

	$context['sub_template'] = 'pages_list';
	$context['page_title'] = $txt['sp_admin_pages_list'];
}

function sportal_admin_page_edit()
{
	global $txt, $context, $modSettings, $smcFunc, $sourcedir, $options;

	require_once($sourcedir . '/Subs-Editor.php');
	require_once($sourcedir . '/Subs-Post.php');

	$context['SPortal']['is_new'] = empty($_REQUEST['page_id']);

	if (!empty($_REQUEST['content_mode']) && $_POST['type'] == 'bbc')
	{
		$_REQUEST['content'] = html_to_bbc($_REQUEST['content']);
		$_REQUEST['content'] = un_htmlspecialchars($_REQUEST['content']);
		$_POST['content'] = $_REQUEST['content'];
	}

	$context['sides'] = array(
		5 => $txt['sp-positionHeader'],
		1 => $txt['sp-positionLeft'],
		2 => $txt['sp-positionTop'],
		3 => $txt['sp-positionBottom'],
		4 => $txt['sp-positionRight'],
		6 => $txt['sp-positionFooter'],
	);

	$blocks = getBlockInfo();
	$context['page_blocks'] = array();

	foreach ($blocks as $block)
	{
		$shown = false;
		$tests = array('all', 'allpages', 'sforum');
		if (!$context['SPortal']['is_new'])
			$tests[] = 'p' . ((int) $_REQUEST['page_id']);

		foreach (array('display', 'display_custom') as $field)
		{
			if (substr($block[$field], 0, 4) === '$php')
				continue 2;

			$block[$field] = explode(',', $block[$field]);

			if (!$context['SPortal']['is_new'] && in_array('-p' . ((int) $_REQUEST['page_id']), $block[$field]))
				continue;

			foreach ($tests as $test)
			{
				if (in_array($test, $block[$field]))
				{
					$shown = true;
					break;
				}
			}
		}

		$context['page_blocks'][$block['column']][] = array(
			'id' => $block['id'],
			'label' => $block['label'],
			'shown' => $shown,
		);
	}

	if (!empty($_POST['submit']))
	{
		checkSession();

		if (!isset($_POST['title']) || $smcFunc['htmltrim']($smcFunc['htmlspecialchars']($_POST['title'], ENT_QUOTES)) === '')
			fatal_lang_error('sp_error_page_name_empty', false);

		if (!isset($_POST['namespace']) || $smcFunc['htmltrim']($smcFunc['htmlspecialchars']($_POST['namespace'], ENT_QUOTES)) === '')
			fatal_lang_error('sp_error_page_namespace_empty', false);

		$result = $smcFunc['db_query']('','
			SELECT id_page
			FROM {db_prefix}sp_pages
			WHERE namespace = {string:namespace}
				AND id_page != {int:current}
			LIMIT 1',
			array(
				'limit' => 1,
				'namespace' => $smcFunc['htmlspecialchars']($_POST['namespace'], ENT_QUOTES),
				'current' => (int) $_POST['page_id'],
			)
		);
		list ($has_duplicate) = $smcFunc['db_fetch_row']($result);
		$smcFunc['db_free_result']($result);

		if (!empty($has_duplicate))
			fatal_lang_error('sp_error_page_namespace_duplicate', false);

		if (preg_match('~[^A-Za-z0-9_]+~', $_POST['namespace']) != 0)
			fatal_lang_error('sp_error_page_namespace_invalid_chars', false);

		if (preg_replace('~[0-9]+~', '', $_POST['namespace']) === '')
			fatal_lang_error('sp_error_page_namespace_numeric', false);

		if ($_POST['type'] == 'php' && !allowedTo('admin_forum'))
			fatal_lang_error('cannot_admin_forum', false);

		if ($_POST['type'] == 'php' && !empty($_POST['content']) && empty($modSettings['sp_disable_php_validation']))
		{
			$error = sp_validate_php($_POST['content']);

			if ($error)
				fatal_lang_error('error_sp_php_' . $error, false);
		}

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

		if (!empty($_POST['blocks']) && is_array($_POST['blocks']))
		{
			foreach ($_POST['blocks'] as $id => $block)
				$_POST['blocks'][$id] = (int) $block;
		}
		else
			$_POST['blocks'] = array();

		$fields = array(
			'namespace' => 'string',
			'title' => 'string',
			'body' => 'string',
			'type' => 'string',
			'permission_set' => 'int',
			'groups_allowed' => 'string',
			'groups_denied' => 'string',
			'style' => 'string',
			'status' => 'int',
		);

		$page_info = array(
			'id' => (int) $_POST['page_id'],
			'namespace' => $smcFunc['htmlspecialchars']($_POST['namespace'], ENT_QUOTES),
			'title' => $smcFunc['htmlspecialchars']($_POST['title'], ENT_QUOTES),
			'body' => $smcFunc['htmlspecialchars']($_POST['content'], ENT_QUOTES),
			'type' => $_POST['type'],
			'permission_set' => $permission_set,
			'groups_allowed' => $groups_allowed,
			'groups_denied' => $groups_denied,
			'style' => sportal_parse_style('implode'),
			'status' => !empty($_POST['status']) ? 1 : 0,
		);

		if ($page_info['type'] == 'bbc')
			preparsecode($page_info['body']);

		if ($context['SPortal']['is_new'])
		{
			unset($page_info['id']);

			$smcFunc['db_insert']('',
				'{db_prefix}sp_pages',
				$fields,
				$page_info,
				array('id_page')
			);
			$page_info['id'] = $smcFunc['db_insert_id']('{db_prefix}sp_pages', 'id_page');
		}
		else
		{
			$update_fields = array();
			foreach ($fields as $name => $type)
				$update_fields[] = $name . ' = {' . $type . ':' . $name . '}';

			$smcFunc['db_query']('','
				UPDATE {db_prefix}sp_pages
				SET ' . implode(', ', $update_fields) . '
				WHERE id_page = {int:id}',
				$page_info
			);
		}

		$to_show = array();
		$not_to_show = array();
		$changes = array();

		foreach ($context['page_blocks'] as $page_blocks)
		{
			foreach ($page_blocks as $block)
			{
				if ($block['shown'] && !in_array($block['id'], $_POST['blocks']))
					$not_to_show[] = $block['id'];
				elseif (!$block['shown'] && in_array($block['id'], $_POST['blocks']))
					$to_show[] = $block['id'];
			}
		}

		foreach ($to_show as $id)
		{
			if ((empty($blocks[$id]['display']) && empty($blocks[$id]['display_custom'])) || $blocks[$id]['display'] == 'sportal')
			{
				$changes[$id] = array(
					'display' => 'portal,p' . $page_info['id'],
					'display_custom' => '',
				);
			}
			elseif (in_array($blocks[$id]['display'], array('allaction', 'allboard')))
			{
				$changes[$id] = array(
					'display' => '',
					'display_custom' => $blocks[$id]['display'] . ',p' . $page_info['id'],
				);
			}
			elseif (in_array('-p' . $page_info['id'], explode(',', $blocks[$id]['display_custom'])))
			{
				$changes[$id] = array(
					'display' => $blocks[$id]['display'],
					'display_custom' => implode(',', array_diff(explode(',', $blocks[$id]['display_custom']), array('-p' . $page_info['id']))),
				);
			}
			elseif (empty($blocks[$id]['display_custom']))
			{
				$changes[$id] = array(
					'display' => implode(',', array_merge(explode(',', $blocks[$id]['display']), array('p' . $page_info['id']))),
					'display_custom' => '',
				);
			}
			else
			{
				$changes[$id] = array(
					'display' => $blocks[$id]['display'],
					'display_custom' => implode(',', array_merge(explode(',', $blocks[$id]['display_custom']), array('p' . $page_info['id']))),
				);
			}
		}

		foreach ($not_to_show as $id)
		{
			if (count(array_intersect(array($blocks[$id]['display'], $blocks[$id]['display_custom']), array('sforum', 'allpages', 'all'))) > 0)
			{
				$changes[$id] = array(
					'display' => '',
					'display_custom' => $blocks[$id]['display'] . $blocks[$id]['display_custom'] . ',-p' . $page_info['id'],
				);
			}
			elseif (empty($blocks[$id]['display_custom']))
			{
				$changes[$id] = array(
					'display' => implode(',', array_diff(explode(',', $blocks[$id]['display']), array('p' . $page_info['id']))),
					'display_custom' => '',
				);
			}
			else
			{
				$changes[$id] = array(
					'display' => implode(',', array_diff(explode(',', $blocks[$id]['display']), array('p' . $page_info['id']))),
					'display_custom' => implode(',', array_diff(explode(',', $blocks[$id]['display_custom']), array('p' . $page_info['id']))),
				);
			}
		}

		foreach ($changes as $id => $data)
		{
			$smcFunc['db_query']('','
				UPDATE {db_prefix}sp_blocks
				SET
					display = {string:display},
					display_custom = {string:display_custom}
				WHERE id_block = {int:id}',
				array(
					'id' => $id,
					'display' => $data['display'],
					'display_custom' => $data['display_custom'],
				)
			);
		}

		redirectexit('action=admin;area=portalpages');
	}

	if (!empty($_POST['preview']))
	{
		$permission_set = 0;
		$groups_allowed = $groups_denied = array();

		if (!empty($_POST['permission_set']))
			$permission_set = (int) $_POST['permission_set'];
		elseif (!empty($_POST['membergroups']) && is_array($_POST['membergroups']))
		{
			foreach ($_POST['membergroups'] as $id => $value)
			{
				if ($value == 1)
					$groups_allowed[] = (int) $id;
				elseif ($value == -1)
					$groups_denied[] = (int) $id;
			}
		}

		$context['SPortal']['page'] = array(
			'id' => $_POST['page_id'],
			'page_id' => $_POST['namespace'],
			'title' => $smcFunc['htmlspecialchars']($_POST['title'], ENT_QUOTES),
			'body' => $smcFunc['htmlspecialchars']($_POST['content'], ENT_QUOTES),
			'type' => $_POST['type'],
			'permission_set' => $permission_set,
			'groups_allowed' => $groups_allowed,
			'groups_denied' => $groups_denied,
			'style' => sportal_parse_style('implode'),
			'status' => !empty($_POST['status']),
		);

		if ($context['SPortal']['page']['type'] == 'bbc')
			preparsecode($context['SPortal']['page']['body']);

		loadTemplate('PortalPages');
		$context['SPortal']['preview'] = true;
	}
	elseif ($context['SPortal']['is_new'])
	{
		$context['SPortal']['page'] = array(
			'id' => 0,
			'page_id' => 'page' . mt_rand(1, 5000),
			'title' => $txt['sp_pages_default_title'],
			'body' => '',
			'type' => 'bbc',
			'permission_set' => 3,
			'groups_allowed' => array(),
			'groups_denied' => array(),
			'style' => '',
			'status' => 1,
		);
	}
	else
	{
		$_REQUEST['page_id'] = (int) $_REQUEST['page_id'];
		$context['SPortal']['page'] = sportal_get_pages($_REQUEST['page_id']);
	}

	if ($context['SPortal']['page']['type'] == 'bbc')
		$context['SPortal']['page']['body'] = str_replace(array('"', '<', '>', '&nbsp;'), array('&quot;', '&lt;', '&gt;', ' '), un_preparsecode($context['SPortal']['page']['body']));

	if ($context['SPortal']['page']['type'] != 'bbc')
	{
		$temp_editor = !empty($options['wysiwyg_default']);
		$options['wysiwyg_default'] = false;
	}

	$editorOptions = array(
		'id' => 'content',
		'value' => $context['SPortal']['page']['body'],
		'width' => '95%',
		'height' => '200px',
		'preview_type' => 0,
	);
	create_control_richedit($editorOptions);
	$context['post_box_name'] = $editorOptions['id'];

	if (isset($temp_editor))
		$options['wysiwyg_default'] = $temp_editor;

	$context['SPortal']['page']['groups'] = sp_load_membergroups();
	$context['SPortal']['page']['style'] = sportal_parse_style('explode', $context['SPortal']['page']['style'], !empty($context['SPortal']['preview']));

	$context['page_title'] = $context['SPortal']['is_new'] ? $txt['sp_admin_pages_add'] : $txt['sp_admin_pages_edit'];
	$context['sub_template'] = 'pages_edit';
}

function sportal_admin_page_delete()
{
	global $smcFunc;

	checkSession('get');

	$page_id = !empty($_REQUEST['page_id']) ? (int) $_REQUEST['page_id'] : 0;

	$smcFunc['db_query']('','
		DELETE FROM {db_prefix}sp_pages
		WHERE id_page = {int:id}',
		array(
			'id' => $page_id,
		)
	);

	redirectexit('action=admin;area=portalpages');
}

function sportal_admin_page_status()
{
	global $smcFunc;

	checkSession('get');

	$page_id = !empty($_REQUEST['page_id']) ? (int) $_REQUEST['page_id'] : 0;

	$smcFunc['db_query']('', '
		UPDATE {db_prefix}sp_pages
		SET status = CASE WHEN status = {int:is_active} THEN 0 ELSE 1 END
		WHERE id_page = {int:id}',
		array(
			'is_active' => 1,
			'id' => $page_id,
		)
	);

	redirectexit('action=admin;area=portalpages');
}

?>