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

function sportal_admin_profiles_main()
{
	global $context, $sourcedir, $txt;

	if (!allowedTo('sp_admin'))
		isAllowedTo('sp_manage_profiles');

	require_once($sourcedir . '/Subs-PortalAdmin.php');

	loadTemplate('PortalAdminProfiles');

	$sub_actions = array(
		'listpermission' => 'sportal_admin_permission_profiles_list',
		'addpermission' => 'sportal_admin_permission_profiles_edit',
		'editpermission' => 'sportal_admin_permission_profiles_edit',
		'deletepermission' => 'sportal_admin_permission_profiles_delete',
	);

	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($sub_actions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'listpermission';

	$context['sub_action'] = $_REQUEST['sa'];

	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $txt['sp_admin_profiles_title'],
		'help' => 'sp_ProfilesArea',
		'description' => $txt['sp_admin_profiles_desc'],
		'tabs' => array(
			'listpermission' => array(
			),
			'addpermission' => array(
			),
		),
	);

	$sub_actions[$context['sub_action']]();
}

function sportal_admin_permission_profiles_list()
{
	global $smcFunc, $context, $scripturl, $txt;

	if (!empty($_POST['remove_profiles']) && !empty($_POST['remove']) && is_array($_POST['remove']))
	{
		checkSession();

		foreach ($_POST['remove'] as $index => $profile_id)
			$_POST['remove'][(int) $index] = (int) $profile_id;

		$smcFunc['db_query']('','
			DELETE FROM {db_prefix}sp_profiles
			WHERE id_profile IN ({array_int:profiles})',
			array(
				'profiles' => $_POST['remove'],
			)
		);
	}

	$sort_methods = array(
		'name' =>  array(
			'down' => 'name ASC',
			'up' => 'name DESC'
		),
	);

	$context['columns'] = array(
		'name' => array(
			'width' => '35%',
			'label' => $txt['sp_admin_profiles_col_name'],
			'class' => 'first_th',
			'sortable' => true
		),
		'articles' => array(
			'width' => '10%',
			'label' => $txt['sp_admin_profiles_col_articles'],
			'sortable' => false
		),
		'blocks' => array(
			'width' => '10%',
			'label' => $txt['sp_admin_profiles_col_blocks'],
			'sortable' => false
		),
		'categories' => array(
			'width' => '10%',
			'label' => $txt['sp_admin_profiles_col_categories'],
			'sortable' => false
		),
		'pages' => array(
			'width' => '10%',
			'label' => $txt['sp_admin_profiles_col_pages'],
			'sortable' => false
		),
		'shoutboxes' => array(
			'width' => '10%',
			'label' => $txt['sp_admin_profiles_col_shoutboxes'],
			'sortable' => false
		),
		'actions' => array(
			'width' => '15%',
			'label' => $txt['sp_admin_profiles_col_actions'],
			'sortable' => false
		),
	);

	if (!isset($_REQUEST['sort']) || !isset($sort_methods[$_REQUEST['sort']]))
		$_REQUEST['sort'] = 'name';

	foreach ($context['columns'] as $col => $dummy)
	{
		$context['columns'][$col]['selected'] = $col == $_REQUEST['sort'];
		$context['columns'][$col]['href'] = $scripturl . '?action=admin;area=portalprofiles;sa=listpermission;sort=' . $col;

		if (!isset($_REQUEST['desc']) && $col == $_REQUEST['sort'])
			$context['columns'][$col]['href'] .= ';desc';

		$context['columns'][$col]['link'] = '<a href="' . $context['columns'][$col]['href'] . '">' . $context['columns'][$col]['label'] . '</a>';
	}

	$context['sort_by'] = $_REQUEST['sort'];
	$context['sort_direction'] = !isset($_REQUEST['desc']) ? 'down' : 'up';

	$request = $smcFunc['db_query']('','
		SELECT COUNT(*)
		FROM {db_prefix}sp_profiles
		WHERE type = {int:type}',
		array(
			'type' => 1,
		)
	);
	list ($total_profiles) =  $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	$context['page_index'] = constructPageIndex($scripturl . '?action=admin;area=portalprofiles;sa=listpermission;sort=' . $_REQUEST['sort'] . (isset($_REQUEST['desc']) ? ';desc' : ''), $_REQUEST['start'], $total_profiles, 20);
	$context['start'] = $_REQUEST['start'];

	$request = $smcFunc['db_query']('','
		SELECT id_profile, name
		FROM {db_prefix}sp_profiles
		ORDER BY {raw:sort}
		LIMIT {int:start}, {int:limit}',
		array(
			'type' => 1,
			'sort' => $sort_methods[$_REQUEST['sort']][$context['sort_direction']],
			'start' => $context['start'],
			'limit' => 20,
		)
	);
	$context['profiles'] = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$context['profiles'][$row['id_profile']] = array(
			'id' => $row['id_profile'],
			'name' => $row['name'],
			'label' => isset($txt['sp_admin_profiles' . substr($row['name'], 1)]) ? $txt['sp_admin_profiles' . substr($row['name'], 1)] : $row['name'],
			'actions' => array(
				'edit' => '<a href="' . $scripturl . '?action=admin;area=portalprofiles;sa=editpermission;profile_id=' . $row['id_profile'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '">' . sp_embed_image('modify') . '</a>',
				'delete' => '<a href="' . $scripturl . '?action=admin;area=portalprofiles;sa=deletepermission;profile_id=' . $row['id_profile'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '" onclick="return confirm(\'', $txt['sp_admin_profiles_delete_confirm'], '\');">' . sp_embed_image('delete') . '</a>',
			)
		);
	}
	$smcFunc['db_free_result']($request);
	
	foreach (array('articles', 'blocks', 'categories', 'pages', 'shoutboxes') as $module)
	{
		$request = $smcFunc['db_query']('','
			SELECT permissions, COUNT(*) AS used
			FROM smf_sp_{raw:module}
			GROUP BY permissions',
			array(
				'module' => $module,
			)
		);
		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			if (isset($context['profiles'][$row['permissions']]))
				$context['profiles'][$row['permissions']][$module] = $row['used'];
		}
		$smcFunc['db_free_result']($request);
	}

	$context['sub_template'] = 'permission_profiles_list';
	$context['page_title'] = $txt['sp_admin_permission_profiles_list'];
}

