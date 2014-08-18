<?php
/**********************************************************************************
* PortalAdminBlocks.php                                                           *
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
	void sportal_admin_blocks_main()
		// !!!

	void sportal_admin_block_list()
		// !!!

	void sportal_admin_block_edit()
		// !!!

	void sportal_admin_block_delete()
		// !!!

	void sportal_admin_block_move()
		// !!!

	void sportal_admin_state_state()
		// !!!

	void sportal_admin_column_change()
		// !!!
*/

function sportal_admin_blocks_main()
{
	global $context, $txt, $scripturl, $sourcedir;

	if (!allowedTo('sp_admin'))
		isAllowedTo('sp_manage_blocks');

	loadTemplate('PortalAdminBlocks');

	$subActions = array(
		'list' => 'sportal_admin_block_list',
		'header' => 'sportal_admin_block_list',
		'left' => 'sportal_admin_block_list',
		'top' => 'sportal_admin_block_list',
		'bottom' => 'sportal_admin_block_list',
		'right' => 'sportal_admin_block_list',
		'footer' => 'sportal_admin_block_list',
		'add' => 'sportal_admin_block_edit',
		'edit' => 'sportal_admin_block_edit',
		'delete' => 'sportal_admin_block_delete',
		'move' => 'sportal_admin_block_move',
		'statechange' => 'sportal_admin_state_change',
	);

	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'list';

	$context['sub_action'] = $_REQUEST['sa'];

	$context['admin_tabs'] = array(
		'title' => $txt['sp-blocksBlocks'],
		'help' => 'sp_BlocksArea',
		'description' => $txt['sp-adminBlockListDesc'],
		'tabs' => array(
			'list' => array(
				'title' => $txt['sp-adminBlockListName'],
				'description' => $txt['sp-adminBlockListDesc'],
				'href' => $scripturl . '?action=manageportal;area=portalblocks;sa=list',
				'is_selected' => $_REQUEST['sa'] == 'list' || $_REQUEST['sa'] == 'edit',
			),
			'add' => array(
				'title' => $txt['sp-adminBlockAddName'],
				'description' => $txt['sp-adminBlockAddDesc'],
				'href' => $scripturl . '?action=manageportal;area=portalblocks;sa=add',
				'is_selected' => $_REQUEST['sa'] == 'add',
			),
			'header' => array(
				'title' => $txt['sp-positionHeader'],
				'description' => $txt['sp-adminBlockHeaderListDesc'],
				'href' => $scripturl . '?action=manageportal;area=portalblocks;sa=header',
				'is_selected' => $_REQUEST['sa'] == 'header',
			),
			'left' => array(
				'title' => $txt['sp-positionLeft'],
				'description' => $txt['sp-adminBlockLeftListDesc'],
				'href' => $scripturl . '?action=manageportal;area=portalblocks;sa=left',
				'is_selected' => $_REQUEST['sa'] == 'left',
			),
			'top' => array(
				'title' => $txt['sp-positionTop'],
				'description' => $txt['sp-adminBlockTopListDesc'],
				'href' => $scripturl . '?action=manageportal;area=portalblocks;sa=top',
				'is_selected' => $_REQUEST['sa'] == 'top',
			),
			'bottom' => array(
				'title' => $txt['sp-positionBottom'],
				'description' => $txt['sp-adminBlockBottomListDesc'],
				'href' => $scripturl . '?action=manageportal;area=portalblocks;sa=bottom',
				'is_selected' => $_REQUEST['sa'] == 'bottom',
			),
			'right' => array(
				'title' => $txt['sp-positionRight'],
				'description' => $txt['sp-adminBlockRightListDesc'],
				'href' => $scripturl . '?action=manageportal;area=portalblocks;sa=right',
				'is_selected' => $_REQUEST['sa'] == 'right',
			),
			'footer' => array(
				'title' => $txt['sp-positionFooter'],
				'description' => $txt['sp-adminBlockFooterListDesc'],
				'href' => $scripturl . '?action=manageportal;area=portalblocks;sa=footer',
				'is_selected' => $_REQUEST['sa'] == 'footer',
			),
		),
	);

	$subActions[$_REQUEST['sa']]();
}

