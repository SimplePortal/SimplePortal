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
	global $smcFunc, $context, $modSettings, $article_request;

	loadLanguage('Stats');
	loadTemplate('PortalArticles');
	$context['sub_template'] = 'articles';

	if (empty($modSettings['articleactive']))
		return;

	$request = $smcFunc['db_query']('','
		SELECT COUNT(*)
		FROM {db_prefix}sp_articles as a
			INNER JOIN {db_prefix}sp_categories AS c ON (c.id_category = a.id_category)
			INNER JOIN {db_prefix}messages AS m ON (m.id_msg = a.id_message)
			INNER JOIN {db_prefix}boards AS b ON (b.id_board = m.id_board)
		WHERE {query_see_board}
			AND a.approved = {int:approved}
			AND publish = {int:publish}',
		array(
			'approved' => 1,
			'publish' => 1,
		)
	);
	list ($totalArticles) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	$modSettings['articleperpage'] = isset($modSettings['articleperpage']) ? (int) $modSettings['articleperpage'] : 5;
	$context['start'] = !empty($_REQUEST['articles']) ? (int) $_REQUEST['articles'] : 0;

	if (!empty($modSettings['articleperpage']) && $totalArticles > 0)
		$context['page_index'] = constructPageIndex($context['portal_url'] . '?articles=%1$d', $context['start'], $totalArticles, $modSettings['articleperpage'], true);

	if (empty($modSettings['sp_disableColor']))
	{
		$members_request = $smcFunc['db_query']('','
			SELECT m.id_member
			FROM {db_prefix}sp_articles AS a
				INNER JOIN {db_prefix}messages AS m ON (m.id_msg = a.id_message)
				INNER JOIN {db_prefix}boards AS b ON (b.id_board = m.id_board)
				INNER JOIN {db_prefix}sp_categories AS c ON (c.id_category = a.id_category)
			WHERE {query_see_board}
				AND a.approved = {int:approved}
				AND publish = {int:publish}
				AND m.id_member != {int:guest}
			ORDER BY a.id_message DESC' . (empty($modSettings['articleperpage']) ? '' : '
			LIMIT {int:start}, {int:end}'),
			array(
				'approved' => 1,
				'publish' => 1,
				'start' => $context['start'],
				'end' =>  $modSettings['articleperpage'],
				'guest' => 0,
			)
		);
		$colorids = array();
		while($row = $smcFunc['db_fetch_assoc']($members_request))
			$colorids[] = $row['id_member'];
		$smcFunc['db_free_result']($members_request);

		if (!empty($colorids))
			sp_loadColors($colorids);
	}

	$article_request = $smcFunc['db_query']('','
		SELECT
			a.id_article, a.id_category, a.id_message, a.approved, c.name as cname, c.picture, m.id_member,
			IFNULL(mem.real_name, m.poster_name) AS poster_name, m.icon, m.subject, m.body, m.poster_time,
			m.smileys_enabled, t.id_topic, t.num_replies, t.num_views, t.locked, b.id_board, b.name as bname,
			mem.avatar, at.id_attach, at.attachment_type, at.filename
		FROM {db_prefix}sp_articles AS a
			INNER JOIN {db_prefix}sp_categories AS c ON (c.id_category = a.id_category)
			INNER JOIN {db_prefix}messages AS m ON (m.id_msg = a.id_message)
			INNER JOIN {db_prefix}topics AS t ON (t.id_first_msg = a.id_message)
			INNER JOIN {db_prefix}boards AS b ON (b.id_board = m.id_board)
			LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = m.id_member)
			LEFT JOIN {db_prefix}attachments AS at ON (at.id_member = mem.id_member)
		WHERE {query_see_board}
			AND a.approved = {int:approved}
			AND publish = {int:publish}
		ORDER BY a.id_message DESC' . (empty($modSettings['articleperpage']) ? '' : '
		LIMIT {int:start}, {int:end}'),
		array(
			'approved' => 1,
			'publish' => 1,
			'start' => $context['start'],
			'end' =>  $modSettings['articleperpage'],
		)
	);

	$context['get_articles'] = 'sportal_articles_callback';
}