function sportal_admin_permission_profiles_edit()
{
	global $smcFunc, $context, $txt;

	$context['is_new'] = empty($_REQUEST['profile_id']);

	if (!empty($_POST['submit']))
	{
		checkSession();

		if (!isset($_POST['name']) || $smcFunc['htmltrim']($smcFunc['htmlspecialchars']($_POST['name'], ENT_QUOTES)) === '')
			fatal_lang_error('sp_error_profile_name_empty', false);

		$groups_allowed = $groups_denied = '';

		if (!empty($_POST['membergroups']) && is_array($_POST['membergroups']))
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
			'type' => 'int',
			'name' => 'string',
			'value' => 'string',
		);

		$profile_info = array(
			'id' => (int) $_POST['profile_id'],
			'type' => 1,
			'name' => $smcFunc['htmlspecialchars']($_POST['name'], ENT_QUOTES),
			'value' => implode('|', array($groups_allowed, $groups_denied)),
		);

		if ($context['is_new'])
		{
			unset($profile_info['id']);

			$smcFunc['db_insert']('',
				'{db_prefix}sp_profiles',
				$fields,
				$profile_info,
				array('id_profile')
			);
			$profile_info['id'] = $smcFunc['db_insert_id']('{db_prefix}sp_profiles', 'id_profile');
		}
		else
		{
			$update_fields = array();
			foreach ($fields as $name => $type)
				$update_fields[] = $name . ' = {' . $type . ':' . $name . '}';

			$smcFunc['db_query']('','
				UPDATE {db_prefix}sp_profiles
				SET ' . implode(', ', $update_fields) . '
				WHERE id_profile = {int:id}',
				$profile_info
			);
		}

		redirectexit('action=admin;area=portalprofiles');
	}

	if ($context['is_new'])
	{
		$context['profile'] = array(
			'id' => 0,
			'name' => $txt['sp_profiles_default_name'],
			'groups_allowed' => array(),
			'groups_denied' => array(),
		);
	}
	else
	{
		$_REQUEST['profile_id'] = (int) $_REQUEST['profile_id'];
		$context['profile'] = sportal_get_profiles($_REQUEST['profile_id']);
	}

	$context['profile']['groups'] = sp_load_membergroups();

	$context['page_title'] = $context['is_new'] ? $txt['sp_admin_profiles_add'] : $txt['sp_admin_profiles_edit'];
	$context['sub_template'] = 'permission_profiles_edit';
}

function sportal_admin_permission_delete()
{
	global $smcFunc;

	checkSession('get');

	$profile_id = !empty($_REQUEST['profile_id']) ? (int) $_REQUEST['profile_id'] : 0;

	$smcFunc['db_query']('','
		DELETE FROM {db_prefix}sp_profiles
		WHERE id_profile = {int:id}',
		array(
			'id' => $profile_id,
		)
	);

	redirectexit('action=admin;area=portalprofiles');
}