// Show the Block List.
function sportal_admin_block_list()
{
	global $txt, $context, $scripturl;

	// We have 4 sides...
	$context['sides'] = array(
		'header' => array(
			'id' => '5',
			'name' => 'Header',
			'label' => $txt['sp-positionHeader'],
			'help' => 'sp-blocksHeaderList',
		),
		'left' => array(
			'id' => '1',
			'name' => 'Left',
			'label' => $txt['sp-positionLeft'],
			'help' => 'sp-blocksLeftList',
		),
		'top' => array(
			'id' => '2',
			'name' => 'Middle-Top',
			'label' => $txt['sp-positionTop'],
			'help' => 'sp-blocksTopList',
		),
		'bottom' => array(
			'id' => '3',
			'name' => 'Middle-Bottom',
			'label' => $txt['sp-positionBottom'],
			'help' => 'sp-blocksBottomList',
		),
		'right' => array(
			'id' => '4',
			'name' => 'Right',
			'label' => $txt['sp-positionRight'],
			'help' => 'sp-blocksRightList',
		),
		'footer' => array(
			'id' => '6',
			'name' => 'Footer',
			'label' => $txt['sp-positionFooter'],
			'help' => 'sp-blocksFooterList',
		),
	);

	$context['block_move'] = isset($_GET['sa']) && $_GET['sa'] == 'select' && !empty($_GET['block_id']) ? (int) $_GET['block_id'] : 0;

	$sides = array('header', 'left', 'top', 'bottom', 'right', 'footer');
	// Are we viewing any of the sub lists for an individual side?
	if(in_array($context['sub_action'], $sides))
	{
		// Remove any sides that we don't need to show. ;)
		foreach($sides as $side)
		{
			if($context['sub_action'] != $side)
				unset($context['sides'][$side]);
		}
		$context['sp_blocks_single_side_list'] = true;
	}

	// Columns to show.
	$context['columns'] = array(
		'label' => array(
			'width' => '40%',
			'label' => $txt['sp-adminColumnName'],
		),
		'type' => array(
			'width' => '40%',
			'label' => $txt['sp-adminColumnType'],
		),
		'action' => array(
			'width' => '20%',
			'label' => $txt['sp-adminColumnAction'],
		),
	);

	// Get block info for each side.
	foreach($context['sides'] as $side_id => $side)
	{
		$context['blocks'][$side['name']] = getBlockInfo($side['id']);
		foreach ($context['blocks'][$side['name']] as $block_id => $block)
		{
			$context['sides'][$side_id]['last'] = $block_id;
			$context['blocks'][$side['name']][$block_id]['actions'] = array(
				'state_icon' => empty($block['state']) ? '<a href="' . $scripturl . '?action=manageportal;area=portalblocks;sa=statechange;' . (empty($context['sp_blocks_single_side_list']) ? '' : 'redirect=' . $block['column'] . ';') . 'block_id=' . $block['id'] . ';type=block;sesc=' . $context['session_id'] . '">' . sp_embed_image('deactive', $txt['sp-blocksActivate']) . '</a>' : '<a href="' . $scripturl . '?action=manageportal;area=portalblocks;sa=statechange;' . (empty($context['sp_blocks_single_side_list']) ? '' : 'redirect=' . $block['column'] . ';') . 'block_id=' . $block['id'] . ';type=block;sesc=' . $context['session_id'] . '">' . sp_embed_image('active', $txt['sp-blocksDeactivate']) . '</a>',
				'edit' => '<a href="' . $scripturl . '?action=manageportal;area=portalblocks;sa=edit;block_id=' . $block['id'] . ';sesc=' . $context['session_id'] . '">' . sp_embed_image('modify') . '</a>',
				'move' => '<a href="' . $scripturl . '?action=manageportal;area=portalblocks;sa=select;block_id=' . $block['id'] . ';sesc=' . $context['session_id'] . '">' . sp_embed_image('move', $txt['sp-adminColumnMove']) . '</a>',
				'delete' => '<a href="' . $scripturl . '?action=manageportal;area=portalblocks;sa=delete;block_id=' . $block['id'] . ';col=' . $block['column'] . ';sesc=' . $context['session_id'] . '" onclick="return confirm(\''.$txt['sp-deleteblock'].'\');">' . sp_embed_image('delete') . '</a>',
			);

			if ($context['block_move'])
			{
				$context['blocks'][$side['name']][$block_id]['move_insert'] = '<a href="' . $scripturl . '?action=manageportal;area=portalblocks;sa=move;block_id=' . $context['block_move'] . ';col=' . $block['column'] . ';row=' . $block['row'] . ';sesc=' . $context['session_id'] . '">' . sp_embed_image('arrow', $txt['sp-blocks_move_here']) . '</a>';
	
				if ($context['block_move'] == $block_id)
					$context['move_title'] = sprintf($txt['sp-blocks_select_destination'], htmlspecialchars($block['label']));
			}
		}
	}

	// Call the sub template.
	$context['sub_template'] = 'block_list';
	$context['page_title'] = $txt['sp-adminBlockListName'];
}

