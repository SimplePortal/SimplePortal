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
	global $context, $txt, $sourcedir;

	if (!allowedTo('sp_admin'))
		isAllowedTo('sp_manage_shoutbox');

	require_once($sourcedir . '/Subs-PortalAdmin.php');

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

	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $txt['sp_admin_shoutbox_title'],
		'help' => 'sp_ShoutboxArea',
		'description' => $txt['sp_admin_shoutbox_desc'],
		'tabs' => array(
			'list' => array(
			),
			'add' => array(
			),
		),
	);

	$subActions[$_REQUEST['sa']]();
}

function sportal_admin_shoutbox_list()
{
	global $txt, $smcFunc, $context, $scripturl;

	if (!empty($_POST['remove_shoutbox']) && !empty($_POST['remove']) && is_array($_POST['remove']))
	{
		checkSession();

		foreach ($_POST['remove'] as $index => $page_id)
			$_POST['remove'][(int) $index] = (int) $page_id;

		$smcFunc['db_query']('','
			DELETE FROM {db_prefix}sp_shoutboxes
			WHERE id_shoutbox IN ({array_int:shoutbox})',
			array(
				'shoutbox' => $_POST['remove'],
			)
		);

		$smcFunc['db_query']('','
			DELETE FROM {db_prefix}sp_shouts
			WHERE id_shoutbox IN ({array_int:shoutbox})',
			array(
				'shoutbox' => $_POST['remove'],
			)
		);
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
			'class' => 'first_th',
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
		$context['columns'][$col]['href'] = $scripturl . '?action=admin;area=portalshoutbox;sa=list;sort=' . $col;

		if (!isset($_REQUEST['desc']) && $col == $_REQUEST['sort'])
			$context['columns'][$col]['href'] .= ';desc';

		$context['columns'][$col]['link'] = '<a href="' . $context['columns'][$col]['href'] . '">' . $context['columns'][$col]['label'] . '</a>';
	}

	$context['sort_by'] = $_REQUEST['sort'];
	$context['sort_direction'] = !isset($_REQUEST['desc']) ? 'down' : 'up';

	$request = $smcFunc['db_query']('','
		SELECT COUNT(*)
		FROM {db_prefix}sp_shoutboxes'
	);
	list ($total_shoutbox) =  $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	$context['page_index'] = constructPageIndex($scripturl . '?action=admin;area=portalshoutbox;sa=list;sort=' . $_REQUEST['sort'] . (isset($_REQUEST['desc']) ? ';desc' : ''), $_REQUEST['start'], $total_shoutbox, 20);
	$context['start'] = $_REQUEST['start'];

	$request = $smcFunc['db_query']('','
		SELECT id_shoutbox, name, caching, status, num_shouts
		FROM {db_prefix}sp_shoutboxes
		ORDER BY id_shoutbox, {raw:sort}
		LIMIT {int:start}, {int:limit}',
		array(
			'sort' => $sort_methods[$_REQUEST['sort']][$context['sort_direction']],
			'start' => $context['start'],
			'limit' => 20,
		)
	);
	$context['shoutboxes'] = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$context['shoutboxes'][$row['id_shoutbox']] = array(
			'id' => $row['id_shoutbox'],
			'name' => $row['name'],
			'shouts' => $row['num_shouts'],
			'caching' => $row['caching'],
			'status' => $row['status'],
			'status_image' => '<a href="' . $scripturl . '?action=admin;area=portalshoutbox;sa=status;shoutbox_id=' . $row['id_shoutbox'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '">' . sp_embed_image(empty($row['status']) ? 'deactive' : 'active', $txt['sp_admin_shoutbox_' . (!empty($row['status']) ? 'de' : '') . 'activate']) . '</a>',
			'actions' => array(
				'edit' => '<a href="' . $scripturl . '?action=admin;area=portalshoutbox;sa=edit;shoutbox_id=' . $row['id_shoutbox'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '">' . sp_embed_image('modify') . '</a>',
				'prune' => '<a href="' . $scripturl . '?action=admin;area=portalshoutbox;sa=prune;shoutbox_id=' . $row['id_shoutbox'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '">' . sp_embed_image('bin') . '</a>',
				'delete' => '<a href="' . $scripturl . '?action=admin;area=portalshoutbox;sa=delete;shoutbox_id=' . $row['id_shoutbox'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '" onclick="return confirm(\'', $txt['sp_admin_shoutbox_delete_confirm'], '\');">' . sp_embed_image('delete') . '</a>',
			)
		);
	}
	$smcFunc['db_free_result']($request);

	$context['sub_template'] = 'shoutbox_list';
	$context['page_title'] = $txt['sp_admin_shoutbox_list'];
}

function sportal_admin_shoutbox_edit()
{
	global $txt, $context, $modSettings, $smcFunc;

	$context['SPortal']['is_new'] = empty($_REQUEST['shoutbox_id']);

	if (!empty($_POST['submit']))
	{
		checkSession();

		if (!isset($_POST['name']) || $smcFunc['htmltrim']($smcFunc['htmlspecialchars']($_POST['name'], ENT_QUOTES)) === '')
			fatal_lang_error('sp_error_shoutbox_name_empty', false);

		$result = $smcFunc['db_query']('','
			SELECT id_shoutbox
			FROM {db_prefix}sp_shoutboxes
			WHERE name = {string:name}
				AND id_shoutbox != {int:current}
			LIMIT 1',
			array(
				'limit' => 1,
				'name' => $smcFunc['htmlspecialchars']($_POST['name'], ENT_QUOTES),
				'current' => (int) $_POST['shoutbox_id'],
			)
		);
		list ($has_duplicate) = $smcFunc['db_fetch_row']($result);
		$smcFunc['db_free_result']($result);

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
				$_POST['allowed_bbc'][$id] = $smcFunc['htmlspecialchars']($tag, ENT_QUOTES);

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
			'name' => $smcFunc['htmlspecialchars']($_POST['name'], ENT_QUOTES),
			'permission_set' => $permission_set,
			'groups_allowed' => $groups_allowed,
			'groups_denied' => $groups_denied,
			'moderator_groups' => $_POST['moderator_groups'],
			'warning' => $smcFunc['htmlspecialchars']($_POST['warning'], ENT_QUOTES),
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

			$smcFunc['db_insert']('',
				'{db_prefix}sp_shoutboxes',
				$fields,
				$shoutbox_info,
				array('id_shoutbox')
			);
			$shoutbox_info['id'] = $smcFunc['db_insert_id']('{db_prefix}sp_shoutboxes', 'id_shoutbox');
		}
		else
		{
			$update_fields = array();
			foreach ($fields as $name => $type)
				$update_fields[] = $name . ' = {' . $type . ':' . $name . '}';

			$smcFunc['db_query']('','
				UPDATE {db_prefix}sp_shoutboxes
				SET ' . implode(', ', $update_fields) . '
				WHERE id_shoutbox = {int:id}',
				$shoutbox_info
			);
		}

		sportal_update_shoutbox($shoutbox_info['id']);

		if ($context['SPortal']['is_new'] && (allowedTo(array('sp_admin', 'sp_manage_blocks'))))
			redirectexit('action=admin;area=portalshoutbox;sa=blockredirect;shoutbox=' . $shoutbox_info['id']);
		else
			redirectexit('action=admin;area=portalshoutbox');
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
		'b' => $txt['bold'],
		'i' => $txt['italic'],
		'u' => $txt['underline'],
		's' => $txt['strike'],
		'pre' => $txt['preformatted'],
		'flash' => $txt['flash'],
		'img' => $txt['image'],
		'url' => $txt['hyperlink'],
		'email' => $txt['insert_email'],
		'ftp' => $txt['ftp'],
		'glow' => $txt['glow'],
		'shadow' => $txt['shadow'],
		'sup' => $txt['superscript'],
		'sub' => $txt['subscript'],
		'tt' => $txt['teletype'],
		'code' => $txt['bbc_code'],
		'quote' => $txt['bbc_quote'],
		'size' => $txt['font_size'],
		'font' => $txt['font_face'],
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
	global $smcFunc, $context, $txt;

	$shoutbox_id = empty($_REQUEST['shoutbox_id']) ? 0 : (int) $_REQUEST['shoutbox_id'];
	$context['shoutbox'] = sportal_get_shoutbox($shoutbox_id);

	if (empty($context['shoutbox']))
		fatal_lang_error('error_sp_shoutbox_not_exist', false);

	if (!empty($_POST['submit']))
	{
		checkSession();

		if (!empty($_POST['type']))
		{
			$where = array('id_shoutbox = {int:shoutbox_id}');
			$parameters = array('shoutbox_id' => $shoutbox_id);

			if ($_POST['type'] == 'days' && !empty($_POST['days']))
			{
				$where[] = 'log_time < {int:time_limit}';
				$parameters['time_limit'] = time() - $_POST['days'] * 86400;
			}
			elseif ($_POST['type'] == 'member' && !empty($_POST['member']))
			{
				$request = $smcFunc['db_query']('', '
					SELECT id_member
					FROM {db_prefix}members
					WHERE member_name = {string:member}
						OR real_name = {string:member}
					LIMIT {int:limit}',
					array(
						'member' => strtr(trim($smcFunc['htmlspecialchars']($_POST['member'], ENT_QUOTES)), array('\'' => '&#039;')),
						'limit' => 1,
					)
				);
				list ($member_id) =  $smcFunc['db_fetch_row']($request);
				$smcFunc['db_free_result']($request);

				if (!empty($member_id))
				{
					$where[] = 'id_member = {int:member_id}';
					$parameters['member_id'] = $member_id;
				}
			}

			if ($_POST['type'] == 'all' || count($where) > 1)
			{
				$smcFunc['db_query']('','
					DELETE FROM {db_prefix}sp_shouts
					WHERE ' . implode(' AND ', $where),
					$parameters
				);

				if ($_POST['type'] != 'all')
				{
					$request = $smcFunc['db_query']('', '
						SELECT COUNT(*)
						FROM {db_prefix}sp_shouts
						WHERE id_shoutbox = {int:shoutbox_id}
						LIMIT {int:limit}',
						array(
							'shoutbox_id' => $shoutbox_id,
							'limit' => 1,
						)
					);
					list ($total_shouts) =  $smcFunc['db_fetch_row']($request);
					$smcFunc['db_free_result']($request);
				}
				else
					$total_shouts = 0;

				$smcFunc['db_query']('','
					UPDATE {db_prefix}sp_shoutboxes
					SET num_shouts = {int:total_shouts}
					WHERE id_shoutbox = {int:shoutbox_id}',
					array(
						'shoutbox_id' => $shoutbox_id,
						'total_shouts' => $total_shouts,
					)
				);

				sportal_update_shoutbox($shoutbox_id);
			}
		}

		redirectexit('action=admin;area=portalshoutbox');
	}

	$context['page_title'] = $txt['sp_admin_shoutbox_prune'];
	$context['sub_template'] = 'shoutbox_prune';
}

function sportal_admin_shoutbox_delete()
{
	global $smcFunc;

	checkSession('get');

	$shoutbox_id = !empty($_REQUEST['shoutbox_id']) ? (int) $_REQUEST['shoutbox_id'] : 0;

	$smcFunc['db_query']('','
		DELETE FROM {db_prefix}sp_shoutboxes
		WHERE id_shoutbox = {int:id}',
		array(
			'id' => $shoutbox_id,
		)
	);

	$smcFunc['db_query']('','
		DELETE FROM {db_prefix}sp_shouts
		WHERE id_shoutbox = {int:id}',
		array(
			'id' => $shoutbox_id,
		)
	);

	redirectexit('action=admin;area=portalshoutbox');
}

function sportal_admin_shoutbox_status()
{
	global $smcFunc;

	checkSession('get');

	$shoutbox_id = !empty($_REQUEST['shoutbox_id']) ? (int) $_REQUEST['shoutbox_id'] : 0;

	$smcFunc['db_query']('', '
		UPDATE {db_prefix}sp_shoutboxes
		SET status = CASE WHEN status = {int:is_active} THEN 0 ELSE 1 END
		WHERE id_shoutbox = {int:id}',
		array(
			'is_active' => 1,
			'id' => $shoutbox_id,
		)
	);

	redirectexit('action=admin;area=portalshoutbox');
}

function sportal_admin_shoutbox_block_redirect()
{
	global $context, $scripturl, $txt;

	if (!allowedTo('sp_admin'))
		isAllowedTo('sp_manage_blocks');

	$context['page_title'] = $txt['sp_admin_shoutbox_add'];
	$context['redirect_message'] = sprintf($txt['sp_admin_shoutbox_block_redirect_message'], $scripturl . '?action=admin;area=portalblocks;sa=add;selected_type=sp_shoutbox;parameters[]=shoutbox;shoutbox=' . $_GET['shoutbox'], $scripturl . '?action=admin;area=portalshoutbox');
	$context['sub_template'] = 'shoutbox_block_redirect';
}

?>