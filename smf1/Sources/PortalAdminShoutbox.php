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
	void sportal_admin_shoutbox_main()
		// !!!

	void sportal_admin_shoutbox_list()
		// !!!

	void sportal_admin_shoutbox_edit()
		// !!!

	void sportal_admin_shoutbox_delete()
		// !!!

	void sportal_admin_shoutbox_status()
		// !!!
*/

function sportal_admin_shoutbox_main()
{
	global $context, $txt, $scripturl, $sourcedir;

	if (!allowedTo('sp_admin'))
		isAllowedTo('sp_manage_shoutbox');

	loadTemplate('PortalAdminShoutbox');

	$subActions = array(
		'list' => 'sportal_admin_shoutbox_list',
		'add' => 'sportal_admin_shoutbox_edit',
		'edit' => 'sportal_admin_shoutbox_edit',
		'prune' => 'sportal_admin_shoutbox_prune',
		'delete' => 'sportal_admin_shoutbox_delete',
		'status' => 'sportal_admin_shoutbox_status',
		'blockredirect' => 'sportal_admin_shoutbox_block_redirect',
	);

	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'list';

	$context['sub_action'] = $_REQUEST['sa'];

	$context['admin_tabs'] = array(
		'title' => $txt['sp_admin_shoutbox_title'],
		'help' => 'sp_ShoutboxArea',
		'description' => $txt['sp_admin_shoutbox_desc'],
		'tabs' => array(
			'list' => array(
				'title' => $txt['sp_admin_shoutbox_list'],
				'description' => $txt['sp_admin_shoutbox_desc'],
				'href' => $scripturl . '?action=manageportal;area=portalshoutbox;sa=list',
				'is_selected' => $_REQUEST['sa'] == 'list' || $_REQUEST['sa'] == 'edit',
			),
			'add' => array(
				'title' => $txt['sp_admin_shoutbox_add'],
				'description' => $txt['sp_admin_shoutbox_desc'],
				'href' => $scripturl . '?action=manageportal;area=portalshoutbox;sa=add',
				'is_selected' => $_REQUEST['sa'] == 'add',
			),
		),
	);

	$subActions[$_REQUEST['sa']]();
}