function sportal_admin_block_edit()
{
	global $txt, $context, $modSettings, $db_prefix, $func, $sourcedir, $boarddir, $boards;

	// Just in case, the admin could be doing something silly like editing a SP block while SP it disabled. ;)
	require_once($sourcedir . '/PortalBlocks.php');

	$context['SPortal']['is_new'] = empty($_REQUEST['block_id']);

	// BBC Fix move the parameter to the correct position.
	if (!empty($_POST['bbc_name']))
		$_POST['parameters'][$_POST['bbc_name']] = !empty($_POST[$_POST['bbc_parameter']]) ? $_POST[$_POST['bbc_parameter']] : '';

	// Passing the selected type via $_GET instead of $_POST?
	$start_parameters = array();
	if (!empty($_GET['selected_type']) && empty($_POST['selected_type']))
	{
		$_POST['selected_type'] = array($_GET['selected_type']);
		if (!empty($_GET['parameters']))
		{
			foreach ($_GET['parameters'] as $param)
			{
				if (isset($_GET[$param]))
					$start_parameters[$param] = $_GET[$param];
			}
		}
	}

	// List the Blocks
	if ($context['SPortal']['is_new'] && empty($_POST['selected_type']) && empty($_POST['add_block']))
	{
		$context['SPortal']['block_types'] = getFunctionInfo();

		if (!empty($_REQUEST['col']))
			$context['SPortal']['block']['column'] = $_REQUEST['col'];

		$context['sub_template'] = 'block_select_type';
		$context['page_title'] = $txt['sp-blocksAdd'];
	}
	// New Block
	elseif ($context['SPortal']['is_new'] && !empty($_POST['selected_type']))
	{
		$context['SPortal']['block'] = array(
			'id' => 0,
			'label' => $txt['sp-blocksDefaultLabel'],
			'type' => $_POST['selected_type'][0],
			'type_text' => !empty($txt['sp_function_' . $_POST['selected_type'][0] . '_label']) ? $txt['sp_function_' . $_POST['selected_type'][0] . '_label'] : $txt['sp_function_unknown_label'],
			'column' => !empty($_POST['block_column']) ? $_POST['block_column'] : 0,
			'row' => 0,
			'permission_set' => 3,
			'groups_allowed' => array(),
			'groups_denied' => array(),
			'state' => 1,
			'force_view' => 0,
			'display' => '',
			'display_custom' => '',
			'style' => '',
			'parameters' => !empty($start_parameters) ? $start_parameters : array(),
			'options'=> $_POST['selected_type'][0](array(), false, true),
			'list_blocks' => !empty($_POST['block_column']) ? getBlockInfo($_POST['block_column']) : array(),
		);
	}
	// Edit Block
	elseif (!$context['SPortal']['is_new'] && empty($_POST['add_block']))
	{
		$_REQUEST['block_id'] = (int) $_REQUEST['block_id'];
		$context['SPortal']['block'] = current(getBlockInfo(null, $_REQUEST['block_id']));

		$context['SPortal']['block'] += array(
			'options'=> $context['SPortal']['block']['type'](array(), false, true),
			'list_blocks' => getBlockInfo($context['SPortal']['block']['column']),
		);
	}

	// Prepare the Blocksetup for the ouput
	if (!empty($_POST['preview_block']))
	{
		// Just in case, the admin could be doing something silly like editing a SP block while SP it disabled. ;)
		require_once($boarddir . '/SSI.php');
		sportal_init_headers();
		loadTemplate('Portal');

		$type_parameters = $_POST['block_type'](array(), 0, true);

		if (!empty($_POST['parameters']) && is_array($_POST['parameters']) && !empty($type_parameters))
		{
			foreach ($type_parameters as $name => $type)
			{
				if (isset($_POST['parameters'][$name]))
				{
					if ($type == 'int' || $type == 'select')
						$_POST['parameters'][$name] = (int) $_POST['parameters'][$name];
					elseif ($type == 'boards' || $type == 'board_select')
						$_POST['parameters'][$name] = is_array($_POST['parameters'][$name]) ? implode('|', $_POST['parameters'][$name]) : $_POST['parameters'][$name];
					elseif ($type == 'text' || $type == 'textarea' || is_array($type))
						$_POST['parameters'][$name] = addslashes($func['htmlspecialchars'](stripslashes($_POST['parameters'][$name]), ENT_QUOTES));
					elseif ($type == 'check')
						$_POST['parameters'][$name] = !empty($_POST['parameters'][$name]) ? 1 : 0;
				}
			}
		}
		else
			$_POST['parameters'] = array();

		if (empty($_POST['display_advanced']))
		{
			if (!empty($_POST['display_simple']) && in_array($_POST['display_simple'], array('all', 'sportal', 'sforum', 'allaction', 'allboard', 'allpages')))
				$display = $_POST['display_simple'];
			else
				$display = '';

			$custom = '';
		}
		else
		{
			$display = array();
			$custom = array();

			if (!empty($_POST['display_actions']))
				foreach ($_POST['display_actions'] as $action)
					$display[] = addslashes($func['htmlspecialchars'](stripslashes($action), ENT_QUOTES));

			if (!empty($_POST['display_boards']))
				foreach ($_POST['display_boards'] as $board)
					$display[] = 'b' . ((int) substr($board, 1));

			if (!empty($_POST['display_pages']))
				foreach ($_POST['display_pages'] as $page)
					$display[] = 'p' . ((int) substr($page, 1));

			if (!empty($_POST['display_custom']))
			{
				$temp = explode(',', $_POST['display_custom']);
				foreach ($temp as $action)
					$custom[] = addslashes($func['htmlspecialchars'](stripslashes($action), ENT_QUOTES));
			}

			$display = empty($display) ? '' : implode(',', $display);
			$custom = empty($custom) ? '' : implode(',', $custom);
		}

		if (!empty($_POST['parameters']))
			foreach ($_POST['parameters'] as $variable => $value)
				$_POST['parameters'][$variable] = stripslashes(!is_array($value) ? $value : implode('|', $value));

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

		$context['SPortal']['block'] = array(
			'id' => $_POST['block_id'],
			'label' => addslashes($func['htmlspecialchars'](stripslashes($_POST['block_name']), ENT_QUOTES)),
			'type' => $_POST['block_type'],
			'type_text' => !empty($txt['sp_function_' . $_POST['block_type'] . '_label']) ? $txt['sp_function_' . $_POST['block_type'] . '_label'] : $txt['sp_function_unknown_label'],
			'column' => $_POST['block_column'],
			'row' => !empty($_POST['block_row']) ? $_POST['block_row'] : 0,
			'permission_set' => $permission_set,
			'groups_allowed' => $groups_allowed,
			'groups_denied' => $groups_denied,
			'state' => !empty($_POST['block_active']),
			'force_view' => !empty($_POST['block_force']),
			'display' => $display,
			'display_custom' => $custom,
			'style' => sportal_parse_style('implode'),
			'parameters' => !empty($_POST['parameters']) ? $_POST['parameters'] : array(),
			'options'=> $_POST['block_type'](array(), false, true),
			'list_blocks' => getBlockInfo($_POST['block_column']),
			'collapsed' => false,
		);

		if (strpos($modSettings['leftwidth'], '%') !== false || strpos($modSettings['leftwidth'], 'px') !== false)
			$context['widths'][1] = $modSettings['leftwidth'];
		else
			$context['widths'][1] = $modSettings['leftwidth'] . 'px';

		if (strpos($modSettings['rightwidth'], '%') !== false || strpos($modSettings['rightwidth'], 'px') !== false)
			$context['widths'][4] = $modSettings['rightwidth'];
		else
			$context['widths'][4] = $modSettings['rightwidth'] . 'px';

		if (strpos($context['widths'][1], '%') !== false)
			$context['widths'][2] = $context['widths'][3] = 100 - ($context['widths'][1] + $context['widths'][4]) . '%';
		elseif (strpos($context['widths'][1], 'px') !== false)
			$context['widths'][2] = $context['widths'][3] = 960 - ($context['widths'][1] + $context['widths'][4]) . 'px';

		if (strpos($context['widths'][1], '%') !== false)
		{
			$context['widths'][2] = $context['widths'][3] = 100 - ($context['widths'][1] + $context['widths'][4]) . '%';
			$context['widths'][5] = $context['widths'][6] = '100%';
		}
		elseif (strpos($context['widths'][1], 'px') !== false)
		{
			$context['widths'][2] = $context['widths'][3] = 960 - ($context['widths'][1] + $context['widths'][4]) . 'px';
			$context['widths'][5] = $context['widths'][6] = '960px';
		}

		$context['SPortal']['preview'] = true;
	}

	// Store the block into the database :D
	if (!empty($_POST['selected_type']) || !empty($_POST['preview_block']) || (!$context['SPortal']['is_new'] && empty($_POST['add_block'])))
	{
		if ($context['SPortal']['block']['type'] == 'sp_php' && !allowedTo('admin_forum'))
			fatal_lang_error('cannot_admin_forum', false);

		$context['html_headers'] .= '
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		function sp_collapseObject(id)
		{
			mode = document.getElementById("sp_object_" + id).style.display == "" ? 0 : 1;
			document.getElementById("sp_collapse_" + id).src = smf_images_url + (mode ? "/collapse.gif" : "/expand.gif");
			document.getElementById("sp_object_" + id).style.display = mode ? "" : "none";
		}
	// ]]></script>';

		if (loadLanguage('SPortalHelp', '', false) === false)
			loadLanguage('SPortalHelp', 'english');

		$context['SPortal']['block']['groups'] = sp_load_membergroups();

		$context['simple_actions'] = array(
			'sportal' => $txt['sp-portal'],
			'sforum' => $txt['sp-forum'],
			'allaction' => $txt['sp-blocksOptionAllActions'],
			'allboard' => $txt['sp-blocksOptionAllBoards'],
			'allpages' => $txt['sp-blocksOptionAllPages'],
			'all' => $txt['sp-blocksOptionEverywhere'],
		);

		$context['display_actions'] = array(
			'portal' => $txt['sp-portal'],
			'forum' => $txt['sp-forum'],
			'recent' => $txt[214],
			'unread' => $txt['unread_topics_visit'],
			'unreadreplies' => $txt['unread_replies'],
			'profile' => $txt[79],
			'pm' => $txt[144],
			'calendar' => $txt['calendar24'],
			'admin' =>  $txt[2],
			'login' =>  $txt[34],
			'register' =>  $txt[97],
			'post' =>  $txt[105],
			'stats' =>  $txt[645],
			'search' =>  $txt[182],
			'mlist' =>  $txt[19],
			'help' =>  $txt[119],
			'who' =>  $txt['who_title'],
		);

		$request = db_query("
			SELECT ID_BOARD, name
			FROM {$db_prefix}boards
			ORDER BY name", __FILE__, __LINE__);
		$context['display_boards'] = array();
		while ($row = mysql_fetch_assoc($request))
			$context['display_boards']['b' . $row['ID_BOARD']] = $row['name'];
		mysql_free_result($request);

		$request = db_query("
			SELECT ID_PAGE, title
			FROM {$db_prefix}sp_pages
			ORDER BY title", __FILE__, __LINE__);
		$context['display_pages'] = array();
		while ($row = mysql_fetch_assoc($request))
			$context['display_pages']['p' . $row['ID_PAGE']] = $row['title'];
		mysql_free_result($request);

		if (empty($context['SPortal']['block']['display']))
			$context['SPortal']['block']['display'] = array('0');
		else
			$context['SPortal']['block']['display'] = explode(',', $context['SPortal']['block']['display']);

		if (in_array($context['SPortal']['block']['display'][0], array('all', 'sportal', 'sforum', 'allaction', 'allboard', 'allpages')) || $context['SPortal']['is_new'] || empty($context['SPortal']['block']['display'][0]) && empty($context['SPortal']['block']['display_custom']))
			$context['SPortal']['block']['display_type'] = 0;
		else
			$context['SPortal']['block']['display_type'] = 1;

		$context['SPortal']['block']['style'] = sportal_parse_style('explode', $context['SPortal']['block']['style'], !empty($context['SPortal']['preview']));

		// Prepare the Textcontent for BBC, only the first bbc will be correct detected! (SMF Support only 1 per page with the standard function)
		$firstBBCFound = false;
		foreach ($context['SPortal']['block']['options'] as $name => $type)
		{
			// Selectable Boards :D
			if ($type == 'board_select' || $type == 'boards')
			{
				if (empty($boards))
				{
					require_once($sourcedir.'/Subs-Boards.php');
					getBoardTree();
				}
				$context['SPortal']['block']['board_options'][$name] = array();
				$config_variable = !empty($context['SPortal']['block']['parameters'][$name]) ? $context['SPortal']['block']['parameters'][$name] : array();
				$config_variable = !is_array($config_variable) ? explode('|', $config_variable) : $config_variable;
				$context['SPortal']['block']['board_options'][$name] = array();

				// Create the list for this Item
				foreach ($boards as $board)
				{
					if (!empty($board['redirect'])) // Ignore the redirected boards :)
						continue;

					$context['SPortal']['block']['board_options'][$name][$board['id']] = array(
						'value' => $board['id'],
						'text' => $board['name'],
						'selected' => in_array($board['id'], $config_variable),
					);
				}
			}
			// Prepare the Textcontent for BBC, only the first bbc will be correct detected! (SMF Support only 1 per page with the standard function)
			elseif($type == 'bbc')
			{
				if(!$firstBBCFound)
				{
					$firstBBCFound = true;
					require_once($sourcedir.'/Subs-Post.php');
					$context['post_box_name'] = 'bbc_'.$name;
					$context['post_form'] = 'sp_block';
					$context['SPortal']['bbc'] = 'bbc_'.$name;
				}
				else
					$context['SPortal']['block']['options'][$name] = 'textarea';
			}
		}

		$context['sub_template'] = 'block_edit';
		$context['page_title'] = $context['SPortal']['is_new'] ? $txt['sp-blocksAdd'] : $txt['sp-blocksEdit'];
	}

	if (!empty($_POST['add_block']))
	{
		checkSession();

		if ($_POST['block_type'] == 'sp_php' && !allowedTo('admin_forum'))
			fatal_lang_error('cannot_admin_forum', false);

		if (!isset($_POST['block_name']) || $func['htmltrim']($func['htmlspecialchars']($_POST['block_name']), ENT_QUOTES) === '')
			fatal_lang_error('error_sp_name_empty', false);

		if ($_POST['block_type'] == 'sp_php' && !empty($_POST['parameters']['content']) && empty($modSettings['sp_disable_php_validation']))
		{
			$error = sp_validate_php(stripslashes($_POST['parameters']['content']));

			if ($error)
				fatal_lang_error('error_sp_php_' . $error, false);
		}

		if (!empty($_REQUEST['block_id']))
			$current_data = current(getBlockInfo(null, $_REQUEST['block_id']));

		if (!empty($_POST['placement']) && (($_POST['placement'] == 'before') || ($_POST['placement'] == 'after')))
		{
			if (!empty($current_data))
				$current_row = $current_data['row'];
			else
				$current_row = null;

			if ($_POST['placement'] == 'before')
				$row = (int) $_POST['block_row'];
			else
				$row = (int) $_POST['block_row'] + 1;

			if (!empty($current_row) && ($row > $current_row))
			{
				$row = $row - 1;

				db_query("
					UPDATE {$db_prefix}sp_blocks
					SET row = row - 1
					WHERE col = ".(int) $_POST['block_column']."
						AND row > $current_row
						AND row <= $row", __FILE__, __LINE__);
			}
			else
			{
				db_query("
					UPDATE {$db_prefix}sp_blocks
					SET row = row + 1
					WHERE col = " . (int) $_POST['block_column'] . "
						AND row >= $row" . (!empty($current_row) ? "
						AND row < $current_row" : ""), __FILE__, __LINE__);
			}
		}
		elseif (!empty($_POST['placement']) && $_POST['placement'] == 'nochange')
			$row = 0;
		else
		{
			$request = db_query("
				SELECT row
				FROM {$db_prefix}sp_blocks
				WHERE col = " . (int) $_POST['block_column'] . (!empty($_REQUEST['block_id']) ? "
            AND id_block != " . (int) $_REQUEST['block_id'] : "") . "
				ORDER BY row DESC
				LIMIT 1",__FILE__, __LINE__);
			list ($row) = mysql_fetch_row($request);
			mysql_free_result($request);

			$row = $row + 1;
		}

		$type_parameters = $_POST['block_type'](array(), 0, true);

		if (!empty($_POST['parameters']) && is_array($_POST['parameters']) && !empty($type_parameters))
		{
			foreach ($type_parameters as $name => $type)
			{
				if (isset($_POST['parameters'][$name]))
				{
					if ($type == 'int' || $type == 'select')
						$_POST['parameters'][$name] = (int) $_POST['parameters'][$name];
					elseif ($type == 'boards' || $type == 'board_select')
						$_POST['parameters'][$name] = is_array($_POST['parameters'][$name]) ? implode('|', $_POST['parameters'][$name]) : $_POST['parameters'][$name];
					elseif ($type == 'text' || $type == 'textarea' || is_array($type))
						$_POST['parameters'][$name] = addslashes($func['htmlspecialchars'](stripslashes($_POST['parameters'][$name]), ENT_QUOTES));
					elseif ($type == 'check')
						$_POST['parameters'][$name] = !empty($_POST['parameters'][$name]) ? 1 : 0;
				}
			}
		}
		else
			$_POST['parameters'] = array();

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

		if (empty($_POST['display_advanced']))
		{
			if (!empty($_POST['display_simple']) && in_array($_POST['display_simple'], array('all', 'sportal', 'sforum', 'allaction', 'allboard', 'allpages')))
				$display = $_POST['display_simple'];
			else
				$display = '';

			$custom = '';
		}
		else
		{
			$display = array();

			if (!empty($_POST['display_actions']))
				foreach ($_POST['display_actions'] as $action)
					$display[] = addslashes($func['htmlspecialchars'](stripslashes($action), ENT_QUOTES));

			if (!empty($_POST['display_boards']))
				foreach ($_POST['display_boards'] as $board)
					$display[] = 'b' . ((int) substr($board, 1));

			if (!empty($_POST['display_pages']))
				foreach ($_POST['display_pages'] as $page)
					$display[] = 'p' . ((int) substr($page, 1));

			$display = empty($display) ? '' : implode(',', $display);

			if (!allowedTo('admin_forum') && isset($current_data['display_custom']) && substr($current_data['display_custom'], 0, 4) === '$php')
				$custom = $current_data['display_custom'];
			elseif (!empty($_POST['display_custom']))
			{
				if (allowedTo('admin_forum') && substr($_POST['display_custom'], 0, 4) === '$php')
					$custom = addslashes($func['htmlspecialchars']($func['htmltrim'](stripslashes($_POST['display_custom'])), ENT_QUOTES));
				else
				{
					$custom = array();
					$temp = explode(',', $_POST['display_custom']);

					foreach ($temp as $action)
						$custom[] = addslashes($func['htmlspecialchars']($func['htmltrim'](stripslashes($action)), ENT_QUOTES));

					$custom = empty($custom) ? '' : implode(',', $custom);
				}
			}
			else
				$custom = '';
		}

		$blockInfo = array(
			'id' => (int) $_POST['block_id'],
			'label' => addslashes($func['htmlspecialchars'](stripslashes($_POST['block_name']), ENT_QUOTES)),
			'type' => $_POST['block_type'],
			'col' => $_POST['block_column'],
			'row' => $row,
			'permission_set' => $permission_set,
			'groups_allowed' => $groups_allowed,
			'groups_denied' => $groups_denied,
			'state' => !empty($_POST['block_active']) ? 1 : 0,
			'force_view' => !empty($_POST['block_force']) ? 1 : 0,
			'display' => $display,
			'display_custom' => $custom,
			'style' => sportal_parse_style('implode'),
		);

		if ($context['SPortal']['is_new'])
		{
			unset($blockInfo['id']);

			$insert = array();
			foreach ($blockInfo as $key => $info)
				$insert[$key] = "'" . $info . "'";

			db_query("
				INSERT INTO {$db_prefix}sp_blocks
					(" . implode(', ', array_keys($insert)) . ")
				VALUES
					(" . implode(', ', $insert) . ")", __FILE__, __LINE__);

			$blockInfo['id'] = db_insert_id();
		}
		else
		{
			$block_fields = array(
				"label = '$blockInfo[label]'",
				"state = '$blockInfo[state]'",
				"force_view = '$blockInfo[force_view]'",
				"permission_set = '$blockInfo[permission_set]'",
				"groups_allowed = '$blockInfo[groups_allowed]'",
				"groups_denied = '$blockInfo[groups_denied]'",
				"display = '$blockInfo[display]'",
				"display_custom = '$blockInfo[display_custom]'",
				"style = '$blockInfo[style]'",
			);

			if (!empty($blockInfo['row']))
				$block_fields[] = "row = '$blockInfo[row]'";
			else
				unset($blockInfo['row']);

			db_query("
				UPDATE {$db_prefix}sp_blocks
				SET " . implode(', ', $block_fields) . "
				WHERE ID_BLOCK = $blockInfo[id]
				LIMIT 1", __FILE__, __LINE__);

			db_query("
				DELETE FROM {$db_prefix}sp_parameters
				WHERE ID_BLOCK = $blockInfo[id]", __FILE__, __LINE__);
		}

		if (!empty($_POST['parameters']))
		{
			$parameters = '';
			foreach ($_POST['parameters'] as $variable => $value)
				$parameters .= "('$blockInfo[id]', '$variable', '$value'),";

			if (substr($parameters, -1) == ',')
				$parameters = substr($parameters, 0, -1);

			db_query("
				INSERT INTO {$db_prefix}sp_parameters
					(ID_BLOCK, variable, value)
				VALUES
					$parameters", __FILE__, __LINE__);
		}

		redirectexit('action=manageportal;area=portalblocks;sa=list');
	}
}

// Function for moving a block.
function sportal_admin_block_move()
{
	global $db_prefix;

	checkSession('get');

	if (empty($_REQUEST['block_id']))
		fatal_lang_error('error_sp_id_empty', false);
	else
		$block_id = (int) $_REQUEST['block_id'];

	if (empty($_REQUEST['col']) || $_REQUEST['col'] < 1 || $_REQUEST['col'] > 6)
		fatal_lang_error('error_sp_side_wrong', false);
	else
		$target_side = (int) $_REQUEST['col'];

	if (empty($_REQUEST['row']))
	{
		$request =  db_query("
			SELECT MAX(row)
			FROM {$db_prefix}sp_blocks
			WHERE col = $target_side
			LIMIT 1", __FILE__, __LINE__);
		list ($target_row) = mysql_fetch_row($request);
		mysql_free_result($request);

		$target_row += 1;
	}
	else
		$target_row = (int) $_REQUEST['row'];

	$request =  db_query("
		SELECT col, row
		FROM {$db_prefix}sp_blocks
		WHERE ID_BLOCK = $block_id
		LIMIT 1", __FILE__, __LINE__);
	list ($current_side, $current_row) = mysql_fetch_row($request);
	mysql_free_result($request);

	if ($current_side != $target_side || $current_row + 1 != $target_row)
	{
		if ($current_side != $target_side)
		{
			$current_row = 100;
			db_query("
				UPDATE {$db_prefix}sp_blocks
				SET col = $target_side, row = $current_row
				WHERE ID_BLOCK = $block_id", __FILE__, __LINE__);
		}

		db_query("
			UPDATE {$db_prefix}sp_blocks
			SET row = row + 1
			WHERE col = $target_side
				AND row >= $target_row", __FILE__, __LINE__);

		db_query("
			UPDATE {$db_prefix}sp_blocks
			SET row = $target_row
			WHERE ID_BLOCK = $block_id", __FILE__, __LINE__);

		foreach (array_unique(array($current_side, $target_side)) as $side)
			fixColumnRows($side);
	}

	redirectexit('action=manageportal;area=portalblocks');
}

// Function for deteling a block.
function sportal_admin_block_delete()
{
	global $db_prefix;

	// Check if he can?
	checkSession('get');

	// Make sure ID is an integer.
	$_REQUEST['block_id'] = (int) $_REQUEST['block_id'];

	// Do we have that?
	if(empty($_REQUEST['block_id']))
		fatal_lang_error('error_sp_id_empty', false);

	// Make sure column ID is an integer too.
	$_REQUEST['col'] = (int)$_REQUEST['col'];

	//Only Admins can Remove PHP Blocks :D
	if(!allowedTo('admin_forum')) {
		$context['SPortal']['block_info'] = current(getBlockInfo(null, $_REQUEST['block_id']));
		if($context['SPortal']['block_info']['type'] == 'sp_php' && !allowedTo('admin_forum'))
			fatal_lang_error('cannot_admin_forum', false);
	}

	// We don't need it anymore.
	db_query("
		DELETE FROM {$db_prefix}sp_blocks
		WHERE ID_BLOCK = '$_REQUEST[block_id]'
		LIMIT 1", __FILE__, __LINE__);

	db_query("
		DELETE FROM {$db_prefix}sp_parameters
		WHERE ID_BLOCK = '$_REQUEST[block_id]'", __FILE__, __LINE__);

	// Fix column rows.
	fixColumnRows($_REQUEST['col']);

	// Return back to the block list.
	redirectexit('action=manageportal;area=portalblocks;sa=list');
}

?>