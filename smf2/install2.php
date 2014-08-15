<?php
/**********************************************************************************
* install2.php                                                                    *
***********************************************************************************
* SimplePortal                                                                    *
* SMF Modification Project Founded by [SiNaN] (sinan@simplemachines.org)          *
* =============================================================================== *
* Software Version:           SimplePortal 2.3.5                                  *
* Software by:                SimplePortal Team (http://www.simpleportal.net)     *
* Copyright 2008-2009 by:     SimplePortal Team (http://www.simpleportal.net)     *
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

// Handle running this file by using SSI.php
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
{
	$_GET['debug'] = 'Blue Dream!';
	require_once(dirname(__FILE__) . '/SSI.php');
}
// Hmm... no SSI.php and no SMF?
elseif (!defined('SMF'))
	die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

global $smcFunc, $db_prefix, $modSettings, $sourcedir, $boarddir, $settings, $db_package_log, $package_cache;

// Make sure that we have the package database functions.
if (!array_key_exists('db_add_column', $smcFunc))
	db_extend('packages');

// Load all tables.
$tables = array(
	'sp_articles' => array(
		'columns' => array(
			array(
				'name' => 'id_article',
				'type' => 'int',
				'size' => '10',
				'auto' => true,
				'deprecated_name' => 'ID_ARTICLE',
			),
			array(
				'name' => 'id_category',
				'type' => 'int',
				'size' => '10',
				'default' => 0,
				'deprecated_name' => 'ID_CATEGORY',
			),
			array(
				'name' => 'id_message',
				'type' => 'int',
				'size' => '10',
				'default' => 0,
				'deprecated_name' => 'ID_MESSAGE',
			),
			array(
				'name' => 'approved',
				'type' => 'tinyint',
				'size' => '2',
				'default' => 0,
			),
		),
		'indexes' => array(
			array(
				'type' => 'primary',
				'columns' => array('id_article'),
			),
		),
	),
	'sp_blocks' => array(
		'columns' => array(
			array(
				'name' => 'id_block',
				'type' => 'int',
				'size' => '10',
				'auto' => true,
				'deprecated_name' => 'ID_BLOCK',
			),
			array(
				'name' => 'label',
				'type' => 'tinytext',
			),
			array(
				'name' => 'type',
				'type' => 'text',
			),
			array(
				'name' => 'col',
				'type' => 'tinyint',
				'size' => '4',
				'default' => 0,
			),
			array(
				'name' => 'row',
				'type' => 'tinyint',
				'size' => '4',
				'default' => 0,
			),
			array(
				'name' => 'permission_set',
				'type' => 'tinyint',
				'size' => '4',
				'default' => 0,
			),
			array(
				'name' => 'groups_allowed',
				'type' => 'varchar',
				'size' => 255,
				'default' => '',
			),
			array(
				'name' => 'groups_denied',
				'type' => 'varchar',
				'size' => 255,
				'default' => '',
			),
			array(
				'name' => 'state',
				'type' => 'tinyint',
				'size' => '4',
				'default' => 1,
			),
			array(
				'name' => 'force_view',
				'type' => 'tinyint',
				'size' => '2',
				'default' => 0,
			),
			array(
				'name' => 'display',
				'type' => 'text',
			),
			array(
				'name' => 'display_custom',
				'type' => 'text',
			),
			array(
				'name' => 'style',
				'type' => 'text',
			),
		),
		'indexes' => array(
			array(
				'type' => 'primary',
				'columns' => array('id_block'),
			),
			array(
				'type' => 'index',
				'columns' => array('state'),
			),
		),
	),
	'sp_categories' => array(
		'columns' => array(
			array(
				'name' => 'id_category',
				'type' => 'int',
				'size' => '10',
				'auto' => true,
				'deprecated_name' => 'ID_CATEGORY',
			),
			array(
				'name' => 'name',
				'type' => 'tinytext',
			),
			array(
				'name' => 'picture',
				'type' => 'tinytext',
			),
			array(
				'name' => 'articles',
				'type' => 'tinyint',
				'size' => '4',
				'default' => 0,
			),
			array(
				'name' => 'publish',
				'type' => 'tinyint',
				'size' => '1',
				'default' => 0,
			),
		),
		'indexes' => array(
			array(
				'type' => 'primary',
				'columns' => array('id_category'),
			),
		),
	),
	'sp_functions' => array(
		'columns' => array(
			array(
				'name' => 'id_function',
				'type' => 'tinyint',
				'size' => '4',
				'auto' => true,
				'deprecated_name' => 'ID_FUNCTION',
			),
			array(
				'name' => 'function_order',
				'type' => 'tinyint',
				'size' => '4',
				'default' => 0,
			),
			array(
				'name' => 'name',
				'type' => 'tinytext',
			),
		),
		'indexes' => array(
			array(
				'type' => 'primary',
				'columns' => array('id_function'),
			),
		),
	),
	'sp_pages' => array(
		'columns' => array(
			array(
				'name' => 'id_page',
				'type' => 'int',
				'size' => 10,
				'auto' => true,
				'deprecated_name' => 'ID_PAGE',
			),
			array(
				'name' => 'namespace',
				'type' => 'tinytext',
			),
			array(
				'name' => 'title',
				'type' => 'tinytext',
			),
			array(
				'name' => 'body',
				'type' => 'text',
			),
			array(
				'name' => 'type',
				'type' => 'tinytext',
			),
			array(
				'name' => 'permission_set',
				'type' => 'tinyint',
				'size' => '4',
				'default' => 0,
			),
			array(
				'name' => 'groups_allowed',
				'type' => 'varchar',
				'size' => 255,
				'default' => '',
			),
			array(
				'name' => 'groups_denied',
				'type' => 'varchar',
				'size' => 255,
				'default' => '',
			),
			array(
				'name' => 'views',
				'type' => 'int',
				'size' => 10,
				'default' => 0,
			),
			array(
				'name' => 'style',
				'type' => 'text',
			),
			array(
				'name' => 'status',
				'type' => 'tinyint',
				'size' => 4,
				'default' => 1,
			),
		),
		'indexes' => array(
			array(
				'type' => 'primary',
				'columns' => array('id_page'),
			),
		),
	),
	'sp_parameters' => array(
		'columns' => array(
			array(
				'name' => 'id_block',
				'type' => 'int',
				'size' => '10',
				'default' => 0,
				'deprecated_name' => 'ID_BLOCK',
			),
			array(
				'name' => 'variable',
				'type' => 'varchar',
				'size' => 255,
				'default' => '',
			),
			array(
				'name' => 'value',
				'type' => 'text',
			),
		),
		'indexes' => array(
			array(
				'type' => 'primary',
				'columns' => array('id_block', 'variable')
			),
			array(
				'type' => 'key',
				'columns' => array('variable')
			),
		),
	),
	'sp_shoutboxes' => array(
		'columns' => array(
			array(
				'name' => 'id_shoutbox',
				'type' => 'int',
				'size' => 10,
				'auto' => true,
				'deprecated_name' => 'ID_SHOUTBOX',
			),
			array(
				'name' => 'name',
				'type' => 'tinytext',
			),
			array(
				'name' => 'permission_set',
				'type' => 'tinyint',
				'size' => '4',
				'default' => 0,
			),
			array(
				'name' => 'groups_allowed',
				'type' => 'varchar',
				'size' => 255,
				'default' => '',
			),
			array(
				'name' => 'groups_denied',
				'type' => 'varchar',
				'size' => 255,
				'default' => '',
			),
			array(
				'name' => 'moderator_groups',
				'type' => 'text',
			),
			array(
				'name' => 'warning',
				'type' => 'text',
			),
			array(
				'name' => 'allowed_bbc',
				'type' => 'text',
			),
			array(
				'name' => 'height',
				'type' => 'smallint',
				'size' => 5,
				'default' => 200,
			),
			array(
				'name' => 'num_show',
				'type' => 'smallint',
				'size' => 5,
				'default' => 20,
			),
			array(
				'name' => 'num_max',
				'type' => 'mediumint',
				'size' => 8,
				'default' => 1000,
			),
			array(
				'name' => 'refresh',
				'type' => 'tinyint',
				'size' => 4,
				'default' => 1,
			),
			array(
				'name' => 'reverse',
				'type' => 'tinyint',
				'size' => 4,
				'default' => 0,
			),
			array(
				'name' => 'caching',
				'type' => 'tinyint',
				'size' => 4,
				'default' => 1,
			),
			array(
				'name' => 'status',
				'type' => 'tinyint',
				'size' => 4,
				'default' => 1,
			),
			array(
				'name' => 'num_shouts',
				'type' => 'mediumint',
				'size' => 8,
				'default' => 0,
			),
			array(
				'name' => 'last_update',
				'type' => 'int',
				'size' => 10,
				'default' => 0,
			),
		),
		'indexes' => array(
			array(
				'type' => 'primary',
				'columns' => array('id_shoutbox'),
			),
		),
	),
	'sp_shouts' => array(
		'columns' => array(
			array(
				'name' => 'id_shout',
				'type' => 'mediumint',
				'size' => '8',
				'auto' => true,
				'deprecated_name' => 'ID_SHOUT',
			),
			array(
				'name' => 'id_shoutbox',
				'type' => 'int',
				'size' => 10,
				'default' => 0,
				'deprecated_name' => 'ID_SHOUTBOX',
			),
			array(
				'name' => 'id_member',
				'type' => 'mediumint',
				'size' => '8',
				'default' => 0,
				'deprecated_name' => 'ID_MEMBER',
			),
			array(
				'name' => 'member_name',
				'type' => 'varchar',
				'size' => '80',
				'default' => 0,
				'deprecated_name' => 'memberName',
			),
			array(
				'name' => 'log_time',
				'type' => 'int',
				'size' => '10',
				'default' => 0,
			),
			array(
				'name' => 'body',
				'type' => 'text',
			),
		),
		'indexes' => array(
			array(
				'type' => 'primary',
				'columns' => array('id_shout'),
			),
		),
	),
);

$deprecated_fields = array(
	'sp_blocks' => array(
		'content',
		'parameters',
		'permission_type',
		'allowed_groups',
	),
	'sp_pages' => array(
		'permission_type',
		'allowed_groups',
	),
	'sp_shoutboxes' => array(
		'permission_type',
		'allowed_groups',
	),
);

// We always need a fresh functions table.
$smcFunc['db_drop_table']('{db_prefix}sp_functions');

$current_tables = $smcFunc['db_list_tables'](false, '%sp%');
$real_prefix = preg_match('~^(`?)(.+?)\\1\\.(.*?)$~', $db_prefix, $match) === 1 ? $match[3] : $db_prefix;
$info = '<ul>';

// Loop through each table and do what needed.
foreach ($tables as $table => $data)
{
	if (in_array(strtolower($real_prefix . $table), array_map('strtolower', $current_tables)))
	{
		$info .= '
	<li>"' . $table . '" table exists, updating table structure.
		<ul>';

		foreach ($data['columns'] as $column)
		{
			if (!isset($column['deprecated_name']) || !$smcFunc['db_change_column']('{db_prefix}' . $table, $column['deprecated_name'], $column))
				$smcFunc['db_add_column']('{db_prefix}' . $table, $column);
		}

		$info .= '
			<li>Table columns updated.</li>';

		foreach ($data['indexes'] as $index)
			$smcFunc['db_add_index']('{db_prefix}' . $table, $index, array(), 'ignore');

		$info .= '
			<li>Table indexes updated.</li>
		</ul>
	</li>';
	}
	else
	{
		$smcFunc['db_create_table']('{db_prefix}' . $table, $data['columns'], $data['indexes'], array(), 'ignore');

		$info .= '<li>"' . $table . '" table created.</li>';
	}
}

// Insert current functions.
$smcFunc['db_insert']('ignore',
	'{db_prefix}sp_functions',
	array(
		'id_function' => 'int',
		'function_order' => 'int',
		'name' => 'string',
	),
	array(
		array(1, 1, 'sp_userInfo'),
		array(2, 2, 'sp_latestMember'),
		array(3, 3, 'sp_whosOnline'),
		array(5, 4, 'sp_boardStats'),
		array(7, 5, 'sp_topPoster'),
		array(35, 6, 'sp_topStatsMember'),
		array(23, 7, 'sp_recent'),
		array(9, 8, 'sp_topTopics'),
		array(8, 9, 'sp_topBoards'),
		array(4, 10, 'sp_showPoll'),
		array(12, 11, 'sp_boardNews'),
		array(6, 12, 'sp_quickSearch'),
		array(13, 13, 'sp_news'),
		array(20, 14, 'sp_attachmentImage'),
		array(21, 15, 'sp_attachmentRecent'),
		array(27, 16, 'sp_calendar'),
		array(24, 17, 'sp_calendarInformation'),
		array(25, 18, 'sp_rssFeed'),
		array(28, 19, 'sp_theme_select'),
		array(29, 20, 'sp_staff'),
		array(31, 21, 'sp_articles'),
		array(36, 22, 'sp_shoutbox'),
		array(26, 23, 'sp_gallery'),
		array(32, 24, 'sp_arcade'),
		array(33, 25, 'sp_shop'),
		array(30, 26, 'sp_blog'),
		array(10, 27, 'sp_menu'),
		array(19, 98, 'sp_bbc'),
		array(17, 99, 'sp_html'),
		array(18, 100, 'sp_php'),
	),
	array('id_function')
);

$info .= '
	<li>"sp_functions" table data inserted.</li>';

$result = $smcFunc['db_query']('','
	SELECT id_block
	FROM {db_prefix}sp_blocks
	LIMIT 1',
	array(
	)
);
list ($has_block) = $smcFunc['db_fetch_row']($result);
$smcFunc['db_free_result']($result);

if (empty($has_block))
{
	foreach ($deprecated_fields as $table => $fields)
		foreach ($fields as $field)
			$smcFunc['db_remove_column']('{db_prefix}' . $table, $field);

	$welcome_text = '<h2 style="text-align: center;">Welcome to SimplePortal!</h2>
<p>SimplePortal is one of several portal mods for Simple Machines Forum (SMF). Although always developing, SimplePortal is produced with the user in mind first. User feedback is the number one method of growth for SimplePortal, and our users are always finding ways for SimplePortal to grow. SimplePortal stays competative with other portal software by adding numerous user-requested features such as articles, block types and the ability to completely customize the portal page.</p>
<p>All this and SimplePortal has remained Simple! SimplePortal is built for simplicity and ease of use; ensuring the average forum administrator can install SimplePortal, configure a few settings, and show off the brand new portal to the users in minutes. Confusing menus, undesired pre-loaded blocks and settings that cannot be found are all avoided as much as possible. Because when it comes down to it, SimplePortal is YOUR portal, and should reflect your taste as much as possible.</p>
<p><strong>Ultimate Simplicity</strong>
<br />
The simplest portal you can ever think of... You only need a few clicks to install it through Package Manager. A few more to create your own blocks and articles. Your portal is ready to go within a couple of minutes, and simple to customise to reflect YOU.</p>
<p><strong>Install Friendly</strong>
<br />
With the ingenius design of install and update packages, SimplePortal is incredibly install and update friendly. You will never need any manual changes even on a heavily modified forum.</p>
<p><strong>Incredible Theme Support</strong>
<br />
The simple but powerful structure of SimplePortal brings you wide-range theme support too. You can use SimplePortal with all SMF themes by just adding a button for it.</p>
<p><strong>Professional Support</strong>
<br />
SimplePortal offers high quality professional support with its own well known support team.</p>';

	$default_blocks = array(
		'user_info' => array(
			'label' => 'User Info',
			'type' => 'sp_userInfo',
			'col' => 1,
			'row' => 1,
			'permission_set' => '3',
			'display' => '',
			'display_custom' => '',
			'style' => '',
		),
		'whos_online' => array(
			'label' => 'Who&#039;s Online',
			'type' => 'sp_whosOnline',
			'col' => 1,
			'row' => 2,
			'permission_set' => '3',
			'display' => '',
			'display_custom' => '',
			'style' => '',
		),
		'board_stats' => array(
			'label' => 'Board Stats',
			'type' => 'sp_boardStats',
			'col' => 1,
			'row' => 3,
			'permission_set' => '3',
			'display' => '',
			'display_custom' => '',
			'style' => '',
		),
		'theme_select' => array(
			'label' => 'Theme Select',
			'type' => 'sp_theme_select',
			'col' => 1,
			'row' => 4,
			'permission_set' => '3',
			'display' => '',
			'display_custom' => '',
			'style' => '',
		),
		'search' => array(
			'label' => 'Search',
			'type' => 'sp_quickSearch',
			'col' => 1,
			'row' => 5,
			'permission_set' => '3',
			'display' => '',
			'display_custom' => '',
			'style' => '',
		),
		'news' => array(
			'label' => 'News',
			'type' => 'sp_news',
			'col' => 2,
			'row' => 1,
			'permission_set' => '3',
			'display' => '',
			'display_custom' => '',
			'style' => 'title_default_class~|title_custom_class~|title_custom_style~|body_default_class~windowbg|body_custom_class~|body_custom_style~|no_title~1|no_body~',
		),
		'welcome' => array(
			'label' => 'Welcome',
			'type' => 'sp_html',
			'col' => 2,
			'row' => 2,
			'permission_set' => '3',
			'display' => '',
			'display_custom' => '',
			'style' => 'title_default_class~|title_custom_class~|title_custom_style~|body_default_class~windowbg|body_custom_class~|body_custom_style~|no_title~1|no_body~',
		),
		'board_news' => array(
			'label' => 'Board News',
			'type' => 'sp_boardNews',
			'col' => 2,
			'row' => 3,
			'permission_set' => '3',
			'display' => '',
			'display_custom' => '',
			'style' => '',
		),
		'recent_topics' => array(
			'label' => 'Recent Topics',
			'type' => 'sp_recent',
			'col' => 3,
			'row' => 1,
			'permission_set' => '3',
			'display' => '',
			'display_custom' => '',
			'style' => '',
		),
		'top_poster' => array(
			'label' => 'Top Poster',
			'type' => 'sp_topPoster',
			'col' => 4,
			'row' => 1,
			'permission_set' => '3',
			'display' => '',
			'display_custom' => '',
			'style' => '',
		),
		'recent_posts' => array(
			'label' => 'Recent Posts',
			'type' => 'sp_recent',
			'col' => 4,
			'row' => 2,
			'permission_set' => '3',
			'display' => '',
			'display_custom' => '',
			'style' => '',
		),
		'staff' => array(
			'label' => 'Forum Staff',
			'type' => 'sp_staff',
			'col' => 4,
			'row' => 3,
			'permission_set' => '3',
			'display' => '',
			'display_custom' => '',
			'style' => '',
		),
		'calendar' => array(
			'label' => 'Calendar',
			'type' => 'sp_calendar',
			'col' => 4,
			'row' => 4,
			'permission_set' => '3',
			'display' => '',
			'display_custom' => '',
			'style' => '',
		),
		'top_boards' => array(
			'label' => 'Top Boards',
			'type' => 'sp_topBoards',
			'col' => 4,
			'row' => 5,
			'permission_set' => '3',
			'display' => '',
			'display_custom' => '',
			'style' => '',
		),
	);

	$smcFunc['db_insert']('ignore',
		'{db_prefix}sp_blocks',
		array(
			'label' => 'text',
			'type' => 'text',
			'col' => 'int',
			'row' => 'int',
			'permission_set' => 'int',
			'display' => 'text',
			'display_custom' => 'text',
			'style' => 'text',
		),
		$default_blocks,
		array('id_block')
	);

	$request = $smcFunc['db_query']('', '
		SELECT MIN(id_block) AS id, type
		FROM {db_prefix}sp_blocks
		WHERE type IN ({array_string:types})
		GROUP BY type
		LIMIT 4',
		array(
			'types' => array('sp_html', 'sp_boardNews', 'sp_calendar', 'sp_recent'),
		)
	);
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$block_ids[$row['type']] = $row['id'];
	$smcFunc['db_free_result']($request);

	$default_parameters = array(
		array(
			'id_block' => $block_ids['sp_html'],
			'variable' => 'content',
			'value' => htmlspecialchars($welcome_text),
		),
		array(
			'id_block' => $block_ids['sp_boardNews'],
			'variable' => 'avatar',
			'value' => 1,
		),
		array(
			'id_block' => $block_ids['sp_boardNews'],
			'variable' => 'per_page',
			'value' => 3,
		),
		array(
			'id_block' => $block_ids['sp_calendar'],
			'variable' => 'events',
			'value' => 1,
		),
		array(
			'id_block' => $block_ids['sp_calendar'],
			'variable' => 'birthdays',
			'value' => 1,
		),
		array(
			'id_block' => $block_ids['sp_calendar'],
			'variable' => 'holidays',
			'value' => 1,
		),
		array(
			'id_block' => $block_ids['sp_recent'],
			'variable' => 'type',
			'value' => 1,
		),
		array(
			'id_block' => $block_ids['sp_recent'],
			'variable' => 'display',
			'value' => 1,
		),
	);

	$smcFunc['db_insert']('replace',
		'{db_prefix}sp_parameters',
		array(
			'id_block' => 'int',
			'variable' => 'text',
			'value' => 'text',
		),
		$default_parameters,
		array()
	);

	$info .= '
	<li>Default blocks created.</li>';
}
else
{
	$permission_updates = array('blocks' => 'block', 'pages' => 'page', 'shoutboxes' => 'shoutbox');
	foreach ($permission_updates as $table => $field)
	{
		$columns = $smcFunc['db_list_columns']('{db_prefix}' . 'sp_' . $table, false);
		if (in_array('permission_type', $columns))
		{
			$request = $smcFunc['db_query']('', '
				SELECT id_{raw:field}, permission_type, allowed_groups
				FROM {db_prefix}sp_{raw:table}',
				array(
					'field' => $field,
					'table' => $table,
				)
			);
			$permissions = array();
			while ($row = $smcFunc['db_fetch_assoc']($request))
				$permissions[] = $row;
			$smcFunc['db_free_result']($request);

			$modified = array();
			foreach ($permissions as $item)
			{
				$set = $allowed = $denied = '';

				if ($item['permission_type'] == 2)
					$set = '3';
				elseif ($item['allowed_groups'] == '-1')
					$set = '1';
				else
				{
					$set = '0';
					$allowed = $item['allowed_groups'];
				}

				$modified[] = array(
					'id' => $item['id_' . $field],
					'permission_set' => $set,
					'groups_allowed' => $allowed,
					'groups_denied' => $denied,
				);
			}

			foreach ($modified as $item)
			{
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}sp_{raw:table}
					SET
						permission_set = {string:permission_set},
						groups_allowed = {string:groups_allowed},
						groups_denied = {string:groups_denied}
					WHERE id_{raw:field} = {int:id}',
					array_merge($item, array(
						'table' => $table,
						'field' => $field,
					))
				);
			}
		}
	}

	if (empty($modSettings['sp_version']) || $modSettings['sp_version'] < '2.3.6')
	{
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}sp_blocks
			SET style = {string:blank}
			WHERE type = {string:type}',
			array(
				'blank' => '',
				'type' => 'sp_boardNews',
			)
		);
	}

	if (empty($modSettings['sp_version']) || $modSettings['sp_version'] < '2.3')
	{
		$request = $smcFunc['db_query']('', '
			SELECT id_block, type
			FROM {db_prefix}sp_blocks
			WHERE type IN ({array_string:types})',
			array(
				'types' => array('sp_recentTopics', 'sp_recentPosts'),
			)
		);
		$replace_blocks = array();
		$add_parameters = array();
		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			$replace_blocks[] = $row['id_block'];
			$add_parameters[] = array(
				'id_block' => $row['id_block'],
				'variable' => 'display',
				'value' => 1,
			);
			$add_parameters[] = array(
				'id_block' => $row['id_block'],
				'variable' => 'type',
				'value' => $row['type'] == 'sp_recentPosts' ? 0 : 1,
			);
		}
		$smcFunc['db_free_result']($request);

		if (!empty($replace_blocks) && !empty($add_parameters))
		{
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}sp_blocks
				SET type = {string:new_type}
				WHERE id_block IN ({array_int:block_ids})',
				array(
					'new_type' => 'sp_recent',
					'block_ids' => $replace_blocks,
				)
			);

			$smcFunc['db_insert']('replace',
				'{db_prefix}sp_parameters',
				array(
					'id_block' => 'int',
					'variable' => 'text',
					'value' => 'text',
				),
				$add_parameters,
				array()
			);
		}
	}

	if (empty($modSettings['sp_version']) || $modSettings['sp_version'] < '2.2')
	{
		$block_updates = array(
			array(
				'old' => 'sp_smfGallery',
				'new' => 'sp_gallery'
			),
			array(
				'old' => 'sp_mgallery',
				'new' => 'sp_gallery'
			),
		);

		foreach ($block_updates as $type)
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}sp_blocks
				SET type = {string:new}
				WHERE type = {string:old}',
				$type
			);

		$current_columns = $smcFunc['db_list_columns']('{db_prefix}' . 'sp_blocks', false);
		if (in_array('content', $current_columns))
		{
			require_once($sourcedir . '/PortalBlocks.php');
			$old_parameters = array();

			$request = $smcFunc['db_query']('', '
				SELECT id_block, type, content, parameters
				FROM {db_prefix}sp_blocks',
				array(
				)
			);
			while ($row = $smcFunc['db_fetch_assoc']($request))
			{
				if (in_array($row['type'], array('sp_bbc', 'sp_html', 'sp_php')))
				{
					$old_parameters[] = array(
						'id_block' => $row['id_block'],
						'variable' => 'content',
						'value' => $row['content'],
					);
				}
				elseif (function_exists($row['type']))
				{
					$type_parameters = $row['type'](array(), 0, true);

					if (empty($row['parameters']) || empty($type_parameters))
						continue;

					$row['parameters'] = explode(',', $row['parameters']);

					foreach ($type_parameters as $variable => $value)
					{
						$old = current($row['parameters']);
						next($row['parameters']);

						if (empty($old))
							continue;

						$old_parameters[] = array(
							'id_block' => $row['id_block'],
							'variable' => $variable,
							'value' => $old,
						);
					}
				}
				else
					continue;
			}
			$smcFunc['db_free_result']($request);

			if (!empty($old_parameters))
			{
				$smcFunc['db_insert']('replace',
					'{db_prefix}sp_parameters',
					array(
						'id_block' => 'int',
						'variable' => 'text',
						'value' => 'text',
					),
					$old_parameters,
					array()
				);
			}
		}
	}

	foreach ($deprecated_fields as $table => $fields)
		foreach ($fields as $field)
			$smcFunc['db_remove_column']('{db_prefix}' . $table, $field);

	$info .= '
	<li>Block types and parameters updated.</li>';
}

// Let's setup some standard settings.
$defaults = array(
	'sp_portal_mode' => 1,
	'sp_disableForumRedirect' => 1,
	'showleft' => 1,
	'showright' => 1,
	'leftwidth' => 200,
	'rightwidth' => 200,
	'sp_enableIntegration' => 1,
	'sp_adminIntegrationHide' => 1,
	'sp_resize_images' => 1,
);

$updates = array(
	'sp_version' => '2.3.5',
	'sp_smf_version' => '2',
);

foreach ($defaults as $index => $value)
	if (!isset($modSettings[$index]))
		$updates[$index] = $value;

updateSettings($updates);

// Take control of the $db_package_log array(), Mahahahaha!!!
$db_package_log = array();
foreach ($tables as $table_name => $null)
	$db_package_log[] = array('remove_table', $db_prefix . $table_name);

$info .= '
	<li>Default settings inserted.</li>';

$info .= '</ul>';

$standalone_file = $boarddir . '/PortalStandalone.php';
if (isset($package_cache[$standalone_file]))
	$package_cache[$standalone_file] = str_replace('full/path/to/forum', $boarddir, $package_cache[$standalone_file]);
elseif (file_exists($standalone_file))
{
	$current_data = file_get_contents($standalone_file);
	if (strpos($current_data, 'full/path/to/forum') !== false)
	{
		$fp = fopen($standalone_file, 'w+');
		fwrite($fp, str_replace('full/path/to/forum', $boarddir, $current_data));
		fclose($fp);
	}
}

// Show them a nice message.
if (SMF == 'SSI')
{
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"', $context['right_to_left'] ? ' dir="rtl"' : '', '>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=', $context['character_set'], '" />
	<title>SimplePortal &bull; Database Tool</title>
	<link rel="stylesheet" type="text/css" href="', $settings['theme_url'], '/css/index.css?235" />
	<meta name="robots" content="noindex" />
	<style type="text/css">
		body, html
		{
			height: 100%;
			overflow: auto;
			padding: 0;
		}
		body
		{
			font-size: 12px;
			text-align: center;
		}
		ul
		{
			padding-left: 3em;
			line-height: 1.5em;
		}
		ul li, li
		{
			list-style: disc;
		}
		h1
		{
			padding-left: 5px;
		}
		#page
		{
			width: 600px;
			border: 1px solid #000000;
			padding: 1px;
			margin: 0 auto;
			text-align: left;
			line-height: 28px;
			clear: left;
		}
		#info
		{
			padding-left: 20px;
			border: 1px solid #DDDDDD;
			margin-left: 10px;
			margin-right: 10px;
		}
		#distance
		{
			float: left;
			height: 50%;
			margin-top: -300px;
			width: 1px;
		}
		#copy
		{
			font-size: x-small;
			text-align: center;
		}
	</style>
</head>
<body>
<div id="distance"></div>
<div id="page" class="windowbg2">
	<h1 class="catbg">SimplePortal &bull; Database Tool</h1>
	<p id="info" class="windowbg">This tool will prepare your database to work with SimplePortal. It will also fix database issues related to SimplePortal, if there are any.</p>
	', $info, '
	<p id="copy">SimplePortal &copy; 2008-2012</p>
</div>
</body>
</html>';
}

?>