function sportal_admin_shoutbox_list()
{
	global $txt, $db_prefix, $context, $scripturl;

	if (!empty($_POST['remove_shoutbox']) && !empty($_POST['remove']) && is_array($_POST['remove']))
	{
		checkSession();

		foreach ($_POST['remove'] as $index => $page_id)
			$_POST['remove'][(int) $index] = (int) $page_id;

		db_query("
			DELETE FROM {$db_prefix}sp_shoutboxes
			WHERE ID_SHOUTBOX IN (" . implode(', ', $_POST['remove']) . ")", __FILE__, __LINE__);

		db_query("
			DELETE FROM {$db_prefix}sp_shouts
			WHERE ID_SHOUTBOX IN (" . implode(', ', $_POST['remove']) . ")", __FILE__, __LINE__);
	}

	$sort_methods = array(
		'name' =>  array(
			'down' => 'name ASC',
			'up' => 'name DESC'
		),
		'num_shouts' => array(
			'down' => 'num_shouts ASC',
			'up' => 'num_shouts DESC'
		),
		'caching' => array(
			'down' => 'caching ASC',
			'up' => 'caching DESC'
		),
		'status' => array(
			'down' => 'status ASC',
			'up' => 'status DESC'
		),
	);

	$context['columns'] = array(
		'name' => array(
			'width' => '40%',
			'label' => $txt['sp_admin_shoutbox_col_name'],
			'sortable' => true
		),
		'num_shouts' => array(
			'width' => '15%',
			'label' => $txt['sp_admin_shoutbox_col_shouts'],
			'sortable' => true
		),
		'caching' => array(
			'width' => '15%',
			'label' => $txt['sp_admin_shoutbox_col_caching'],
			'sortable' => true
		),
		'status' => array(
			'width' => '15%',
			'label' => $txt['sp_admin_shoutbox_col_status'],
			'sortable' => true
		),
		'actions' => array(
			'width' => '15%',
			'label' => $txt['sp_admin_shoutbox_col_actions'],
			'sortable' => false
		),
	);

	if (!isset($_REQUEST['sort']) || !isset($sort_methods[$_REQUEST['sort']]))
		$_REQUEST['sort'] = 'name';

	foreach ($context['columns'] as $col => $dummy)
	{
		$context['columns'][$col]['selected'] = $col == $_REQUEST['sort'];
		$context['columns'][$col]['href'] = $scripturl . '?action=manageportal;area=portalshoutbox;sa=list;sort=' . $col;

		if (!isset($_REQUEST['desc']) && $col == $_REQUEST['sort'])
			$context['columns'][$col]['href'] .= ';desc';

		$context['columns'][$col]['link'] = '<a href="' . $context['columns'][$col]['href'] . '">' . $context['columns'][$col]['label'] . '</a>';
	}

	$context['sort_by'] = $_REQUEST['sort'];
	$context['sort_direction'] = !isset($_REQUEST['desc']) ? 'down' : 'up';

	$request = db_query("
		SELECT COUNT(*)
		FROM {$db_prefix}sp_shoutboxes", __FILE__, __LINE__);
	list ($total_shoutbox) =  mysql_fetch_row($request);
	mysql_free_result($request);

	$context['page_index'] = constructPageIndex($scripturl . '?action=manageportal;area=portalshoutbox;sa=list;sort=' . $_REQUEST['sort'] . (isset($_REQUEST['desc']) ? ';desc' : ''), $_REQUEST['start'], $total_shoutbox, 20);
	$context['start'] = $_REQUEST['start'];

	$request = db_query("
		SELECT ID_SHOUTBOX, name, caching, status, num_shouts
		FROM {$db_prefix}sp_shoutboxes
		ORDER BY " . $sort_methods[$_REQUEST['sort']][$context['sort_direction']] . "
		LIMIT $context[start], 20", __FILE__, __LINE__);
	$context['shoutboxes'] = array();
	while ($row = mysql_fetch_assoc($request))
	{
		$context['shoutboxes'][$row['ID_SHOUTBOX']] = array(
			'id' => $row['ID_SHOUTBOX'],
			'name' => $row['name'],
			'shouts' => $row['num_shouts'],
			'caching' => $row['caching'],
			'status' => $row['status'],
			'status_image' => '<a href="' . $scripturl . '?action=manageportal;area=portalshoutbox;sa=status;shoutbox_id=' . $row['ID_SHOUTBOX'] . ';sesc=' . $context['session_id'] . '">' . sp_embed_image(empty($row['status']) ? 'deactive' : 'active', $txt['sp_admin_shoutbox_' . (empty($row['status']) ? 'de' : '') . 'activate']) . '</a>',
			'actions' => array(
				'edit' => '<a href="' . $scripturl . '?action=manageportal;area=portalshoutbox;sa=edit;shoutbox_id=' . $row['ID_SHOUTBOX'] . ';sesc=' . $context['session_id'] . '">' . sp_embed_image('modify') . '</a>',
				'prune' => '<a href="' . $scripturl . '?action=manageportal;area=portalshoutbox;sa=prune;shoutbox_id=' . $row['ID_SHOUTBOX'] . ';sesc=' . $context['session_id'] . '">' . sp_embed_image('bin') . '</a>',
				'delete' => '<a href="' . $scripturl . '?action=manageportal;area=portalshoutbox;sa=delete;shoutbox_id=' . $row['ID_SHOUTBOX'] . ';sesc=' . $context['session_id'] . '" onclick="return confirm(\'', $txt['sp_admin_shoutbox_delete_confirm'], '\');">' . sp_embed_image('delete') . '</a>',
			)
		);
	}
	mysql_free_result($request);

	$context['sub_template'] = 'shoutbox_list';
	$context['page_title'] = $txt['sp_admin_shoutbox_list'];
}

function sportal_admin_shoutbox_edit()
{
	global $txt, $context, $modSettings, $db_prefix, $func;

	$context['SPortal']['is_new'] = empty($_REQUEST['shoutbox_id']);

	if (!empty($_POST['submit']))
	{
		checkSession();

		if (!isset($_POST['name']) || $func['htmltrim'](addslashes($func['htmlspecialchars'](stripslashes($_POST['name']), ENT_QUOTES))) === '')
			fatal_lang_error('sp_error_shoutbox_name_empty', false);

		$request = db_query("
			SELECT ID_SHOUTBOX
			FROM {$db_prefix}sp_shoutboxes
			WHERE name = '" . addslashes($func['htmlspecialchars'](stripslashes($_POST['name']), ENT_QUOTES)) . "'
				AND ID_SHOUTBOX != " . (int) $_POST['shoutbox_id'] . "
			LIMIT 1", __FILE__, __LINE__);
		list ($has_duplicate) = mysql_fetch_row($request);
		mysql_free_result($request);

		if (!empty($has_duplicate))
			fatal_lang_error('sp_error_shoutbox_name_duplicate', false);

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

		if (isset($_POST['moderator_groups']) && is_array($_POST['moderator_groups']) && count($_POST['moderator_groups']) > 0)
		{
			foreach ($_POST['moderator_groups'] as $id => $group)
				$_POST['moderator_groups'][$id] = (int) $group;

			$_POST['moderator_groups'] = implode(',', $_POST['moderator_groups']);
		}
		else
			$_POST['moderator_groups'] = '';

		if (!empty($_POST['allowed_bbc']) && is_array($_POST['allowed_bbc']))
		{
			foreach ($_POST['allowed_bbc'] as $id => $tag)
				$_POST['allowed_bbc'][$id] = addslashes($func['htmlspecialchars'](stripslashes($tag), ENT_QUOTES));

			$_POST['allowed_bbc'] = implode(',', $_POST['allowed_bbc']);
		}
		else
			$_POST['allowed_bbc'] = '';

		$fields = array(
			'name' => 'string',
			'permission_set' => 'int',
			'groups_allowed' => 'string',
			'groups_denied' => 'string',
			'moderator_groups' => 'string',
			'warning' => 'string',
			'allowed_bbc' => 'string',
			'height' => 'int',
			'num_show' => 'int',
			'num_max' => 'int',
			'reverse' => 'int',
			'caching' => 'int',
			'refresh' => 'int',
			'status' => 'int',
		);

		$shoutbox_info = array(
			'id' => (int) $_POST['shoutbox_id'],
			'name' => addslashes($func['htmlspecialchars'](stripslashes($_POST['name']), ENT_QUOTES)),
			'permission_set' => $permission_set,
			'groups_allowed' => $groups_allowed,
			'groups_denied' => $groups_denied,
			'moderator_groups' => $_POST['moderator_groups'],
			'warning' => addslashes($func['htmlspecialchars'](stripslashes($_POST['warning']), ENT_QUOTES)),
			'allowed_bbc' => $_POST['allowed_bbc'],
			'height' => (int) $_POST['height'],
			'num_show' => (int) $_POST['num_show'],
			'num_max' => (int) $_POST['num_max'],
			'reverse' => !empty($_POST['reverse']) ? 1 : 0,
			'caching' => !empty($_POST['caching']) ? 1 : 0,
			'refresh' => (int) $_POST['refresh'],
			'status' => !empty($_POST['status']) ? 1 : 0,
		);

		if ($context['SPortal']['is_new'])
		{
			unset($shoutbox_info['id']);

			$insert = array();
			foreach ($shoutbox_info as $key => $info)
				$insert[$key] = "'" . $info . "'";

			db_query("
				INSERT INTO {$db_prefix}sp_shoutboxes
					(" . implode(', ', array_keys($insert)) . ")
				VALUES
					(" . implode(', ', $insert) . ")", __FILE__, __LINE__);
			$shoutbox_info['id'] = db_insert_id();
		}
		else
		{
			$update_fields = array();
			foreach ($fields as $name => $type)
				$update_fields[] = $name . ' = \'' . $shoutbox_info[$name] . '\'';

			db_query("
				UPDATE {$db_prefix}sp_shoutboxes
				SET " . implode(', ', $update_fields) . "
				WHERE ID_SHOUTBOX = $shoutbox_info[id]
				LIMIT 1", __FILE__, __LINE__);
		}

		sportal_update_shoutbox($shoutbox_info['id']);

		if ($context['SPortal']['is_new'] && (allowedTo(array('sp_admin', 'sp_manage_blocks'))))
			redirectexit('action=manageportal;area=portalshoutbox;sa=blockredirect;shoutbox=' . $shoutbox_info['id']);
		else
			redirectexit('action=manageportal;area=portalshoutbox');
	}

	if ($context['SPortal']['is_new'])
	{
		$context['SPortal']['shoutbox'] = array(
			'id' => 0,
			'name' => $txt['sp_shoutbox_default_name'],
			'permission_set' => 3,
			'groups_allowed' => array(),
			'groups_denied' => array(),
			'moderator_groups' => array(),
			'warning' => '',
			'allowed_bbc' => array('b', 'i', 'u', 's', 'url', 'code', 'quote', 'me'),
			'height' => 200,
			'num_show' => 20,
			'num_max' => 1000,
			'reverse' => 0,
			'caching' => 1,
			'refresh' => 0,
			'status' => 1,
		);
	}
	else
	{
		$_REQUEST['shoutbox_id'] = (int) $_REQUEST['shoutbox_id'];
		$context['SPortal']['shoutbox'] = sportal_get_shoutbox($_REQUEST['shoutbox_id']);
	}

	loadLanguage('Post');

	$context['SPortal']['shoutbox']['groups'] = sp_load_membergroups();
	sp_loadMemberGroups($context['SPortal']['shoutbox']['moderator_groups'], 'moderator', 'moderator_groups');

	$context['allowed_bbc'] = array(
		'b' => $txt[253],
		'i' => $txt[254],
		'u' => $txt[255],
		's' => $txt[441],
		'pre' => $txt[444],
		'flash' => $txt[433],
		'img' => $txt[435],
		'url' => $txt[257],
		'email' => $txt[258],
		'ftp' => $txt[434],
		'glow' => $txt[442],
		'shadow' => $txt[443],
		'sup' => $txt[447],
		'sub' => $txt[448],
		'tt' => $txt[440],
		'code' => $txt[259],
		'quote' => $txt[260],
		'size' => $txt[532],
		'font' => $txt[533],
		'color' => $txt['change_color'],
		'me' => 'me',
	);

	$disabled_tags = array();
	if (!empty($modSettings['disabledBBC']))
		$disabled_tags = explode(',', $modSettings['disabledBBC']);
	if (empty($modSettings['enableEmbeddedFlash']))
		$disabled_tags[] = 'flash';

	foreach ($disabled_tags as $tag)
	{
		if ($tag == 'list')
			$context['disabled_tags']['orderlist'] = true;

		$context['disabled_tags'][trim($tag)] = true;
	}

	$context['page_title'] = $context['SPortal']['is_new'] ? $txt['sp_admin_shoutbox_add'] : $txt['sp_admin_shoutbox_edit'];
	$context['sub_template'] = 'shoutbox_edit';
}

function sportal_admin_shoutbox_prune()
{
	global $db_prefix, $func, $context, $txt;

	$shoutbox_id = empty($_REQUEST['shoutbox_id']) ? 0 : (int) $_REQUEST['shoutbox_id'];
	$context['shoutbox'] = sportal_get_shoutbox($shoutbox_id);

	if (empty($context['shoutbox']))
		fatal_lang_error('error_sp_shoutbox_not_exist', false);

	if (!empty($_POST['submit']))
	{
		checkSession();

		if (!empty($_POST['type']))
		{
			$query = array('ID_SHOUTBOX = ' . $shoutbox_id);

			if ($_POST['type'] == 'days' && !empty($_POST['days']))
				$query[] = 'log_time < ' . (time() - $_POST['days'] * 86400);
			elseif ($_POST['type'] == 'member' && !empty($_POST['member']))
			{
				$member_name = strtr(trim($func['htmlspecialchars']($_POST['member'], ENT_QUOTES)), array('\'' => '&#039;'));
				$request = db_query("
					SELECT ID_MEMBER
					FROM {$db_prefix}members
					WHERE memberName = '$member_name'
						OR realName = '$member_name'
					LIMIT 1", __FILE__, __LINE__);
				list ($member_id) =  mysql_fetch_row($request);
				mysql_free_result($request);

				if (!empty($member_id))
					$query[] = 'id_member = ' . $member_id;
			}

			if ($_POST['type'] == 'all' || count($query) > 1)
			{
				db_query("
					DELETE FROM {$db_prefix}sp_shouts
					WHERE " . implode(" AND ", $query), __FILE__, __LINE__);

				if ($_POST['type'] != 'all')
				{
					$request = db_query("
						SELECT COUNT(*)
						FROM {$db_prefix}sp_shouts
						WHERE ID_SHOUTBOX = $shoutbox_id
						LIMIT 1", __FILE__, __LINE__);
					list ($total_shouts) =  mysql_fetch_row($request);
					mysql_free_result($request);
				}
				else
					$total_shouts = 0;

				db_query("
					UPDATE {$db_prefix}sp_shoutboxes
					SET num_shouts = $total_shouts
					WHERE ID_SHOUTBOX = $shoutbox_id", __FILE__, __LINE__);

				sportal_update_shoutbox($shoutbox_id);
			}
		}

		redirectexit('action=manageportal;area=portalshoutbox');
	}

	$context['page_title'] = $txt['sp_admin_shoutbox_prune'];
	$context['sub_template'] = 'shoutbox_prune';
}

function sportal_admin_shoutbox_delete()
{
	global $db_prefix;

	checkSession('get');

	$shoutbox_id = !empty($_REQUEST['shoutbox_id']) ? (int) $_REQUEST['shoutbox_id'] : 0;

	db_query("
		DELETE FROM {$db_prefix}sp_shoutboxes
		WHERE ID_SHOUTBOX = $shoutbox_id", __FILE__, __LINE__);

	db_query("
		DELETE FROM {$db_prefix}sp_shouts
		WHERE ID_SHOUTBOX = $shoutbox_id", __FILE__, __LINE__);

	redirectexit('action=manageportal;area=portalshoutbox');
}

function sportal_admin_shoutbox_status()
{
	global $db_prefix;

	checkSession('get');

	$shoutbox_id = !empty($_REQUEST['shoutbox_id']) ? (int) $_REQUEST['shoutbox_id'] : 0;

	db_query("
		UPDATE {$db_prefix}sp_shoutboxes
		SET status = CASE WHEN status = 1 THEN 0 ELSE 1 END
		WHERE ID_SHOUTBOX = $shoutbox_id
		LIMIT 1", __FILE__, __LINE__);

	redirectexit('action=manageportal;area=portalshoutbox');
}

function sportal_admin_shoutbox_block_redirect()
{
	global $context, $scripturl, $txt;

	if (!allowedTo('sp_admin'))
		isAllowedTo('sp_manage_blocks');

	$context['page_title'] = $txt['sp_admin_shoutbox_add'];
	$context['redirect_message'] = sprintf($txt['sp_admin_shoutbox_block_redirect_message'], $scripturl . '?action=manageportal;area=portalblocks;sa=add;selected_type=sp_shoutbox;parameters[]=shoutbox;shoutbox=' . $_GET['shoutbox'], $scripturl . '?action=manageportal;area=portalshoutbox');
	$context['sub_template'] = 'shoutbox_block_redirect';
}

?>