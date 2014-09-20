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
	void sportal_articles()
		// !!!

	array sportal_articles_callback()
		// !!!

	void sportal_add_article()
		// !!!

	void sportal_remove_article()
		// !!!
*/

function sportal_articles()
{
	global $db_prefix, $context, $modSettings, $user_info, $article_request;

	loadLanguage('Stats');
	loadTemplate('PortalArticles');
	$context['sub_template'] = 'articles';

	if (empty($modSettings['articleactive']))
		return;

	$request = db_query("
		SELECT COUNT(*)
		FROM {$db_prefix}sp_articles as a
			INNER JOIN {$db_prefix}sp_categories AS c ON (c.ID_CATEGORY = a.ID_CATEGORY)
			INNER JOIN {$db_prefix}messages AS m ON (m.ID_MSG = a.ID_MESSAGE)
			INNER JOIN {$db_prefix}boards AS b ON (b.ID_BOARD = m.ID_BOARD)
		WHERE $user_info[query_see_board]
			AND approved = 1
			AND publish = 1", __FILE__, __LINE__);
	list ($totalArticles) = mysql_fetch_row($request);
	mysql_free_result($request);

	$modSettings['articleperpage'] = isset($modSettings['articleperpage']) ? (int) $modSettings['articleperpage'] : 5;
	$context['start'] = !empty($_REQUEST['articles']) ? (int) $_REQUEST['articles'] : 0;

	if (!empty($modSettings['articleperpage']) && $totalArticles > 0)
		$context['page_index'] = constructPageIndex($context['portal_url'] . '?articles=%1$d', $context['start'], $totalArticles, $modSettings['articleperpage'], true);

	if (empty($modSettings['sp_disableColor']))
	{
		$members_request = db_query("
			SELECT m.ID_MEMBER
			FROM {$db_prefix}sp_articles AS a
				INNER JOIN {$db_prefix}messages AS m ON (m.ID_MSG = a.ID_MESSAGE)
				INNER JOIN {$db_prefix}boards AS b ON (b.ID_BOARD = m.ID_BOARD)
				INNER JOIN {$db_prefix}sp_categories AS c ON (c.ID_CATEGORY = a.ID_CATEGORY)
			WHERE $user_info[query_see_board]
				AND approved = 1
				AND publish = 1
				AND m.ID_MEMBER != 0
			ORDER BY a.ID_MESSAGE DESC" . (empty($modSettings['articleperpage']) ? "" : "
			LIMIT $context[start], $modSettings[articleperpage]"), __FILE__, __LINE__);
		$colorids = array();
		while($row = mysql_fetch_assoc($members_request))
			$colorids[] = $row['ID_MEMBER'];
		mysql_free_result($members_request);

		if (!empty($colorids))
			sp_loadColors($colorids);
	}

	$article_request = db_query("
		SELECT
			a.ID_ARTICLE, a.ID_CATEGORY, a.ID_MESSAGE, a.approved, c.name as cname, c.picture, m.ID_MEMBER,
			IFNULL(mem.realName, m.posterName) AS posterName, m.icon, m.subject, m.body, m.posterTime,
			m.smileysEnabled, t.ID_TOPIC, t.numReplies, t.numViews, t.locked, b.ID_BOARD, b.name as bname,
			mem.avatar, at.ID_ATTACH, at.attachmentType, at.filename
		FROM {$db_prefix}sp_articles AS a
			INNER JOIN {$db_prefix}sp_categories AS c ON (c.ID_CATEGORY = a.ID_CATEGORY)
			INNER JOIN {$db_prefix}messages AS m ON (m.ID_MSG = a.ID_MESSAGE)
			INNER JOIN {$db_prefix}topics AS t ON (t.ID_FIRST_MSG = a.ID_MESSAGE)
			INNER JOIN {$db_prefix}boards AS b ON (b.ID_BOARD = m.ID_BOARD)
			LEFT JOIN {$db_prefix}members AS mem ON (mem.ID_MEMBER = m.ID_MEMBER)
			LEFT JOIN {$db_prefix}attachments AS at ON (at.ID_MEMBER = mem.ID_MEMBER)
		WHERE $user_info[query_see_board]
			AND approved = 1
			AND publish = 1
		ORDER BY a.ID_MESSAGE DESC" . (empty($modSettings['articleperpage']) ? "" : "
		LIMIT $context[start], $modSettings[articleperpage]"), __FILE__, __LINE__);

	$context['get_articles'] = 'sportal_articles_callback';
}

function sportal_articles_callback($reset = false)
{
	global $scripturl, $article_request, $txt, $context, $settings, $func, $modSettings, $color_profile, $current;

	if ($article_request == false)
		return false;

	if (!($row = mysql_fetch_assoc($article_request)))
		return false;

	if (!empty($current) && $current == $row['ID_MESSAGE'])
		return;

	$current = $row['ID_MESSAGE'];

	$stable_icons = array('xx', 'thumbup', 'thumbdown', 'exclamation', 'question', 'lamp', 'smiley', 'angry', 'cheesy', 'grin', 'sad', 'wink', 'moved', 'recycled', 'wireless');
	$icon_sources = array();
	foreach ($stable_icons as $icon)
		$icon_sources[$icon] = 'images_url';

	$limited = false;
	if (($cutoff = $func['strpos']($row['body'], '[cutoff]')) !== false)
	{
		$row['body'] = $func['substr']($row['body'], 0, $cutoff);
		$limited = true;
	}
	elseif (!empty($modSettings['articlelength']) && $func['strlen']($row['body']) > $modSettings['articlelength'])
	{
		$row['body'] = $func['substr']($row['body'], 0, $modSettings['articlelength']);
		$limited = true;
	}

	$row['body'] = parse_bbc($row['body'], $row['smileysEnabled'], $row['ID_MESSAGE']);

	// Only place an ellipsis if the body has been shortened.
	if ($limited)
		$row['body'] .= '<a href="' . $scripturl . '?topic=' . $row['ID_TOPIC'] . '.0" title="' . $row['subject'] . '">...</a>';

	if (stristr($row['avatar'], 'http://') && !empty($modSettings['avatar_check_size']))
	{
		$sizes = url_image_size($row['avatar']);

		if ($modSettings['avatar_action_too_large'] == 'option_refuse' && is_array($sizes) && (($sizes[0] > $modSettings['avatar_max_width_external'] && !empty($modSettings['avatar_max_width_external'])) || ($sizes[1] > $modSettings['avatar_max_height_external'] && !empty($modSettings['avatar_max_height_external']))))
		{
			$row['avatar'] = '';
			updateMemberData($row['ID_MEMBER'], array('avatar' => '\'\''));
		}
	}

	if (empty($modSettings['messageIconChecks_disable']) && !isset($icon_sources[$row['icon']]))
		$icon_sources[$row['icon']] = file_exists($settings['theme_dir'] . '/images/post/' . $row['icon'] . '.gif') ? 'images_url' : 'default_images_url';

	censorText($row['subject']);
	censorText($row['body']);

	if ($modSettings['sp_resize_images'])
		$row['body'] = preg_replace('~<img\s+src="([^"]+)"([^/>]+)/>~i', '<img src="$1"$2class="sp_article" />', $row['body']);

	$output = array(
		'article' => array(
			'id' => $row['ID_ARTICLE'],
			'comment_href' => !empty($row['locked']) ? '' : $scripturl . '?action=post;topic=' . $row['ID_TOPIC'] . '.' . $row['numReplies'] . ';num_replies=' . $row['numReplies'],
			'comment_link' => !empty($row['locked']) ? '' : ' | <a href="' . $scripturl . '?action=post;topic=' . $row['ID_TOPIC'] . '.' . $row['numReplies'] . ';num_replies=' . $row['numReplies'] . '">' . $txt['smf_news_3'] . '</a>',
			'new_comment' => !empty($row['locked']) ? '' : ' | <a href="' . $scripturl . '?action=post;topic=' . $row['ID_TOPIC'] . '.' . $row['numReplies'] . '">' . $txt['smf_news_3'] . '</a>',
			'link' => '<a href="' . $scripturl . '?topic=' . $row['ID_TOPIC'] . '.0">' . $txt['sp-read_more'] . '</a>',
			'approved' => $row['approved'],
		),
		'category' => array(
			'id' => $row['ID_CATEGORY'],
			'name' => $row['cname'],
			'picture' => array (
				'href' => $row['picture'],
				'image' => '<img src="' . $row['picture'] . '" alt="' . $row['cname'] . '" width="75" />',
			),
		),
		'message' => array(
			'id' => $row['ID_MESSAGE'],
			'icon' => '<img src="' . $settings[$icon_sources[$row['icon']]] . '/post/' . $row['icon'] . '.gif" align="middle" alt="' . $row['icon'] . '" border="0" />',
			'subject' => $row['subject'],
			'body' => $row['body'],
			'time' => timeformat($row['posterTime']),
		),
		'poster' => array(
			'id' => $row['ID_MEMBER'],
			'name' => $row['posterName'],
			'href' => !empty($row['ID_MEMBER']) ? $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] : '',
			'link' => !empty($row['ID_MEMBER']) ? (!empty($color_profile[$row['ID_MEMBER']]['link']) ? $color_profile[$row['ID_MEMBER']]['link'] : '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '">' . $row['posterName'] . '</a>') : $row['posterName'],
			'avatar' => array(
				'name' => $row['avatar'],
				'image' => $row['avatar'] == '' ? ($row['ID_ATTACH'] > 0 ? '<img src="' . (empty($row['attachmentType']) ? $scripturl . '?action=dlattach;attach=' . $row['ID_ATTACH'] . ';type=avatar' : $modSettings['custom_avatar_url'] . '/' . $row['filename']) . '" alt="" class="avatar" border="0" />' : '') : (stristr($row['avatar'], 'http://') ? '<img src="' . $row['avatar'] . '" alt="" class="avatar" border="0" />' : '<img src="' . $modSettings['avatar_url'] . '/' . htmlspecialchars($row['avatar']) . '" alt="" class="avatar" border="0" />'),
				'href' => $row['avatar'] == '' ? ($row['ID_ATTACH'] > 0 ? (empty($row['attachmentType']) ? $scripturl . '?action=dlattach;attach=' . $row['ID_ATTACH'] . ';type=avatar' : $modSettings['custom_avatar_url'] . '/' . $row['filename']) : '') : (stristr($row['avatar'], 'http://') ? $row['avatar'] : $modSettings['avatar_url'] . '/' . $row['avatar']),
				'url' => $row['avatar'] == '' ? '' : (stristr($row['avatar'], 'http://') ? $row['avatar'] : $modSettings['avatar_url'] . '/' . $row['avatar']),
			),
		),
		'topic' => array(
			'id' => $row['ID_TOPIC'],
			'replies' => $row['numReplies'],
			'views' => $row['numViews'],
			'href' => $scripturl . '?topic=' . $row['ID_TOPIC'] . '.0',
			'link' => '<a href="' . $scripturl . '?topic=' . $row['ID_TOPIC'] . '.0">' . $row['subject'] . '</a>',
			'locked' => !empty($row['locked']),
		),
		'board' => array(
			'id' => $row['ID_BOARD'],
			'name' => $row['bname'],
			'link' => '<a href="' . $scripturl . '?board=' . $row['ID_BOARD'] . '.0">' . $row['bname'] . '</a>',
		),
	);

	return $output;
}

function sportal_add_article()
{
	global $db_prefix, $context, $scripturl, $sourcedir, $txt;

	if (!allowedTo(array('sp_add_article', 'sp_manage_articles', 'sp_admin')))
		fatal_lang_error('error_sp_cannot_add_article');

	require_once($sourcedir . '/Subs-PortalAdmin.php');
	if (loadLanguage('SPortalAdmin') === false)
		loadLanguage('SPortalAdmin', 'english');
	loadTemplate('PortalArticles');

	if (!empty($_POST['add_article']))
	{
		checkSession();

		$article_options = array(
			'ID_CATEGORY' => !empty($_POST['category']) ? (int) $_POST['category'] : 0,
			'ID_MESSAGE' => !empty($_POST['message']) ? (int) $_POST['message'] : 0,
			'approved' => allowedTo(array('sp_auto_article_approval', 'sp_manage_articles', 'sp_admin')) ? 1 : 0,
		);
		createArticle($article_options);

		redirectexit('topic=' . $_POST['return']);
	}

	$context['message'] = !empty($_REQUEST['message']) ? (int) $_REQUEST['message'] : 0;
	$context['return'] = !empty($_REQUEST['return']) ? $_REQUEST['return'] : '';

	if (empty($context['message']))
		fatal_lang_error('error_sp_no_message_id');

	$request = db_query("
		SELECT ID_MESSAGE
		FROM {$db_prefix}sp_articles
		WHERE ID_MESSAGE = '$context[message]'
		LIMIT 1", __FILE__, __LINE__);
	list ($exists) = mysql_fetch_row($request);
	mysql_free_result($request);

	if ($exists)
		fatal_lang_error('error_sp_article_exists', false);

	$context['list_categories'] = getCategoryInfo(null, true);

	if (empty($context['list_categories']))
		fatal_error(allowedTo(array('sp_manage_articles', 'sp_admin')) ? $txt['error_sp_no_category'] . '<br />' . sprintf($txt['error_sp_no_category_sp_moderator'], $scripturl . '?action=manageportal;area=portalarticles;sa=addcategory') : $txt['error_sp_no_category_normaluser'], false);

	$context['sub_template'] = 'add_article';
}

function sportal_remove_article()
{
	global $db_prefix, $sourcedir;

	checkSession('get');

	if (!allowedTo(array('sp_remove_article', 'sp_manage_articles', 'sp_admin')))
		fatal_lang_error('error_sp_cannot_remove_article');

	require_once($sourcedir . '/Subs-PortalAdmin.php');

	$message_id = !empty($_REQUEST['message']) ? (int) $_REQUEST['message'] : 0;
	$topic_id = !empty($_REQUEST['return']) ? $_REQUEST['return'] : '';

	db_query("
		DELETE FROM {$db_prefix}sp_articles
		WHERE ID_MESSAGE = '$message_id'
		LIMIT 1", __FILE__, __LINE__);

	fixCategoryArticles();

	redirectexit('topic=' . $topic_id);
}

?>