function sportal_articles_callback($reset = false)
{
	global $smcFunc, $scripturl, $modSettings, $settings, $txt, $color_profile, $article_request, $current;

	if ($article_request == false)
		return false;

	if (!($row = $smcFunc['db_fetch_assoc']($article_request)))
		return false;

	if (!empty($current) && $current == $row['id_message'])
		return;

	$current = $row['id_message'];

	$stable_icons = array('xx', 'thumbup', 'thumbdown', 'exclamation', 'question', 'lamp', 'smiley', 'angry', 'cheesy', 'grin', 'sad', 'wink', 'moved', 'recycled', 'wireless');
	$icon_sources = array();
	foreach ($stable_icons as $icon)
		$icon_sources[$icon] = 'images_url';

	$limited = false;
	if (($cutoff = $smcFunc['strpos']($row['body'], '[cutoff]')) !== false)
	{
		$row['body'] = $smcFunc['substr']($row['body'], 0, $cutoff);
		$limited = true;
	}
	elseif (!empty($modSettings['articlelength']) && $smcFunc['strlen']($row['body']) > $modSettings['articlelength'])
	{
		$row['body'] = $smcFunc['substr']($row['body'], 0, $modSettings['articlelength']);
		$limited = true;
	}

	$row['body'] = parse_bbc($row['body'], $row['smileys_enabled'], $row['id_message']);

	// Only place an ellipsis if the body has been shortened.
	if ($limited)
		$row['body'] .= '<a href="' . $scripturl . '?topic=' . $row['id_topic'] . '.0" title="' . $row['subject'] . '">...</a>';

	if ($modSettings['avatar_action_too_large'] == 'option_html_resize' || $modSettings['avatar_action_too_large'] == 'option_js_resize')
	{
		$avatar_width = !empty($modSettings['avatar_max_width_external']) ? ' width="' . $modSettings['avatar_max_width_external'] . '"' : '';
		$avatar_height = !empty($modSettings['avatar_max_height_external']) ? ' height="' . $modSettings['avatar_max_height_external'] . '"' : '';
	}
	else
	{
		$avatar_width = '';
		$avatar_height = '';
	}

	if (empty($modSettings['messageIconChecks_disable']) && !isset($icon_sources[$row['icon']]))
		$icon_sources[$row['icon']] = file_exists($settings['theme_dir'] . '/images/post/' . $row['icon'] . '.gif') ? 'images_url' : 'default_images_url';

	censorText($row['subject']);
	censorText($row['body']);

	if ($modSettings['sp_resize_images'])
		$row['body'] = preg_replace('~class="bbc_img~i', 'class="bbc_img sp_article', $row['body']);

	$output = array(
		'article' => array(
			'id' => $row['id_article'],
			'comment_href' => !empty($row['locked']) ? '' : $scripturl . '?action=post;topic=' . $row['id_topic'] . '.' . $row['num_replies'] . ';num_replies=' . $row['num_replies'],
			'comment_link' => !empty($row['locked']) ? '' : '| <a href="' . $scripturl . '?action=post;topic=' . $row['id_topic'] . '.' . $row['num_replies'] . ';num_replies=' . $row['num_replies'] . '">' . $txt['ssi_write_comment'] . '</a>',
			'new_comment' => !empty($row['locked']) ? '' : '| <a href="' . $scripturl . '?action=post;topic=' . $row['id_topic'] . '.' . $row['num_replies'] . '">' . $txt['ssi_write_comment'] . '</a>',
			'link' => '<a href="' . $scripturl . '?topic=' . $row['id_topic'] . '.0">' . $txt['sp-read_more'] . '</a>',
			'approved' => $row['approved'],
		),
		'category' => array(
			'id' => $row['id_category'],
			'name' => $row['cname'],
			'picture' => array (
				'href' => $row['picture'],
				'image' => '<img src="' . $row['picture'] . '" alt="' . $row['cname'] . '" width="75" />',
			),
		),
		'message' => array(
			'id' => $row['id_message'],
			'icon' => '<img src="' . $settings[$icon_sources[$row['icon']]] . '/post/' . $row['icon'] . '.gif" align="middle" alt="' . $row['icon'] . '" border="0" />',
			'subject' => $row['subject'],
			'body' => $row['body'],
			'time' => timeformat($row['poster_time']),
		),
		'poster' => array(
			'id' => $row['id_member'],
			'name' => $row['poster_name'],
			'href' => !empty($row['id_member']) ? $scripturl . '?action=profile;u=' . $row['id_member'] : '',
			'link' => !empty($row['id_member']) ? (!empty($color_profile[$row['id_member']]['link']) ? $color_profile[$row['id_member']]['link'] : '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['poster_name'] . '</a>') : $row['poster_name'],
			'avatar' => array(
				'name' => $row['avatar'],
				'image' => $row['avatar'] == '' ? ($row['id_attach'] > 0 ? '<img src="' . (empty($row['attachment_type']) ? $scripturl . '?action=dlattach;attach=' . $row['id_attach'] . ';type=avatar' : $modSettings['custom_avatar_url'] . '/' . $row['filename']) . '" alt="" class="avatar" border="0" />' : '') : (stristr($row['avatar'], 'http://') ? '<img src="' . $row['avatar'] . '"' . $avatar_width . $avatar_height . ' alt="" class="avatar" border="0" />' : '<img src="' . $modSettings['avatar_url'] . '/' . htmlspecialchars($row['avatar']) . '" alt="" class="avatar" border="0" />'),
				'href' => $row['avatar'] == '' ? ($row['id_attach'] > 0 ? (empty($row['attachment_type']) ? $scripturl . '?action=dlattach;attach=' . $row['id_attach'] . ';type=avatar' : $modSettings['custom_avatar_url'] . '/' . $row['filename']) : '') : (stristr($row['avatar'], 'http://') ? $row['avatar'] : $modSettings['avatar_url'] . '/' . $row['avatar']),
				'url' => $row['avatar'] == '' ? '' : (stristr($row['avatar'], 'http://') ? $row['avatar'] : $modSettings['avatar_url'] . '/' . $row['avatar'])
			),
		),
		'topic' => array(
			'id' => $row['id_topic'],
			'replies' => $row['num_replies'],
			'views' => $row['num_views'],
			'href' => $scripturl . '?topic=' . $row['id_topic'] . '.0',
			'link' => '<a href="' . $scripturl . '?topic=' . $row['id_topic'] . '.0">' . $row['subject'] . '</a>',
			'locked' => !empty($row['locked']),
		),
		'board' => array(
			'id' => $row['id_board'],
			'name' => $row['bname'],
			'link' => '<a href="' . $scripturl . '?board=' . $row['id_board'] . '.0">' . $row['bname'] . '</a>',
		),
	);

	return $output;
}

function sportal_add_article()
{
	global $smcFunc, $context, $scripturl, $sourcedir, $txt;

	if (!allowedTo(array('sp_add_article', 'sp_manage_articles', 'sp_admin')))
		fatal_lang_error('error_sp_cannot_add_article');

	require_once($sourcedir . '/Subs-PortalAdmin.php');
	loadLanguage('SPortalAdmin', sp_languageSelect('SPortalAdmin'));
	loadTemplate('PortalArticles');

	if (!empty($_POST['add_article']))
	{
		checkSession();

		$article_options = array(
			'id_category' => !empty($_POST['category']) ? (int) $_POST['category'] : 0,
			'id_message' => !empty($_POST['message']) ? (int) $_POST['message'] : 0,
			'approved' => allowedTo(array('sp_admin', 'sp_manage_articles', 'sp_auto_article_approval')) ? 1 : 0,
		);
		createArticle($article_options);

		redirectexit('topic=' . $_POST['return']);
	}

	$context['message'] = !empty($_REQUEST['message']) ? (int) $_REQUEST['message'] : 0;
	$context['return'] = !empty($_REQUEST['return']) ? $_REQUEST['return'] : '';

	if (empty($context['message']))
		fatal_lang_error('error_sp_no_message_id');

	$request = $smcFunc['db_query']('','
		SELECT id_message
		FROM {db_prefix}sp_articles
		WHERE id_message = {int:message}',
		array(
			'message' => $context['message'],
		)
	);
	list ($exists) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	if ($exists)
		fatal_lang_error('error_sp_article_exists');

	$context['list_categories'] = getCategoryInfo(null, true);

	if (empty($context['list_categories']))
		fatal_error(allowedTo(array('sp_admin', 'sp_manage_articles')) ? $txt['error_sp_no_category'] . '<br />' . sprintf($txt['error_sp_no_category_sp_moderator'], $scripturl . '?action=admin;area=portalarticles;sa=addcategory') : $txt['error_sp_no_category_normaluser'], false);

	$context['sub_template'] = 'add_article';
}

function sportal_remove_article()
{
	global $smcFunc, $sourcedir;

	checkSession('get');

	if (!allowedTo(array('sp_remove_article', 'sp_manage_articles', 'sp_admin')))
		fatal_lang_error('error_sp_cannot_remove_article');

	require_once($sourcedir . '/Subs-PortalAdmin.php');

	$message_id = !empty($_REQUEST['message']) ? (int) $_REQUEST['message'] : 0;
	$topic_id = !empty($_REQUEST['return']) ? $_REQUEST['return'] : '';

	$smcFunc['db_query']('','
		DELETE FROM {db_prefix}sp_articles
		WHERE id_message = {int:message}',
		array(
			'message' => $message_id,
		)
	);

	fixCategoryArticles();

	redirectexit('topic=' . $topic_id);
}

?>