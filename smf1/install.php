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

// Handle running this file by using SSI.php
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
{
	$_GET['debug'] = 'Blue Dream!';
	require_once(dirname(__FILE__) . '/SSI.php');
}
// Hmm... no SSI.php and no SMF?
elseif (!defined('SMF'))
	die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

global $db_prefix, $db_name, $modSettings, $sourcedir, $boarddir, $settings, $package_cache;

// Load all tables.
$tables = array(
	'sp_articles' => array(
		'columns' => array(
			array(
				'name' => 'ID_ARTICLE',
				'type' => 'int',
				'size' => '10',
				'auto' => true,
			),
			array(
				'name' => 'ID_CATEGORY',
				'type' => 'int',
				'size' => '10',
				'default' => 0,
			),
			array(
				'name' => 'ID_MESSAGE',
				'type' => 'int',
				'size' => '10',
				'default' => 0,
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
				'columns' => array('ID_ARTICLE'),
			),
		),
	),
	'sp_blocks' => array(
		'columns' => array(
			array(
				'name' => 'ID_BLOCK',
				'type' => 'int',
				'size' => '10',
				'auto' => true,
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
				'columns' => array('ID_BLOCK'),
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
				'name' => 'ID_CATEGORY',
				'type' => 'int',
				'size' => '10',
				'auto' => true,
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
				'columns' => array('ID_CATEGORY'),
			),
		),
	),
	'sp_functions' => array(
		'columns' => array(
			array(
				'name' => 'ID_FUNCTION',
				'type' => 'tinyint',
				'size' => '4',
				'auto' => true,
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
				'columns' => array('ID_FUNCTION'),
			),
		),
	),
	'sp_pages' => array(
		'columns' => array(
			array(
				'name' => 'ID_PAGE',
				'type' => 'int',
				'size' => 10,
				'auto' => true,
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
				'type' => 'mediumtext',
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
				'name' => 'ID_BLOCK',
				'type' => 'int',
				'size' => '10',
				'default' => 0,
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
				'columns' => array('ID_BLOCK', 'variable'),
			),
			array(
				'type' => 'key',
				'columns' => array('variable'),
			),
		),
	),
	'sp_shoutboxes' => array(
		'columns' => array(
			array(
				'name' => 'ID_SHOUTBOX',
				'type' => 'int',
				'size' => 10,
				'auto' => true,
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
				'columns' => array('ID_SHOUTBOX'),
			),
		),
	),
	'sp_shouts' => array(
		'columns' => array(
			array(
				'name' => 'ID_SHOUT',
				'type' => 'mediumint',
				'size' => '8',
				'auto' => true,
			),
			array(
				'name' => 'ID_SHOUTBOX',
				'type' => 'int',
				'size' => 10,
				'default' => 0,
			),
			array(
				'name' => 'ID_MEMBER',
				'type' => 'mediumint',
				'size' => '8',
				'default' => 0,
			),
			array(
				'name' => 'memberName',
				'type' => 'varchar',
				'size' => '80',
				'default' => 0,
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
				'columns' => array('ID_SHOUT'),
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
db_query("
	DROP TABLE IF EXISTS {$db_prefix}sp_functions", __FILE__, __LINE__);

$current_tables = sp_db_list_tables(false, '%sp%');
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
			sp_db_add_column($table, $column);

		$info .= '
			<li>Table columns updated.</li>';

		foreach ($data['indexes'] as $index)
			sp_db_add_index($table, $index);

		$info .= '
			<li>Table indexes updated.</li>
		</ul>
	</li>';
	}
	else
	{
		sp_db_create_table($table, $data['columns'], $data['indexes'], array());

		$info .= '<li>"' . $table . '" table created.</li>';
	}
}

sp_db_insert(
	'sp_functions',
	array(
		'ID_FUNCTION',
		'function_order',
		'name',
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
		array(19, 98, 'sp_bbc'),
		array(17, 99, 'sp_html'),
		array(18, 100, 'sp_php'),
	)
);

$info .= '
	<li>"sp_functions" table data inserted.</li>';

$request = db_query("
	SELECT ID_BLOCK
	FROM {$db_prefix}sp_blocks
	LIMIT 1", __FILE__, __LINE__);
list ($has_block) = mysql_fetch_row($request);
mysql_free_result($request);

if (empty($has_block))
{
	foreach ($deprecated_fields as $table => $fields)
		foreach ($fields as $field)
			sp_db_remove_column($table, $field);

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

	sp_db_insert(
		'sp_blocks',
		array(
			'label',
			'type',
			'col',
			'row',
			'permission_set',
			'display',
			'display_custom',
			'style',
		),
		$default_blocks
	);

	$request = db_query("
		SELECT MIN(ID_BLOCK) AS id, type
		FROM {$db_prefix}sp_blocks
		WHERE type IN ('sp_html', 'sp_boardNews', 'sp_calendar', 'sp_recent')
		GROUP BY type
		LIMIT 4",__FILE__, __LINE__);
	while ($row = mysql_fetch_assoc($request))
		$block_ids[$row['type']] = $row['id'];
	mysql_free_result($request);

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

	sp_db_insert(
		'sp_parameters',
		array(
			'ID_BLOCK',
			'variable',
			'value',
		),
		$default_parameters,
		true
	);

	$info .= '
	<li>Default blocks created.</li>';
}
else
{
	$permission_updates = array('blocks' => 'block', 'pages' => 'page', 'shoutboxes' => 'shoutbox');
	foreach ($permission_updates as $table => $field)
	{
		$columns = sp_db_list_columns('sp_' . $table, false);
		if (in_array('allowed_groups', $columns))
		{
			$request = db_query("
				SELECT id_{$field}," . (in_array('permission_type', $columns) ? " permission_type," : "") . " allowed_groups
				FROM {$db_prefix}sp_{$table}",__FILE__, __LINE__);
			$permissions = array();
			while ($row = mysql_fetch_assoc($request))
			{
				if (!isset($row['permission_type']))
					$row['permission_type'] = 2;
				$permissions[] = $row;
			}
			mysql_free_result($request);

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
				$request = db_query("
					UPDATE {$db_prefix}sp_{$table}
					SET
						permission_set = $item[permission_set],
						groups_allowed = '$item[groups_allowed]',
						groups_denied = '$item[groups_denied]'
					WHERE id_{$field} = $item[id]",__FILE__, __LINE__);
			}
		}
	}

	if (empty($modSettings['sp_version']) || $modSettings['sp_version'] < '2.3.6')
	{
		sp_db_change_column("sp_pages", 'body', array('type' => 'mediumtext'));

		db_query("
			UPDATE {$db_prefix}sp_blocks
			SET style = ''
			WHERE type = 'sp_boardNews'", __FILE__, __LINE__);
	}

	if (empty($modSettings['sp_version']) || $modSettings['sp_version'] < '2.3')
	{
		$request = db_query("
			SELECT ID_BLOCK, type
			FROM {$db_prefix}sp_blocks
			WHERE type IN ('sp_recentTopics', 'sp_recentPosts')",__FILE__, __LINE__);
		$replace_blocks = array();
		$add_parameters = array();
		while ($row = mysql_fetch_assoc($request))
		{
			$replace_blocks[] = $row['ID_BLOCK'];
			$add_parameters[] = array(
				'id_block' => $row['ID_BLOCK'],
				'variable' => 'display',
				'value' => 1,
			);
			$add_parameters[] = array(
				'id_block' => $row['ID_BLOCK'],
				'variable' => 'type',
				'value' => $row['type'] == 'sp_recentPosts' ? 0 : 1,
			);
		}
		mysql_free_result($request);

		if (!empty($replace_blocks) && !empty($add_parameters))
		{
			db_query("
				UPDATE {$db_prefix}sp_blocks
				SET type = 'sp_recent'
				WHERE ID_BLOCK IN (" . implode(', ', $replace_blocks) . ")", __FILE__, __LINE__);

			sp_db_insert(
				'sp_parameters',
				array(
					'ID_BLOCK',
					'variable',
					'value',
				),
				$add_parameters,
				true
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
				'old' => 'sp_smfArcade',
				'new' => 'sp_arcade'
			),
			array(
				'old' => 'sp_smfShop',
				'new' => 'sp_shop'
			),
			array(
				'old' => 'sp_mgallery',
				'new' => 'sp_gallery'
			),
		);

		foreach ($block_updates as $type)
			db_query("
				UPDATE {$db_prefix}sp_blocks
				SET type = '$type[new]'
				WHERE type = '$type[old]'", __FILE__, __LINE__);

		$current_columns = sp_db_list_columns('sp_blocks', false);
		if (in_array('content', $current_columns))
		{
			require_once($sourcedir . '/PortalBlocks.php');
			$old_parameters = array();

			$request = db_query("
				SELECT ID_BLOCK, type, content, parameters
				FROM {$db_prefix}sp_blocks",__FILE__, __LINE__);
			while ($row = mysql_fetch_assoc($request))
			{
				if (in_array($row['type'], array('sp_bbc', 'sp_html', 'sp_php')))
				{
					$old_parameters[] = array(
						'id_block' => $row['ID_BLOCK'],
						'variable' => 'content',
						'value' => addslashes($row['content']),
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
							'id_block' => $row['ID_BLOCK'],
							'variable' => $variable,
							'value' => $old,
						);
					}
				}
				else
					continue;
			}
			mysql_free_result($request);

			if (!empty($old_parameters))
			{
				sp_db_insert(
					'sp_parameters',
					array(
						'ID_BLOCK',
						'variable',
						'value',
					),
					$old_parameters,
					true
				);
			}
		}
	}

	foreach ($deprecated_fields as $table => $fields)
		foreach ($fields as $field)
			sp_db_remove_column($table, $field);

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
	'sp_version' => '2.3.7',
	'sp_smf_version' => '1',
);

foreach ($defaults as $index => $value)
	if (!isset($modSettings[$index]))
		$updates[$index] = $value;

updateSettings($updates);

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
	<link rel="stylesheet" type="text/css" href="', $settings['theme_url'], '/style.css?237" />
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
			font-size: 14px;
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
	<div class="catbg">SimplePortal &bull; Database Tool</div>
	<p id="info" class="windowbg">This tool will prepare your database to work with SimplePortal. It will also fix database issues related to SimplePortal, if there are any.</p>
	', $info, '
	<p id="copy">SimplePortal &copy; 2008-', strftime('%Y'), '</p>
</div>
</body>
</html>';
}

function sp_db_create_table($table_name, $columns, $indexes = array(), $no_prefix = true)
{
	global $db_prefix, $db_character_set;

	$real_prefix = preg_match('~^(`?)(.+?)\\1\\.(.*?)$~', $db_prefix, $match) === 1 ? $match[3] : $db_prefix;

	$complete_table_name = !$no_prefix ? $db_prefix . $table_name : $table_name;
	$full_table_name = !$no_prefix ? $real_prefix . $table_name : $table_name;

	$tables = sp_db_list_tables();
	if (in_array($full_table_name, $tables))
		return true;

	$table_query = 'CREATE TABLE ' . $complete_table_name . "\n" .'(';
	foreach ($columns as $column)
	{
		if (!empty($column['auto']))
			$default = 'auto_increment';
		elseif (isset($column['default']) && $column['default'] !== null)
			$default = 'default \'' . $column['default'] . '\'';
		else
			$default = '';

		$column['size'] = isset($column['size']) && is_numeric($column['size']) ? $column['size'] : null;
		list ($type, $size) = array($column['type'], $column['size']);
		if ($size !== null)
			$type = $type . '(' . $size . ')';

		$table_query .= "\n\t`" .$column['name'] . '` ' . $type . ' ' . (!empty($column['null']) ? '' : 'NOT NULL') . ' ' . $default . ',';
	}

	foreach ($indexes as $index)
	{
		$columns = implode(',', $index['columns']);

		if (isset($index['type']) && $index['type'] == 'primary')
			$table_query .= "\n\t" . 'PRIMARY KEY (' . implode(',', $index['columns']) . '),';
		else
		{
			if (empty($index['name']))
				$index['name'] = implode('_', $index['columns']);
			$table_query .= "\n\t" . (isset($index['type']) && $index['type'] == 'unique' ? 'UNIQUE' : 'KEY') . ' ' . $index['name'] . ' (' . $columns . '),';
		}
	}

	if (substr($table_query, -1) == ',')
		$table_query = substr($table_query, 0, -1);

	$table_query .= ') ENGINE=MyISAM';
	if (!empty($db_character_set) && $db_character_set == 'utf8')
		$table_query .= ' DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci';

	db_query($table_query, __FILE__, __LINE__);
}

function sp_db_add_column($table_name, $column_info, $no_prefix = true)
{
	global $txt, $db_prefix;

	if ($no_prefix)
		$table_name = $db_prefix . $table_name;

	$columns = sp_db_list_columns($table_name, false, false);
	foreach ($columns as $column)
		if ($column == $column_info['name'])
				return sp_db_change_column($table_name, $column_info['name'], $column_info, false);

	$column_info['size'] = isset($column_info['size']) && is_numeric($column_info['size']) ? $column_info['size'] : null;
	list ($type, $size) = array($column_info['type'], $column_info['size']);
	if ($size !== null)
		$type = $type . '(' . $size . ')';

	$query = '
		ALTER TABLE ' . $table_name . '
		ADD ' . $column_info['name'] . ' ' . $type . ' ' . (empty($column_info['null']) ? 'NOT NULL' : '') . ' ' .
			(!isset($column_info['default']) ? '' : 'default \'' . $column_info['default'] . '\'') . ' ' .
			(empty($column_info['auto']) ? '' : 'auto_increment') . ' ';
	db_query($query, __FILE__, __LINE__);

	return true;
}

function sp_db_change_column($table_name, $old_column, $column_info, $no_prefix = true)
{
	global $db_prefix;

	if ($no_prefix)
		$table_name = $db_prefix . $table_name;

	$columns = sp_db_list_columns($table_name, true, false);
	$old_info = null;
	foreach ($columns as $column)
		if ($column['name'] == $old_column)
			$old_info = $column;

	if ($old_info == null)
		return false;

	if (!isset($column_info['name']))
		$column_info['name'] = $old_column;
	if (!isset($column_info['default']))
		$column_info['default'] = $old_info['default'];
	if (!isset($column_info['null']))
		$column_info['null'] = $old_info['null'];
	if (!isset($column_info['auto']))
		$column_info['auto'] = $old_info['auto'];
	if (!isset($column_info['type']))
		$column_info['type'] = $old_info['type'];
	if (!isset($column_info['size']) || !is_numeric($column_info['size']))
		$column_info['size'] = $old_info['size'];

	list ($type, $size) = array($column_info['type'], $column_info['size']);
	if ($size !== null)
		$type = $type . '(' . $size . ')';

	$query = '
		ALTER TABLE ' . $table_name . '
		CHANGE COLUMN ' . $old_column . ' ' . $column_info['name'] . ' ' . $type . ' ' . (empty($column_info['null']) ? 'NOT NULL' : '') . ' ' .
			(!isset($column_info['default']) || $column_info['default'] === '' ? '' : 'default \'' . $column_info['default'] . '\'') . ' ' .
			(empty($column_info['auto']) ? '' : 'auto_increment') . ' ';
	db_query($query, __FILE__, __LINE__);

	return true;
}

function sp_db_add_index($table_name, $index_info, $no_prefix = true)
{
	global $db_prefix;

	if ($no_prefix)
		$table_name = $db_prefix . $table_name;

	if (empty($index_info['columns']))
		return false;
	$columns = implode(',', $index_info['columns']);

	if (empty($index_info['name']))
	{
		if ($index_info['type'] == 'primary')
			$index_info['name'] = 'PRIMARY';
		else
			$index_info['name'] = implode('_', $index_info['columns']);
	}
	else
		$index_info['name'] = $index_info['name'];

	$indexes = sp_db_list_indexes($table_name, true, false);
	foreach ($indexes as $index)
		if ($index['name'] == $index_info['name'] || ($index['is_primary'] && isset($index_info['type']) && $index_info['type'] == 'primary'))
			return false;

	if (!empty($index_info['type']) && $index_info['type'] == 'primary')
	{
		$query = '
			ALTER TABLE ' . $table_name . '
			ADD PRIMARY KEY (' . $columns . ')';
	}
	else
	{
		$query = '
			ALTER TABLE ' . $table_name . '
			ADD ' . (isset($index_info['type']) && $index_info['type'] == 'unique' ? 'UNIQUE' : 'INDEX') . ' ' . $index_info['name'] . ' (' . $columns . ')';
	}
	db_query($query, __FILE__, __LINE__);

	return true;
}

function sp_db_remove_column($table_name, $column_name, $no_prefix = true)
{
	global $db_prefix;

	if ($no_prefix)
		$table_name = $db_prefix . $table_name;
	$columns = sp_db_list_columns($table_name, true, false);

	foreach ($columns as $column)
		if ($column['name'] == $column_name)
		{
			$query = '
				ALTER TABLE ' . $table_name . '
				DROP COLUMN ' . $column_name;
			db_query($query, __FILE__, __LINE__);

			return true;
		}

	return false;
}

function sp_db_remove_index($table_name, $index_name, $no_prefix = true)
{
	global $db_prefix;

	if ($no_prefix)
		$table_name = $db_prefix . $table_name;
	$indexes = sp_db_list_indexes($table_name, true, false);

	foreach ($indexes as $index)
	{
		if (strtolower($index['type']) == 'primary' && $index_name == 'primary')
		{
			$query = '
				ALTER TABLE ' . $table_name . '
				DROP PRIMARY KEY';
			db_query($query, __FILE__, __LINE__);


			return true;
		}
		if ($index['name'] == $index_name)
		{
			$query = '
				ALTER TABLE ' . $table_name . '
				DROP INDEX ' . $index_name;
			db_query($query, __FILE__, __LINE__);

			return true;
		}
	}

	return false;
}

function sp_db_list_tables($db = false, $filter = false)
{
	global $db_name;

	$db = $db == false ? $db_name : $db;
	$db = trim($db);
	$db = $db{0} == '`' ? strtr($db, array('`' => '')) : $db;
	$filter = $filter == false ? '' : ' LIKE \'' . $filter . '\'';

	$request = db_query("
		SHOW TABLES
		FROM `{$db_name}`
		{$filter}", __FILE__, __LINE__);
	$tables = array();
	while ($row = mysql_fetch_assoc($request))
		$tables[] = reset($row);
	mysql_free_result($request);

	return $tables;
}

function sp_db_list_columns($table_name, $detail = false, $no_prefix = true)
{
	global $db_prefix;

	if ($no_prefix)
		$table_name = $db_prefix . $table_name;
	$table_name = substr($table_name, 0, 1) == '`' ? $table_name : '`' . $table_name . '`';

	$request = db_query("
		SHOW FIELDS
		FROM {$table_name}", __FILE__, __LINE__);
	$columns = array();
	while ($row = mysql_fetch_assoc($request))
	{
		if (!$detail)
		{
			$columns[] = $row['Field'];
		}
		else
		{
			$auto = strpos($row['Extra'], 'auto_increment') !== false ? true : false;

			if (preg_match('~(.+?)\s*\((\d+)\)~i', $row['Type'], $matches) === 1)
			{
				$type = $matches[1];
				$size = $matches[2];
			}
			else
			{
				$type = $row['Type'];
				$size = null;
			}

			$columns[$row['Field']] = array(
				'name' => $row['Field'],
				'null' => $row['Null'] != 'YES' ? false : true,
				'default' => isset($row['Default']) ? $row['Default'] : null,
				'type' => $type,
				'size' => $size,
				'auto' => $auto,
			);
		}
	}
	mysql_free_result($request);

	return $columns;
}

function sp_db_list_indexes($table_name, $detail = false, $no_prefix = true)
{
	global $db_prefix;

	if ($no_prefix)
		$table_name = $db_prefix . $table_name;
	$table_name = substr($table_name, 0, 1) == '`' ? $table_name : '`' . $table_name . '`';

	$request = db_query("
		SHOW KEYS
		FROM {$table_name}", __FILE__, __LINE__);
	$indexes = array();
	while ($row = mysql_fetch_assoc($request))
	{
		if (!$detail)
			$indexes[] = $row['Key_name'];
		else
		{
			if ($row['Key_name'] == 'PRIMARY')
				$type = 'primary';
			elseif (empty($row['Non_unique']))
				$type = 'unique';
			elseif (isset($row['Index_type']) && $row['Index_type'] == 'FULLTEXT')
				$type = 'fulltext';
			else
				$type = 'index';

			if (empty($indexes[$row['Key_name']]))
			{
				$indexes[$row['Key_name']] = array(
					'name' => $row['Key_name'],
					'type' => $type,
					'columns' => array(),
					'is_primary' => $type == 'primary',
				);
			}

			if (!empty($row['Sub_part']))
				$indexes[$row['Key_name']]['columns'][] = $row['Column_name'] . '(' . $row['Sub_part'] . ')';
			else
				$indexes[$row['Key_name']]['columns'][] = $row['Column_name'];
		}
	}
	mysql_free_result($request);

	return $indexes;
}

function sp_db_insert($table_name, $fields, $values, $replace = false)
{
	global $db_prefix;

	$query = "
		" . ($replace ? "REPLACE" : "INSERT") . " INTO {$db_prefix}{$table_name}
			(" . implode(', ', $fields) . ")
		VALUES ";

	foreach ($values as $value)
	{
		$query .= "(";

		$row = array();
		foreach ($value as $field)
			$row[] = "'" . $field . "'";

		$query .= implode(', ', $row) . "),";
	}

	if (substr($query, -1) == ',')
		$query = substr($query, 0, -1);

	db_query($query, __FILE__, __LINE__);
}

?>