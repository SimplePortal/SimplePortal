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
	void sp_userInfo()
		// !!!

	void sp_latestMember()
		// !!!

	void sp_whosOnline()
		// !!!

	void sp_boardStats()
		// !!!

	void sp_topPoster()
		// !!!

	void sp_topStatsMember()
		// !!!

	void sp_recent()
		// !!!

	void sp_topTopics()
		// !!!

	void sp_topBoards()
		// !!!

	void sp_showPoll()
		// !!!

	void sp_boardNews()
		// !!!

	void sp_quickSearch()
		// !!!

	void sp_news()
		// !!!

	void sp_attachmentImage()
		// !!!

	void sp_attachmentRecent()
		// !!!

	void sp_calendar()
		// !!!

	void sp_calendarInformation()
		// !!!

	void sp_rssFeed()
		// !!!

	void sp_theme_select()
		// !!!

	void sp_staff()
		// !!!

	void sp_articles()
		// !!!

	void sp_shoutbox()
		// !!!

	void sp_gallery()
		// !!!

	void sp_arcade()
		// !!!

	void sp_shop()
		// !!!

	void sp_blog()
		// !!!

	void sp_bbc()
		// !!!

	void sp_html()
		// !!!

	void sp_php()
		// !!!
*/

function sp_userInfo($parameters, $id, $return_parameters = false)
{
	global $context, $txt, $scripturl, $memberContext, $modSettings, $user_info, $color_profile;

	$block_parameters = array();

	if ($return_parameters)
		return $block_parameters;

	echo '
								<div class="sp_center sp_fullwidth">';

	if ($context['user']['is_guest'])
	{
		echo '
									<form action="', $scripturl, '?action=login2" method="post" accept-charset="', $context['character_set'], '">
										<table>
											<tr>
												<td class="sp_right"><label for="sp_user">', $txt[35], ':</label>&nbsp;</td>
												<td><input type="text" id="sp_user" name="user" size="9" value="', !empty($user_info['username']) ? $user_info['username'] : '', '" /></td>
											</tr><tr>
												<td class="sp_right"><label for="sp_passwrd">', $txt[36], ':</label>&nbsp;</td>
												<td><input type="password" name="passwrd" id="sp_passwrd" size="9" /></td>
											</tr><tr>
												<td>
													<select name="cookielength">
														<option value="60">', $txt['smf53'], '</option>
														<option value="1440">', $txt['smf47'], '</option>
														<option value="10080">', $txt['smf48'], '</option>
														<option value="43200">', $txt['smf49'], '</option>
														<option value="-1" selected="selected">', $txt['smf50'], '</option>
													</select>
												</td>
												<td><input type="submit" value="', $txt[34], '" /></td>
											</tr>
										</table>
									</form>', $txt['welcome_guest'];
	}
	else
	{
		loadMemberData($context['user']['id']);
		loadMemberContext($context['user']['id']);

		$member_info = $memberContext[$context['user']['id']];

		if (sp_loadColors($member_info['id']) !== false)
			$member_info['colored_name'] = $color_profile[$member_info['id']]['colored_name'];

		$member_info['karma']['total'] = $member_info['karma']['good'] - $member_info['karma']['bad'];

		echo '
									', strtolower($member_info['name']) === 'okarin' ? 'Okae-Rin, ' : $txt['hello_member'], ' <strong>', !empty($member_info['colored_name']) ? $member_info['colored_name'] : $member_info['name'], '</strong>
									<br /><br />';

		if (!empty($member_info['avatar']['image']))
			echo '
									<a href="', $scripturl, '?action=profile;u=', $member_info['id'], '">', $member_info['avatar']['image'], '</a><br /><br />';

		if (!empty($member_info['group']))
			echo '
									', $member_info['group'], '<br />';
		else
			echo '
									', $member_info['post_group'], '<br />';

		echo '
									', $member_info['group_stars'], '<br />';

		echo '
									<br />
									<ul class="sp_list">';

		echo '
										<li>', sp_embed_image('dot'), ' <strong>', $txt[21], ':</strong> ', $member_info['posts'], '</li>';

		if (!empty($modSettings['karmaMode']))
		{
			echo '
										<li>', sp_embed_image('dot'), ' <strong>', $modSettings['karmaLabel'], '</strong> ';

			if ($modSettings['karmaMode'] == 1)
				echo $member_info['karma']['total'];
			elseif ($modSettings['karmaMode'] == 2)
				echo '+', $member_info['karma']['good'], '/-', $member_info['karma']['bad'];

			echo '</li>';
		}

		if (allowedTo('pm_read'))
		{
			echo '
										<li>', sp_embed_image('dot'), ' <strong>', $txt['sp-usertmessage'], ':</strong> <a href="', $scripturl, '?action=pm">', $context['user']['messages'], '</a></li>
										<li>', sp_embed_image('dot'), ' <strong>', $txt['sp-usernmessage'], ':</strong> ', $context['user']['unread_messages'], '</li>';
		}

		echo '
										<li>', sp_embed_image('dot'), ' <a href="', $scripturl, '?action=unread">', $txt['unread_topics_visit'], '</a></li>
										<li>', sp_embed_image('dot'), ' <a href="', $scripturl, '?action=unreadreplies">', $txt['unread_replies'], '</a></li>';

		echo '
									</ul>
									<br />';

		echo '
									', sp_embed_image('arrow'), ' <a href="', $scripturl, '?action=profile">', $txt[79], '</a> ', sp_embed_image('arrow'), ' <a href="', $scripturl, '?action=logout;sesc=', $context['session_id'], '">', $txt[108], '</a>';
	}

	echo '
								</div>';
}

function sp_latestMember($parameters, $id, $return_parameters = false)
{
	global $db_prefix, $context, $scripturl, $txt, $color_profile;

	$block_parameters = array(
		'limit' => 'int',
	);

	if ($return_parameters)
		return $block_parameters;

	$limit = !empty($parameters['limit']) ? (int) $parameters['limit'] : 5;

	$request = db_query("
		SELECT ID_MEMBER, realName, dateRegistered
		FROM {$db_prefix}members
		WHERE is_activated = 1
		ORDER BY ID_MEMBER DESC
		LIMIT $limit", __FILE__, __LINE__);
	$members = array();
	$colorids = array();
	while ($row = mysql_fetch_assoc($request))
	{
		if (!empty($row['ID_MEMBER']))
			$colorids[$row['ID_MEMBER']] = $row['ID_MEMBER'];

		$members[] = array(
			'id' => $row['ID_MEMBER'],
			'name' => $row['realName'],
			'href' => $scripturl . '?action=profile;u=' . $row['ID_MEMBER'],
			'link' => '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '">' . $row['realName'] . '</a>',
			'date' => timeformat($row['dateRegistered'], '%d %b'),
		);
	}
	mysql_free_result($request);

	if (empty($members))
	{
		echo '
								', $txt['error_sp_no_members_found'];
		return;
	}

	if (!empty($colorids) && sp_loadColors($colorids) !== false)
	{
		foreach ($members as $k => $p)
		{
			if (!empty($color_profile[$p['id']]['link']))
				$members[$k]['link'] = $color_profile[$p['id']]['link'];
		}
	}

	echo '
								<ul class="sp_list">';

	foreach ($members as $member)
		echo '
									<li>', sp_embed_image('dot'), ' ', $member['link'], ' - ', $member['date'], '</li>';

	echo '
								</ul>';
}

function sp_whosOnline($parameters, $id, $return_parameters = false)
{
	global $db_prefix, $user_info, $context, $scripturl, $modSettings, $txt;

	$block_parameters = array(
		'online_today' => 'check'
	);

	if ($return_parameters)
		return $block_parameters;

	$online_today = !empty($parameters['online_today']);

	$stats = ssi_whosOnline('array');

	echo '
								<ul class="sp_list">
									<li>', sp_embed_image('dot'), ' ', $txt['guests'], ': ', $stats['guests'], '</li>
									<li>', sp_embed_image('dot'), ' ', $txt['hidden'], ': ', $stats['hidden'], '</li>
									<li>', sp_embed_image('dot'), ' ', $txt['users'], ': ', $stats['num_users'], '</li>';

	if (!empty($stats['users']))
	{
		echo '
									<li>', sp_embed_image('dot'), ' ', allowedTo('who_view') && !empty($modSettings['who_enabled']) ? '<a href="' . $scripturl . '?action=who">' : '', $txt[158], allowedTo('who_view') && !empty($modSettings['who_enabled']) ? '</a>' : '', ':</li>
								</ul>
								<div class="sp_online_flow">
									<ul class="sp_list">';

		foreach ($stats['users'] as $user)
			echo '
										<li class="sp_list_indent">', sp_embed_image($user['name'] == 'H' ? 'tux' : 'user'), ($user['name'] == 'Blue' && ($user['link'] = str_replace('>Blue<', '>Purple<', $user['link'])) ? '' : ''), ' ', $user['hidden'] ? '<em>' . $user['link'] . '</em>' : $user['link'], '</li>';

		echo '
									</ul>
								</div>';
	}
	else
	{
		echo '
								</ul>
								<br />
								<div class="sp_fullwidth sp_center">', $txt['error_sp_no_online'], '</div>';
	}

	if ($online_today && !empty($txt['uot_users_online_today']))
	{
		$date = @getdate(forum_time(false));
		$midnight = mktime(0, 0, 0, $date['mon'], $date['mday'], $date['year']) - ($modSettings['time_offset'] * 3600);

		$s = strpos($user_info['time_format'], '%S') === false ? '' : ':%S';
		if (strpos($user_info['time_format'], '%H') === false && strpos($user_info['time_format'], '%T') === false)
			$time_fmt = '%I:%M' . $s . ' %p';
		else
			$time_fmt = '%H:%M' . $s;

		$result = db_query("
			SELECT
				mem.ID_MEMBER, mem.lastLogin, mem.realName, mem.memberName, mem.showOnline,
				mg.onlineColor, mg.ID_GROUP, mg.groupName
			FROM {$db_prefix}members AS mem
				LEFT JOIN {$db_prefix}membergroups AS mg ON (mg.ID_GROUP = IF(mem.ID_GROUP = 0, mem.ID_POST_GROUP, mem.ID_GROUP)) WHERE mem.lastLogin >= $midnight", __FILE__, __LINE__);

		$stats['num_hidden_users_online_today'] = 0;
		$stats['users_online_today'] = array();
		$stats['list_users_online_today'] = array();

		while ($row = mysql_fetch_assoc($result))
		{
			if (empty($row['showOnline']))
			{
				$stats['num_hidden_users_online_today'] = $stats['num_hidden_users_online_today'] + 1;
				if (!$user_info['is_admin'])
					continue;
			}

			$userday = strftime('%d', forum_time(true));
			$loginday = strftime('%d', forum_time(true, $row['lastLogin']));
			$yesterday = $userday == $loginday ? '' : $txt['uot_yesterday'];

			$lastLogin = $yesterday . strftime($time_fmt, forum_time(true, $row['lastLogin']));
			$title = ' title="' . $lastLogin . '"';

			if (!empty($row['onlineColor']))
				$link = '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '"' . $title . ' style="color: ' . $row['onlineColor'] . ';">' . $row['realName'] . '</a>';
			else
				$link = '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '"' . $title . '>' . $row['realName'] . '</a>';

			$is_buddy = in_array($row['ID_MEMBER'], $user_info['buddies']);
			if ($is_buddy)
				$link = '<b>' . $link . '</b>';

			$stats['users_online_today'][$row['lastLogin'] . $row['memberName']] = array(
				'id' => $row['ID_MEMBER'],
				'username' => $row['memberName'],
				'name' => $row['realName'],
				'group' => $row['ID_GROUP'],
				'href' => $scripturl . '?action=profile;u=' . $row['ID_MEMBER'],
				'link' => $link,
				'is_buddy' => $is_buddy,
				'hidden' => empty($row['showOnline']),
			);

			$stats['list_users_online_today'][$row['lastLogin'] . $row['memberName']] = empty($row['showOnline']) ? '<i>' . $link . '</i>' : $link;
		}
		mysql_free_result($result);

		krsort($stats['users_online_today']);
		krsort($stats['list_users_online_today']);

		$stats['num_users_online_today'] = count($stats['users_online_today']);
		if (!$user_info['is_admin'])
			$stats['num_users_online_today'] = $stats['num_users_online_today'] + $stats['num_hidden_users_online_today'];

		if (empty($stats['num_users_online_today']))
			return;

		echo '
								<ul class="sp_list">
									<li>', sp_embed_image('dot'), ' ', $txt['sp-online_today'], ': ', $stats['num_users_online_today'], '</li>
								</ul>
								<div class="sp_online_flow">
									<ul class="sp_list">';

		foreach ($stats['users_online_today'] as $user)
			echo '
										<li class="sp_list_indent">', sp_embed_image($user['name'] == 'H' ? 'tux' : 'user'), ' ', $user['hidden'] ? '<em>' . $user['link'] . '</em>' : $user['link'], '</li>';

		echo '
									</ul>
								</div>';
	}
}

function sp_boardStats($parameters, $id, $return_parameters = false)
{
	global  $db_prefix, $scripturl, $modSettings, $txt;

	$block_parameters = array(
		'averages' => 'check',
	);

	if ($return_parameters)
		return $block_parameters;

	$averages = !empty($parameters['averages']) ? 1 : 0;

	loadLanguage('Stats');

	$totals = ssi_boardStats('array');

	if ($averages)
	{
		$result = db_query("
			SELECT
				SUM(posts) AS posts, SUM(topics) AS topics, SUM(registers) AS registers,
				SUM(mostOn) AS mostOn, MIN(date) AS date, SUM(hits) AS hits
			FROM {$db_prefix}log_activity", __FILE__, __LINE__);
		$row = mysql_fetch_assoc($result);
		mysql_free_result($result);

		$total_days_up = ceil((time() - strtotime($row['date'])) / (60 * 60 * 24));

		$totals['average_posts'] = round($row['posts'] / $total_days_up, 2);
		$totals['average_topics'] = round($row['topics'] / $total_days_up, 2);
		$totals['average_members'] = round($row['registers'] / $total_days_up, 2);
		$totals['average_online'] = round($row['mostOn'] / $total_days_up, 2);
	}

	echo '
								<ul class="sp_list">
									<li>', sp_embed_image('stats'), ' ', $txt[488], ': <a href="', $scripturl . '?action=mlist">', $totals['members'], '</a></li>
									<li>', sp_embed_image('stats'), ' ', $txt[489], ': ', $totals['posts'], '</li>
									<li>', sp_embed_image('stats'), ' ', $txt[490], ': ', $totals['topics'], '</li>
									<li>', sp_embed_image('stats'), ' ', $txt[658], ': ', $totals['categories'], '</li>
									<li>', sp_embed_image('stats'), ' ', $txt[665], ': ', $totals['boards'], '</li>
									<li>', sp_embed_image('stats'), ' ', $txt[888], ': ', $modSettings['mostOnline'], '</li>
								</ul>';

	if ($averages)
	{
		echo '
								<hr />
								<ul class="sp_list">
									<li>', sp_embed_image('averages'), ' ', $txt['sp-average_posts'], ': ', $totals['average_posts'], '</li>
									<li>', sp_embed_image('averages'), ' ', $txt['sp-average_topics'], ': ', $totals['average_topics'], '</li>
									<li>', sp_embed_image('averages'), ' ', $txt['sp-average_members'], ': ', $totals['average_members'], '</li>
									<li>', sp_embed_image('averages'), ' ', $txt['sp-average_online'], ': ', $totals['average_online'], '</li>
								</ul>';
	}
}

function sp_topPoster($parameters, $id, $return_parameters = false)
{
	global $db_prefix, $context, $scripturl, $modSettings, $txt, $color_profile;

	$block_parameters = array(
		'limit' => 'int',
		'type' => 'select',
	);

	if ($return_parameters)
		return $block_parameters;

	$limit = !empty($parameters['limit']) ? (int) $parameters['limit'] : 5;
	$type = !empty($parameters['type']) ? (int) $parameters['type'] : 0;

	if (!empty($type))
	{
		if ($type == 1)
		{
			list($year, $month, $day) = explode('-', date('Y-m-d'));
			$start_time = mktime(0, 0, 0, $month, $day, $year);
		}
		elseif ($type == 2)
			$start_time = mktime(0, 0, 0, date("n"), date("j"), date("Y")) - (date("N") * 3600 * 24);
		elseif ($type == 3)
		{
			$months = array( 1 => 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
			$start_time = mktime(0, 0, 0, date("n"), date("j"), date("Y")) - (3600 * 24 * $months[(int) date("m", time())]);
		}

		$start_time = forum_time(false, $start_time);

		$request = db_query("
			SELECT
				mem.ID_MEMBER, mem.realName, COUNT(*) as posts,
				mem.avatar, a.ID_ATTACH, a.attachmentType, a.filename
			FROM {$db_prefix}messages AS m
				LEFT JOIN {$db_prefix}members AS mem ON (mem.ID_MEMBER = m.ID_MEMBER)
				LEFT JOIN {$db_prefix}attachments AS a ON (a.ID_MEMBER = m.ID_MEMBER)
			WHERE m.posterTime > $start_time
				AND m.ID_MEMBER != 0
			GROUP BY mem.ID_MEMBER
			ORDER BY posts DESC
			LIMIT $limit", __FILE__, __LINE__);
	}
	else
	{
		$request = db_query("
			SELECT
				m.ID_MEMBER, m.realName, m.posts, m.avatar,
				a.ID_ATTACH, a.attachmentType, a.filename
			FROM {$db_prefix}members as m
				LEFT JOIN {$db_prefix}attachments AS a ON (a.ID_MEMBER = m.ID_MEMBER)
			ORDER BY posts DESC
			LIMIT $limit", __FILE__, __LINE__);
	}
	$members = array();
	$colorids = array();
	while ($row = mysql_fetch_assoc($request))
	{
		if (!empty($row['ID_MEMBER']))
			$colorids[$row['ID_MEMBER']] = $row['ID_MEMBER'];

		if (stristr($row['avatar'], 'http://') && !empty($modSettings['avatar_check_size']))
		{
			$sizes = url_image_size($row['avatar']);

			if ($modSettings['avatar_action_too_large'] == 'option_refuse' && is_array($sizes) && (($sizes[0] > $modSettings['avatar_max_width_external'] && !empty($modSettings['avatar_max_width_external'])) || ($sizes[1] > $modSettings['avatar_max_height_external'] && !empty($modSettings['avatar_max_height_external']))))
			{
				$row['avatar'] = '';
				updateMemberData($row['ID_MEMBER'], array('avatar' => '\'\''));
			}
		}

		$members[] = array(
			'id' => $row['ID_MEMBER'],
			'name' => $row['realName'],
			'href' => $scripturl . '?action=profile;u=' . $row['ID_MEMBER'],
			'link' => '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '">' . $row['realName'] . '</a>',
			'posts' => comma_format($row['posts']),
			'avatar' => array(
				'name' => $row['avatar'],
				'image' => $row['avatar'] == '' ? ($row['ID_ATTACH'] > 0 ? '<img src="' . (empty($row['attachmentType']) ? $scripturl . '?action=dlattach;attach=' . $row['ID_ATTACH'] . ';type=avatar' : $modSettings['custom_avatar_url'] . '/' . $row['filename']) . '" alt="" class="avatar" border="0" />' : '') : (stristr($row['avatar'], 'http://') ? '<img src="' . $row['avatar'] . '" alt="" class="avatar" border="0" />' : '<img src="' . $modSettings['avatar_url'] . '/' . htmlspecialchars($row['avatar']) . '" alt="" class="avatar" border="0" />'),
				'href' => $row['avatar'] == '' ? ($row['ID_ATTACH'] > 0 ? (empty($row['attachmentType']) ? $scripturl . '?action=dlattach;attach=' . $row['ID_ATTACH'] . ';type=avatar' : $modSettings['custom_avatar_url'] . '/' . $row['filename']) : '') : (stristr($row['avatar'], 'http://') ? $row['avatar'] : $modSettings['avatar_url'] . '/' . $row['avatar']),
				'url' => $row['avatar'] == '' ? '' : (stristr($row['avatar'], 'http://') ? $row['avatar'] : $modSettings['avatar_url'] . '/' . $row['avatar']),
			)
		);
	}
	mysql_free_result($request);

	if (empty($members))
	{
		echo '
								', $txt['error_sp_no_members_found'];
		return;
	}

	if (!empty($colorids) && sp_loadColors($colorids) !== false)
	{
		foreach ($members as $k => $p)
		{
			if (!empty($color_profile[$p['id']]['link']))
				$members[$k]['link'] = $color_profile[$p['id']]['link'];
		}
	}

	echo '
								<table class="sp_fullwidth">';

	foreach ($members as $member)
		echo '
									<tr>
										<td class="sp_top_poster sp_center">', !empty($member['avatar']['href']) ? '
											<a href="' . $scripturl . '?action=profile;u=' . $member['id'] . '"><img src="' . $member['avatar']['href'] . '" alt="' . $member['name'] . '" width="40" /></a>' : '', '
										</td>
										<td>
											', $member['link'], '<br />
											', $member['posts'], ' ', $txt[21], '
										</td>
									</tr>';

	echo '
								</table>';
}

function sp_topStatsMember($parameters, $id, $return_parameters = false)
{
	global $context, $settings, $txt, $scripturl, $user_info, $user_info, $modSettings, $boards, $color_profile;
	global $db_prefix, $sourcedir, $boarddir, $themedir;
	static $sp_topStatsSystem;

	$block_parameters = array(
		'type' => array(
			'0' => $txt['sp_topStatsMember_total_time_logged_in'],
			'1' => $txt['sp_topStatsMember_Posts'],
			'2' => $txt['sp_topStatsMember_Karma_Good'],
			'3' => $txt['sp_topStatsMember_Karma_Bad'],
			'4' => $txt['sp_topStatsMember_Karma_Total'],
			'5' => $txt['sp_topStatsMember_Thank-O-Matic_Top_Given'],
			'6' => $txt['sp_topStatsMember_Thank-O-Matic_Top_Recived'],
			'7' => $txt['sp_topStatsMember_Automatic_Karma_Good'],
			'8' => $txt['sp_topStatsMember_Automatic_Karma_Bad'],
			'9' => $txt['sp_topStatsMember_Automatic_Karma_Total'],
			'10' => $txt['sp_topStatsMember_Advanced_Reputation_System_Best'],
			'11' => $txt['sp_topStatsMember_Advanced_Reputation_System_Worst'],
			'smf_shop_money' => $txt['sp_topStatsMember_SMF_Shop_Money'],
		), 
		'limit' => 'int',
		'sort_asc' => 'check',
		'last_active_limit' => 'int',
		'enable_label' => 'check',
		'list_label' => 'text',
	);

	if ($return_parameters)
		return $block_parameters;

	if (empty($sp_topStatsSystem))
	{
		/*
			The system setup array, order depend on the $txt array of the select
			name
				It's for better knowing what this option can do.
			field
				The members field that should be loaded
				(That what is after the SELECT Statment)
			order
				What is the field name i need to be sort after
			where
				Here you can add additional where statments :)
			output_text
				What should be outputed after the avatar and nickname
				For example if you field is karmaGood
				'output' => $txt['karma'] . '%karmaGood%';
			output_function
				With this you can add to the $row of the query some infomartions.
			reverse
				On true it change the reverse cause, if not set it will be false :)
			enabled
				true = mod exists or is possible to use :D

			'error_msg' => $txt['my_error_msg'];; You can insert here what kind of error message should appear if the modification not exists =D
		*/
		$sp_topStatsSystem = array(
			'0' => array(
				'name' => 'Total time logged in',
				'field' => 'mem.totalTimeLoggedIn',
				'order' => 'mem.totalTimeLoggedIn',
				'output_function' => create_function('&$row', '
					global $txt;
					// Figure out the days, hours and minutes.
					$timeDays = floor($row["totalTimeLoggedIn"] / 86400);
					$timeHours = floor(($row["totalTimeLoggedIn"] % 86400) / 3600);

					// Figure out which things to show... (days, hours, minutes, etc.)
					$timelogged = "";
					if ($timeDays > 0)
						$timelogged .= $timeDays . $txt["totalTimeLogged5"];
					if ($timeHours > 0)
						$timelogged .= $timeHours . $txt["totalTimeLogged6"];
					$timelogged .= floor(($row["totalTimeLoggedIn"] % 3600) / 60) . $txt["totalTimeLogged7"];
					$row["timelogged"] = $timelogged;
				'),
				'output_text' => ' %timelogged%',
				'reverse_sort_asc' => false,
				'enabled' => true,
			),
			'1' => array(
				'name' => 'Posts',
				'field' => 'mem.posts',
				'order' => 'mem.posts',
				'output_text' => ' %posts% '. $txt[21],
				'enabled' => true,
			),
			'2' => array(
				'name' => 'Karma Good',
				'field' => 'mem.karmaGood, mem.karmaBad',
				'order' => 'mem.karmaGood',
				'output_function' => create_function('&$row', '
					$row["karmaTotal"] = $row["karmaGood"] - $row["karmaBad"];
				'),
				'output_text' => $modSettings['karmaLabel'] . ($modSettings['karmaMode'] == 1 ? ' %karmaTotal%' : ' +%karmaGood%\-%karmaBad%'),
				'enabled' => !empty($modSettings['karmaMode']),
				'error_msg' => $txt['sp_karma_is_disabled'],
			),
			'3' => array(
				'name' => 'Karma Bad',
				'field' => 'mem.karmaGood, mem.karmaBad',
				'order' => 'mem.karmaBad',
				'output_function' => create_function('&$row', '
					$row["karmaTotal"] = $row["karmaGood"] - $row["karmaBad"];
				'),
				'output_text' => $modSettings['karmaLabel'] . ($modSettings['karmaMode'] == 1 ? ' %karmaTotal%' : ' +%karmaGood%\-%karmaBad%'),
				'enabled' => !empty($modSettings['karmaMode']),
				'error_msg' => $txt['sp_karma_is_disabled'],
			),
			'4' => array(
				'name' => 'Karma Total',
				'field' => 'mem.karmaGood, mem.karmaBad',
				'order' => 'FLOOR(1000000+karmaGood-karmaBad)',
				'output_function' => create_function('&$row', '
					$row["karmaTotal"] = $row["karmaGood"] - $row["karmaBad"];
				'),
				'output_text' => $modSettings['karmaLabel'] . ($modSettings['karmaMode'] == 1 ? ' %karmaTotal%' : ' +%karmaGood%\-%karmaBad%'),
				'enabled' => !empty($modSettings['karmaMode']),
				'error_msg' => $txt['sp_karma_is_disabled'],
			),
			'5' => array(
				'name' => 'Thank-O-Matic Top Given',
				'mod_id' => 710,
				'field' => 'mem.thank_you_post_made, mem.thank_you_post_became',
				'order' => 'mem.thank_you_post_made',
				'output_text' => '%thank_you_post_made% ' . (!empty($txt['thank_you_post_made_display']) ? $txt['thank_you_post_thx_display'] . ' ' . $txt['thank_you_post_made_display'] : ''),
				'enabled' => file_exists($sourcedir . '/ThankYouPost.php'),
				'error_msg' => $txt['sp_thankomatic_no_exist'],
			),
			'6' => array(
				'name' => 'Thank-O-Matic Top Recived',
				'mod_id' => 710,
				'field' => 'mem.thank_you_post_made, mem.thank_you_post_became',
				'order' => 'mem.thank_you_post_became',
				'output_text' => '%thank_you_post_became% ' . (!empty($txt['thank_you_post_became_display']) ? $txt['thank_you_post_thx_display'] . ' ' . $txt['thank_you_post_became_display'] : ''),
				'enabled' => file_exists($sourcedir . '/ThankYouPost.php'),
				'error_msg' => $txt['sp_thankomatic_no_exist'],
			),
			'7' => array(
				'name' => 'Automatic Karma Good',
				'mod_id' => 1121,
				'field' => 'mem.karmaGood, mem.karmaBad, mem.elianaGood, mem.elianaBad',
				'order' => 'elianaGood+karmaGood',
				'output_function' => create_function('&$row', '
					$row["karmaTotal"] = $row["karmaGood"] - $row["karmaBad"];
					$row["elianaTotal"] = $row["elianaGood"] + $row["karmaGood"] - $row["elianaBad"] - $row["karmaBad"];
					$row["elianaGood"] = $row["elianaGood"] + $row["karmaGood"];
					$row["elianaBad"] = $row["elianaBad"] + $row["karmaBad"];
				'),
				'output_text' => $modSettings['karmaLabel'] . ($modSettings['karmaMode'] == 1 ? ' %elianaTotal%' : ' +%elianaGood%\-%elianaBad%'),
				'enabled' => file_exists($sourcedir . '/ElianaAdmin.php') && !empty($modSettings['eliana_enabled']),
				'error_msg' => $txt['sp_eliana_no_exist'],
			),
			'8' => array(
				'name' => 'Automatic Karma Bad',
				'mod_id' => 1121,
				'field' => 'mem.karmaGood, mem.karmaBad, mem.elianaGood, mem.elianaBad',
				'order' => 'elianaBad+karmaBad',
				'output_function' => create_function('&$row', '
					$row["karmaTotal"] = $row["karmaGood"] - $row["karmaBad"];
					$row["elianaTotal"] = $row["elianaGood"] + $row["karmaGood"] - $row["elianaBad"] - $row["karmaBad"];
					$row["elianaGood"] = $row["elianaGood"] + $row["karmaGood"];
					$row["elianaBad"] = $row["elianaBad"] + $row["karmaBad"];
				'),
				'output_text' => $modSettings['karmaLabel'] . ($modSettings['karmaMode'] == 1 ? ' %elianaTotal%' : ' +%elianaGood%\-%elianaBad%'),
				'enabled' => file_exists($sourcedir . '/ElianaAdmin.php') && !empty($modSettings['eliana_enabled']),
				'error_msg' => $txt['sp_eliana_no_exist'],
			),
			'9' => array(
				'name' => 'Automatic Karma Total',
				'mod_id' => 1121,
				'field' => 'mem.karmaGood, mem.karmaBad, mem.elianaGood, mem.elianaBad',
				'order' => 'FLOOR(1000000+elianaGood+karmaGood-elianaBad-karmaBad)',
				'output_function' => create_function('&$row', '
					$row["karmaTotal"] = $row["karmaGood"] - $row["karmaBad"];
					$row["elianaTotal"] = $row["elianaGood"] + $row["karmaGood"] - $row["elianaBad"] - $row["karmaBad"];
					$row["elianaGood"] = $row["elianaGood"] + $row["karmaGood"];
					$row["elianaBad"] = $row["elianaBad"] + $row["karmaBad"];
				'),
				'output_text' => $modSettings['karmaLabel'] . ($modSettings['karmaMode'] == 1 ? ' %elianaTotal%' : ' +%elianaGood%\-%elianaBad%'),
				'enabled' => file_exists($sourcedir . '/ElianaAdmin.php') && !empty($modSettings['eliana_enabled']),
				'error_msg' => $txt['sp_eliana_no_exist'],
			),
			'10' => array(
				'name' => 'Advanced Reputation System Best',
				'mod_id' => 1129,
				'field' => '(mem.karmaGood - mem.karmaBad) AS karma, mem.karmaGood, mem.karmaBad',
				'order' => 'karma',
				'where' => 'mem.karmaGood > mem.karmaBad',
				'output_function' => create_function('&$row', '
						global $modSettings;
						$descriptions = preg_split("/(\r)?\n/", $modSettings["karmaDescriptions"]);
						$rep_bars = "";

						$points = $row["karma"];
						$bars = ($points - ($points % $modSettings["karmaBarPoints"])) / $modSettings["karmaBarPoints"];
						$bars = $bars < 1 ? 1 : (($bars > $modSettings["karmaMaxBars"]) ? $modSettings["karmaMaxBars"] : $bars);
						$description = $descriptions[$bars - 1];

						for($i = 0; $i < $bars; $i++)
							$rep_bars .= \'<img src=\"\' . $settings["images_url"] . "/karmaGood_" . ($i < ($modSettings["karmaSuperBar"] - 1) ? "basic" : "super") . \'.gif" title="\' . $row["realName"] . " " . $description . \'" alt="\' . $row["realName"] . " " . $description . \'" />\';

						$row += array(
							"reputation_bars" => $rep_bars,
							"amount" => "+" . $row["karma"],
						);
				'),
				'output_text' => (!empty($txt['karma_power']) ? $txt['karma_power'] : '') . ': %amount%<br />%reputation_bars%',
				'enabled' => !empty($modSettings['karma_enabled']) && file_exists($settings['images_url'] . '/karmaBad_basic.gif'),
				'error_msg' => $txt['sp_reputation_no_exist'],
			),
			'11' => array(
				'name' => 'Advanced Reputation System Worst',
				'mod_id' => 1129,
				'field' => '(mem.karmaBad - meme.karmaGood) AS mem.karma, mem.karmaGood, mem.karmaBad',
				'order' => 'karma',
				'where' => 'mem.karmaBad > mem.karmaGood',
				'output_function' => create_function('&$row', '
						global $modSettings;
						$rep_bars = "";

						$points = $row["karma"];
						$bars = ($points - ($points % $modSettings["karmaBarPoints"])) / $modSettings["karmaBarPoints"];
						$bars = $bars < 1 ? 1 : (($bars > $modSettings["karmaMaxBars"]) ? $modSettings["karmaMaxBars"] : $bars);
						$description = $descriptions[$bars - 1];

						for($i = 0; $i < $bars; $i++)
							$rep_bars .= \'<img src=\"\' . $settings["images_url"] . "/karmaGood_" . ($i < ($modSettings["karmaSuperBar"] - 1) ? "basic" : "super") . \'.gif" title="\' . $row["realName"] . " " . $modSettings["karmaNegativeDescription"] . \'" alt="\' . $row["realName"] . " " . $modSettings["karmaNegativeDescription"] . \'" />\';

						$row += array(
							"reputation_bars" => $rep_bars,
							"amount" => "-" . $row["karma"],
						);
				'),
				'output_text' => (!empty($txt['karma_power']) ? $txt['karma_power'] : '') . ': %amount%<br />%reputation_bars%',
				'enabled' => !empty($modSettings['karma_enabled']) && file_exists($settings['images_url'] . '/karmaBad_basic.gif'),
				'error_msg' => $txt['sp_reputation_no_exist'],
			),
			'smf_shop_money' => array(
				'name' => 'Shop Money',
				'mod_id' => 65,
				'field' => 'mem.money',
				'order' => 'mem.money',
				'output_text' => (!empty($modSettings['shopCurrencyPreffix']) ? $modSettings['shopCurrencyPrefix'] : '') . '%money% ' . (!empty($modSettings['shopCurrencySuffix']) ? $modSettings['shopCurrencySuffix'] : ''),
				'enabled' => file_exists($sourcedir . '/shop/Shop.php'),
				'error_msg' => $txt['sp_shop_no_exist'],
			),
		);
	}

	// Standard Variables
	$type = !empty($parameters['type']) ? $parameters['type'] : 0;
	$limit = !empty($parameters['limit']) ? (int) $parameters['limit'] : 5;
	$limit = empty($limit) ? 5 : $limit;
	$sort_asc = !empty($parameters['sort_asc']);
	// Time is in days :D, but i need seconds :P
	$last_active_limit = !empty($parameters['last_active_limit']) ? $parameters['last_active_limit'] * 86400 : 0;
	$enable_label = !empty($parameters['enable_label']);
	$list_label = !empty($parameters['list_label']) ? $parameters['list_label'] : '';

	// Setup current Block Type
	$current_system = !empty($sp_topStatsSystem[$type]) ? $sp_topStatsSystem[$type] : array();

	// What how could this happen?
	if (empty($current_system))
	{
		echo $txt['sp_topstats_unknown_type'];
		return;
	}

	// Possible to ouput?
	if (empty($current_system['enabled']))
	{
		echo (!empty($current_system['error_msg']) ? $current_system['error_msg'] : '');
		return;
	}

	// This are the important fields, without the array have an mistake and it will not work :X
	if (empty($current_system['field']) || empty($current_system['order']))
	{
		echo $context['user']['is_admin'] ? $txt['sp_topstats_type_error'] : $txt['sp_topstats_unknown_type'];
		return;
	}

	// Switch the reverse? (It's a reverse to reverse the allready reverse, fun byside :P)
	$sort_asc = !empty($current_system['reverse']) ? !$sort_asc : $sort_asc;

	// Create the where statment :)
	$where = array();

	// Some cached data availible?
	$chache_id = 'sp_chache_' . $id . '_topStatsMember';
	if (empty($modSettings['sp_disableChache']) && !empty($modSettings[$chache_id]))
	{
		$data = explode(';', $modSettings[$chache_id]);
		if($data[0] == $type && $data[1] == $limit && !empty($data[2]) == $sort_asc && $data[3] > time() - 300) // 5 Minute cache
			$where[] = 'mem.id_member IN (' . $data[4] . ')';
		else
			unset($modSettings[$chache_id]);
	}
	
	// Last active remove?
	if (!empty($last_active_limit))
	{
		$timeLimit = time() - $last_active_limit;
		$where[] = "lastLogin > $timeLimit";
	}
	if (!empty($current_system['where']))
		$where[] = $current_system['where'];

	if (!empty($where))
		$where = 'WHERE (' . implode(')
			AND (', $where) . ')';
	else
		$where = "";

	// Okay load the data :D
	$limitmax = $limit + 5;
	$request = db_query("
		SELECT
			mem.ID_MEMBER, mem.realName, mem.avatar,
			a.ID_ATTACH, a.attachmentType, a.filename,
			$current_system[field]
		FROM {$db_prefix}members as mem
			LEFT JOIN {$db_prefix}attachments AS a ON (a.ID_MEMBER = mem.ID_MEMBER)
		$where
		ORDER BY $current_system[order]" . ($sort_asc ? " ASC" : " DESC" )."
		LIMIT $limitmax", __FILE__, __LINE__);

	$members = array();
	$colorids = array();
	$count = 1;
	$chache_member_ids = array();
	while ($row = mysql_fetch_assoc($request))
	{
		// Collect some to cache data =)
		$chache_member_ids[$row['ID_MEMBER']] = $row['ID_MEMBER'];
		if($count++ > $limit)
			continue;
		
		$colorids[$row['ID_MEMBER']] = $row['ID_MEMBER'];

		if (stristr($row['avatar'], 'http://') && !empty($modSettings['avatar_check_size']))
		{
			$sizes = url_image_size($row['avatar']);

			if ($modSettings['avatar_action_too_large'] == 'option_refuse' && is_array($sizes) && (($sizes[0] > $modSettings['avatar_max_width_external'] && !empty($modSettings['avatar_max_width_external'])) || ($sizes[1] > $modSettings['avatar_max_height_external'] && !empty($modSettings['avatar_max_height_external']))))
			{
				$row['avatar'] = '';
				updateMemberData($row['ID_MEMBER'], array('avatar' => '\'\''));
			}
		}

		// Setup the row :P
		$output = '';

		// Prepare some data of the row?
		if (!empty($current_system['output_function']))
			$current_system['output_function']($row);

		if (!empty($current_system['output_text']))
		{
			$output = $current_system['output_text'];
			foreach ($row as $item => $replacewith)
				$output = str_replace('%' . $item . '%', $replacewith, $output);
		}

		$members[] = array(
			'id' => $row['ID_MEMBER'],
			'name' => $row['realName'],
			'href' => $scripturl . '?action=profile;u=' . $row['ID_MEMBER'],
			'link' => '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '">' . $row['realName'] . '</a>',
			'avatar' => array(
				'name' => $row['avatar'],
				'image' => $row['avatar'] == '' ? ($row['ID_ATTACH'] > 0 ? '<img src="' . (empty($row['attachmentType']) ? $scripturl . '?action=dlattach;attach=' . $row['ID_ATTACH'] . ';type=avatar' : $modSettings['custom_avatar_url'] . '/' . $row['filename']) . '" alt="" class="avatar" border="0" />' : '') : (stristr($row['avatar'], 'http://') ? '<img src="' . $row['avatar'] . '" alt="" class="avatar" border="0" />' : '<img src="' . $modSettings['avatar_url'] . '/' . htmlspecialchars($row['avatar']) . '" alt="" class="avatar" border="0" />'),
				'href' => $row['avatar'] == '' ? ($row['ID_ATTACH'] > 0 ? (empty($row['attachmentType']) ? $scripturl . '?action=dlattach;attach=' . $row['ID_ATTACH'] . ';type=avatar' : $modSettings['custom_avatar_url'] . '/' . $row['filename']) : '') : (stristr($row['avatar'], 'http://') ? $row['avatar'] : $modSettings['avatar_url'] . '/' . $row['avatar']),
				'url' => $row['avatar'] == '' ? '' : (stristr($row['avatar'], 'http://') ? $row['avatar'] : $modSettings['avatar_url'] . '/' . $row['avatar']),
			),
			'output' => $output,
			'complete_row' => $row,
		);
	}
	mysql_free_result($request);

	if (empty($members))
	{
		echo '
								', $txt['error_sp_no_members_found'];
		return;
	}

	// Update the cache, at least around 100 members are needed for a good working version
	if (empty($modSettings['sp_disableChache']) && $context['common_stats']['total_members'] > 100 && !empty($chache_member_ids) && count($chache_member_ids) > $limit && empty($modSettings[$chache_id]))
	{
		$toCache = array($type, $limit, ($sort_asc ? 1 : 0), time(), implode(',', $chache_member_ids));
		updateSettings(array($chache_id => implode(';', $toCache)));
	}
	// One time error, if this happen the chache need an update (Next reload is mystical fixed)
	elseif(!empty($modSettings[$chache_id]))
		updateSettings(array($chache_id => '0;0;0;1000;0'));

	if (!empty($colorids) && sp_loadColors($colorids) !== false)
	{
		foreach ($members as $k => $p)
		{
			if (!empty($color_profile[$p['id']]['link']))
				$members[$k]['link'] = $color_profile[$p['id']]['link'];
		}
	}

	echo '
								<table class="sp_fullwidth">';

	if($enable_label)
		echo '
									<tr>
										<td class="sp_top_poster sp_center" colspan="2"><strong>', $list_label, '</strong></td>
									</tr>';

	foreach ($members as $member)
	{
		echo '
									<tr>
										<td class="sp_top_poster sp_center">', !empty($member['avatar']['href']) ? '
											<a href="' . $scripturl . '?action=profile;u=' . $member['id'] . '"><img src="' . $member['avatar']['href'] . '" alt="' . $member['name'] . '" width="40" /></a>' : '', '
										</td>
										<td>
											', $member['link'], '<br />', $member['output'], '
										</td>
									</tr>';
	}
	echo '
								</table>';

}

function sp_recent($parameters, $id, $return_parameters = false)
{
	global $context, $txt, $scripturl, $settings, $user_info, $color_profile;

	$block_parameters = array(
		'boards' => 'boards',
		'limit' => 'int',
		'type' => 'select',
		'display' => 'select',
	);

	if ($return_parameters)
		return $block_parameters;

	$boards = !empty($parameters['boards']) ? explode('|', $parameters['boards']) : null;
	$limit = !empty($parameters['limit']) ? (int) $parameters['limit'] : 5;
	$type = 'ssi_recent' . (empty($parameters['type']) ? 'Posts' : 'Topics');
	$display = empty($parameters['display']) ? 'compact' : 'full';

	if (!empty($boards))
	{
		$temp_query_see_board = $user_info['query_see_board'];
		$user_info['query_see_board'] .= ' AND b.ID_BOARD IN (' . implode(', ', $boards) . ')';
	}

	$items = $type($limit, null, 'array');

	if (!empty($temp_query_see_board))
		$user_info['query_see_board'] = $temp_query_see_board;

	if (empty($items))
	{
		echo '
								', $txt['error_sp_no_posts_found'];
		return;
	}
	else
		$items[count($items) - 1]['is_last'] = true;

	$colorids = array();
	foreach ($items as $item)
		$colorids[] = $item['poster']['id'];

	if (!empty($colorids) && sp_loadColors($colorids) !== false)
	{
		foreach ($items as $k => $p)
		{
			if (!empty($color_profile[$p['poster']['id']]['link']))
				$items[$k]['poster']['link'] = $color_profile[$p['poster']['id']]['link'];
		}
	}

	if ($display == 'compact')
	{
		foreach ($items as $key => $item)
			echo '
								<a href="', $item['href'], '">', $item['subject'], '</a> <span class="smalltext">', $txt[525], ' ', $item['poster']['link'], $item['new'] ? '' : ' <a href="' . $scripturl . '?topic=' . $item['topic'] . '.msg' . $item['new_from'] . ';topicseen#new" rel="nofollow"><img src="' . $settings['images_url'] . '/' . $context['user']['language'] . '/new.gif" alt="' . $txt[302] . '" border="0" /></a>', '<br />[', $item['time'], ']</span><br />', empty($item['is_last']) ? '<hr />' : '';
	}
	elseif ($display == 'full')
	{
		echo '
								<table class="sp_fullwidth">';

		foreach ($items as $item)
			echo '
									<tr>
										<td class="sp_recent_icon sp_center">
											', sp_embed_image(empty($parameters['type']) ? 'post' : 'topic'), '
										</td>
										<td class="sp_recent_subject">
											<a href="', $item['href'], '">', $item['subject'], '</a>
											', $item['new'] ? '' : '<a href="' . $scripturl . '?topic=' . $item['topic'] . '.msg' . $item['new_from'] . ';topicseen#new"><img src="' . $settings['images_url'] . '/' . $context['user']['language'] . '/new.gif" alt="' . $txt[302] . '" border="0" /></a>', '<br />[', $item['board']['link'], ']
										</td>
										<td class="sp_recent_info sp_right">
											', $item['poster']['link'], '<br />', $item['time'], '
										</td>
									</tr>';

		echo '
								</table>';
	}
}

function sp_topTopics($parameters, $id, $return_parameters = false)
{
	global $txt, $scripturl, $user_info, $user_info, $modSettings, $topics;

	$block_parameters = array(
		'type' => 'select',
		'limit' => 'int',
	);

	if ($return_parameters)
		return $block_parameters;

	$type = !empty($parameters['type']) ? $parameters['type'] : 0;
	$limit = !empty($parameters['limit']) ? $parameters['limit'] : 5;

	$topics = ssi_topTopics($type ? 'views' : 'replies', $limit, 'array');

	if (empty($topics))
	{
		echo '
								', $txt['error_sp_no_topics_found'];
		return;
	}
	else
		$topics[count($topics) - 1]['is_last'] = true;

	echo '
								<ul class="sp_list">';

	foreach ($topics as $topic)
		echo '
									<li class="sp_list_top">', sp_embed_image('topic'), ' ', $topic['link'], '</li>
									<li class="sp_list_indent', empty($topic['is_last']) ? ' sp_list_bottom' : '', ' smalltext">', $txt[110], ': ', $topic['num_replies'], ' | ', $txt[301], ': ', $topic['num_views'], '</li>';

	echo '
								</ul>';
}

function sp_topBoards($parameters, $id, $return_parameters = false)
{
	global $context, $settings, $txt, $scripturl, $user_info, $user_info, $modSettings, $boards;

	$block_parameters = array(
		'limit' => 'int',
	);

	if ($return_parameters)
		return $block_parameters;

	$limit = !empty($parameters['limit']) ? $parameters['limit'] : 5;

	$boards = ssi_topBoards($limit, 'array');

	if (empty($boards))
	{
		echo '
								', $txt['error_sp_no_boards_found'];
		return;
	}
	else
		$boards[count($boards) - 1]['is_last'] = true;

	echo '
								<ul class="sp_list">';

	foreach ($boards as $board)
		echo '
									<li class="sp_list_top">', sp_embed_image('board'), ' ', $board['link'], '</li>
									<li class="sp_list_indent', empty($board['is_last']) ? ' sp_list_bottom' : '', ' smalltext">', $txt[330], ': ', comma_format($board['num_topics']), ' | ', $txt[21], ': ', comma_format($board['num_posts']), '</li>';

	echo '
								</ul>';
}

function sp_showPoll($parameters, $id, $return_parameters = false)
{
	global $db_prefix, $context, $scripturl, $boardurl, $user_info, $txt;

	$block_parameters = array(
		'topic' => 'int',
		'type' => 'select',
	);

	if ($return_parameters)
		return $block_parameters;

	$topic = !empty($parameters['topic']) ? $parameters['topic'] : null;
	$type = !empty($parameters['type']) ? (int) $parameters['type'] : 0;
	$boardsAllowed = boardsAllowedTo('poll_view');

	if (empty($boardsAllowed))
	{
		loadLanguage('Errors');

		echo '
								', $txt['cannot_poll_view'];
		return;
	}

	if (!empty($type))
	{
		$request = db_query("
			SELECT t.ID_TOPIC
			FROM ({$db_prefix}polls AS p, {$db_prefix}boards AS b, {$db_prefix}topics AS t)
			WHERE t.ID_POLL = p.ID_POLL
				AND p.votingLocked = 0
				AND b.ID_BOARD = t.ID_BOARD
				AND $user_info[query_see_board]" . (!in_array(0, $boardsAllowed) ? "
				AND b.ID_BOARD IN (" . implode(', ', $boardsAllowed) . ")" : '') . "
			ORDER BY " . ($type == 1 ? 'p.ID_POLL DESC' : 'RAND()') . "
			LIMIT 1", __FILE__, __LINE__);
		list ($topic) = mysql_fetch_row($request);
		mysql_free_result($request);
	}

	if (empty($topic) || $topic < 0)
	{
		loadLanguage('Errors');

		echo '
								', $txt[472];
		return;
	}

	$poll = ssi_showPoll($topic, 'array');

	if (empty($poll))
	{
		echo '
								', $txt['error_sp_no_polls_found'];
		return;
	}

	if ($poll['allow_vote'])
	{
		echo '
								<form action="', $boardurl, '/SSI.php?ssi_function=pollVote" method="post" accept-charset="', $context['character_set'], '">
									<ul class="sp_list">
										<li><strong>', $poll['question'], '</strong></li>
										<li>', $poll['allowed_warning'], '</li>';

		foreach ($poll['options'] as $option)
			echo '
										<li><label for="', $option['id'], '">', $option['vote_button'], ' ', $option['option'], '</label></li>';

		echo '
										<li class="sp_center"><input type="submit" value="', $txt['smf23'], '" /></li>
										<li class="sp_center"><a href="', $scripturl, '?topic=', $poll['topic'], '.0">', $txt['sp-pollViewTopic'], '</a></li>
									</ul>
									<input type="hidden" name="sc" value="', $context['session_id'], '" />
									<input type="hidden" name="poll" value="', $poll['id'], '" />
								</form>';
	}
	else
	{
		echo '
								<ul class="sp_list">
									<li><strong>', $poll['question'], '</strong></li>';

		foreach ($poll['options'] as $option)
			echo '
									<li>', sp_embed_image('dot'), ' ', $option['option'], '</li>
									<li class="sp_list_indent"><strong>', $option['votes'], '</strong> (', $option['percent'], '%)</li>
									<li>', $option['bar'], '</li>';

		echo '
									<li><strong>', $txt['smf24'], ': ', $poll['total_votes'], '</strong></li>
									<li class="sp_center"><a href="', $scripturl, '?topic=', $poll['topic'], '.0">', $txt['sp-pollViewTopic'], '</a></li>
								</ul>';
	}
}

function sp_boardNews($parameters, $id, $return_parameters = false)
{
	global $scripturl, $db_prefix, $txt, $settings, $user_info, $modSettings, $context, $color_profile, $func;

	loadLanguage('Stats');

	$block_parameters = array(
		'board' => 'boards',
		'limit' => 'int',
		'start' => 'int',
		'length' => 'int',
		'avatar' => 'check',
		'per_page' => 'int',
	);

	if ($return_parameters)
		return $block_parameters;

	$board = !empty($parameters['board']) ? explode('|', $parameters['board']) : null;
	$limit = !empty($parameters['limit']) ? (int) $parameters['limit'] : 5;
	$start = !empty($parameters['start']) ? (int) $parameters['start'] : 0;
	$length = isset($parameters['length']) ? (int) $parameters['length'] : 250;
	$avatars = !empty($parameters['avatar']);
	$per_page = !empty($parameters['per_page']) ? (int) $parameters['per_page'] : 0;
	$style = !empty($parameters['style']) ? $parameters['style'] : sportal_parse_style('explode', '', true);

	$limit = max(0, $limit);
	$start = max(0, $start);

	$stable_icons = array('xx', 'thumbup', 'thumbdown', 'exclamation', 'question', 'lamp', 'smiley', 'angry', 'cheesy', 'grin', 'sad', 'wink', 'moved', 'recycled', 'wireless');
	$icon_sources = array();
	foreach ($stable_icons as $icon)
		$icon_sources[$icon] = 'images_url';

	$request = db_query("
		SELECT t.ID_FIRST_MSG
		FROM {$db_prefix}topics AS t
			INNER JOIN {$db_prefix}boards AS b ON (b.ID_BOARD = t.ID_BOARD)
			INNER JOIN {$db_prefix}messages AS m ON (m.ID_MSG = t.ID_FIRST_MSG)
		WHERE $user_info[query_see_board]
			AND " . (empty($board) ? "t.ID_FIRST_MSG >= " . ($modSettings['maxMsgID'] - 45 * min($limit, 5)) : "b.ID_BOARD IN (" . implode(', ', $board) . ")") . "
			AND (t.locked != 1 OR m.icon != 'moved')
		ORDER BY t.ID_FIRST_MSG DESC
		LIMIT $limit", __FILE__, __LINE__);
	$posts = array();
	while ($row = mysql_fetch_assoc($request))
		$posts[] = $row['ID_FIRST_MSG'];
	mysql_free_result($request);

	if (empty($posts))
	{
		echo '
				', $txt['error_sp_no_posts_found'];
		return;
	}
	elseif (!empty($per_page))
	{
		$limit = count($posts);
		$start = !empty($_REQUEST['news' . $id]) ? (int) $_REQUEST['news' . $id] : 0;

		$clean_url = str_replace('%', '%%', preg_replace('~news' . $id . '=[^;]+;?~', '', $_SERVER['REQUEST_URL']));
		$current_url = $clean_url . (strpos($clean_url, '?') !== false ? (in_array(substr($clean_url, -1), array(';', '?')) ? '' : ';') : '?');

		$page_index = constructPageIndex($current_url . 'news' . $id . '=%1$d', $start, $limit, $per_page, true);
	}

	$request = db_query("
		SELECT
			m.icon, m.subject, m.body, IFNULL(mem.realName, m.posterName) AS posterName, m.posterTime,
			t.numReplies, t.numViews, t.ID_TOPIC, m.ID_MEMBER, m.smileysEnabled, m.ID_MSG, t.locked,
			mem.avatar, a.ID_ATTACH, a.attachmentType, a.filename
		FROM ({$db_prefix}topics AS t, {$db_prefix}messages AS m)
			LEFT JOIN {$db_prefix}members AS mem ON (mem.ID_MEMBER = m.ID_MEMBER)
			LEFT JOIN {$db_prefix}attachments AS a ON (a.ID_MEMBER = mem.ID_MEMBER)
		WHERE t.ID_FIRST_MSG IN (" . implode(', ', $posts) . ")
			AND m.ID_MSG = t.ID_FIRST_MSG
		ORDER BY t.ID_FIRST_MSG DESC
		LIMIT " . (!empty($per_page) ? $start . ', ' . $per_page : $limit), __FILE__, __LINE__);
	$return = array();
	$colorids = array();
	while ($row = mysql_fetch_assoc($request))
	{
		$limited = false;
		if (($cutoff = $func['strpos']($row['body'], '[cutoff]')) !== false)
		{
			$row['body'] = $func['substr']($row['body'], 0, $cutoff);
			$limited = true;
		}
		elseif (!empty($length) && $func['strlen']($row['body']) > $length)
		{
			$row['body'] = $func['substr']($row['body'], 0, $length);
			$limited = true;
		}

		$row['body'] = parse_bbc($row['body'], $row['smileysEnabled'], $row['ID_MSG']);

		// Only place an ellipsis if the body has been shortened.
		if ($limited)
			$row['body'] .= '<a href="' . $scripturl . '?topic=' . $row['ID_TOPIC'] . '.0" title="' . $row['subject'] . '">...</a>';

		if (!isset($icon_sources[$row['icon']]))
			$icon_sources[$row['icon']] = file_exists($settings['theme_dir'] . '/images/post/' . $row['icon'] . '.gif') ? 'images_url' : 'default_images_url';

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

		if (!empty($row['ID_MEMBER']))
			$colorids[$row['ID_MEMBER']] = $row['ID_MEMBER'];

		$return[] = array(
			'id' => $row['ID_TOPIC'],
			'message_id' => $row['ID_MSG'],
			'icon' => '<img src="' . $settings[$icon_sources[$row['icon']]] . '/post/' . $row['icon'] . '.gif" align="middle" alt="' . $row['icon'] . '" border="0" />',
			'subject' => $row['subject'],
			'time' => timeformat($row['posterTime']),
			'views' => $row['numViews'],
			'body' => $row['body'],
			'href' => $scripturl . '?topic=' . $row['ID_TOPIC'] . '.0',
			'link' => '<a href="' . $scripturl . '?topic=' . $row['ID_TOPIC'] . '.0">' . $txt['sp-read_more'] . '</a>',
			'replies' => $row['numReplies'],
			'comment_href' => !empty($row['locked']) ? '' : $scripturl . '?action=post;topic=' . $row['ID_TOPIC'] . '.' . $row['numReplies'] . ';num_replies=' . $row['numReplies'],
			'comment_link' => !empty($row['locked']) ? '' : '| <a href="' . $scripturl . '?action=post;topic=' . $row['ID_TOPIC'] . '.' . $row['numReplies'] . ';num_replies=' . $row['numReplies'] . '">' . $txt['smf_news_3'] . '</a>',
			'new_comment' => !empty($row['locked']) ? '' : '| <a href="' . $scripturl . '?action=post;topic=' . $row['ID_TOPIC'] . '.' . $row['numReplies'] . '">' . $txt['smf_news_3'] . '</a>',
			'poster' => array(
				'id' => $row['ID_MEMBER'],
				'name' => $row['posterName'],
				'href' => !empty($row['ID_MEMBER']) ? $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] : '',
				'link' => !empty($row['ID_MEMBER']) ? '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '">' . $row['posterName'] . '</a>' : $row['posterName']
			),
			'locked' => !empty($row['locked']),
			'is_last' => false,
			'avatar' => array(
				'name' => $row['avatar'],
				'image' => $row['avatar'] == '' ? ($row['ID_ATTACH'] > 0 ? '<img src="' . (empty($row['attachmentType']) ? $scripturl . '?action=dlattach;attach=' . $row['ID_ATTACH'] . ';type=avatar' : $modSettings['custom_avatar_url'] . '/' . $row['filename']) . '" alt="" class="avatar" border="0" />' : '') : (stristr($row['avatar'], 'http://') ? '<img src="' . $row['avatar'] . '" alt="" class="avatar" border="0" />' : '<img src="' . $modSettings['avatar_url'] . '/' . htmlspecialchars($row['avatar']) . '" alt="" class="avatar" border="0" />'),
				'href' => $row['avatar'] == '' ? ($row['ID_ATTACH'] > 0 ? (empty($row['attachmentType']) ? $scripturl . '?action=dlattach;attach=' . $row['ID_ATTACH'] . ';type=avatar' : $modSettings['custom_avatar_url'] . '/' . $row['filename']) : '') : (stristr($row['avatar'], 'http://') ? $row['avatar'] : $modSettings['avatar_url'] . '/' . $row['avatar']),
				'url' => $row['avatar'] == '' ? '' : (stristr($row['avatar'], 'http://') ? $row['avatar'] : $modSettings['avatar_url'] . '/' . $row['avatar']),
			),
		);
	}
	mysql_free_result($request);

	if (empty($return))
	{
		echo '
				', $txt['error_sp_no_posts_found'];
		return;
	}

	$return[count($return) - 1]['is_last'] = true;

	if (!empty($colorids) && sp_loadColors($colorids) !== false)
	{
		foreach ($return as $k => $p)
		{
			if (!empty($color_profile[$p['poster']['id']]['link']))
				$return[$k]['poster']['link'] = $color_profile[$p['poster']['id']]['link'];
		}
	}

	foreach ($return as $news)
	{
		echo '
				<div class="sp_article_content">
					<div class="sp_block_container', !empty($style['no_body']) ? '' : ' tborder', '">
						<table class="sp_block">';

		if (empty($style['no_title']))
		{
			echo '
							<tr>
								<td class="sp_middle ', $style['title']['class'], '"', !empty($style['title']['style']) ? ' style="' . $style['title']['style'] . '"' : '', '>', $news['icon'], '</td>
								<td class="sp_middle sp_regular_padding sp_fullwidth ', $style['title']['class'], '"', !empty($style['title']['style']) ? ' style="' . $style['title']['style'] . '"' : '', '><a href="', $news['href'], '" >', $news['subject'], '</a></td>
							</tr>';
		}

		echo '
							<tr>
								<td class="sp_block_padding', empty($style['body']['class']) ? '' : ' ' . $style['body']['class'], '"', !empty($style['body']['style']) ? ' style="' . $style['body']['style'] . '"' : '', ' colspan="2">';

		if ($avatars && $news['avatar']['name'] !== null && !empty($news['avatar']['href']))
			echo '
									<a href="', $scripturl, '?action=profile;u=', $news['poster']['id'], '"><img src="', $news['avatar']['href'], '" alt="', $news['poster']['name'], '" width="30" style="float: right;" /></a>
									<div class="middletext">', $news['time'], ' ', $txt[525], ' ', $news['poster']['link'], '<br />', $txt['sp-articlesViews'], ': ', $news['views'], ' | ', $txt['sp-articlesComments'], ': ', $news['replies'], '</div>';
		else
			echo '
									<div class="middletext">', $news['time'], ' ', $txt[525], ' ', $news['poster']['link'], ' | ', $txt['sp-articlesViews'], ': ', $news['views'], ' | ', $txt['sp-articlesComments'], ': ', $news['replies'], '</div>';

		echo '
									<div class="post"><hr />', $news['body'], '<br /><br /></div>
									<div class="sp_right sp_regular_padding">', $news['link'], ' ',  $news['new_comment'], '</div>
								</td>
							</tr>
						</table>
					</div>
				</div>';
	}

	if (!empty($per_page))
		echo '
				<div class="sp_page_index">', $txt['sp-articlesPages'], ': ', $page_index, '</div>';
}

function sp_quickSearch($parameters, $id, $return_parameters = false)
{
	global $scripturl, $txt, $context;

	$block_parameters = array();

	if ($return_parameters)
		return $block_parameters;

	echo '
								<form action="', $scripturl, '?action=search2" method="post" accept-charset="', $context['character_set'], '">
									<div class="sp_center">
										<input type="text" name="search" value="" class="sp_search" /><br />
										<input type="submit" name="submit" value="', $txt[182], '" />
										<input type="hidden" name="advanced" value="0" />';

	if (!empty($context['current_topic']))
		echo '
										<input type="hidden" name="topic" value="', $context['current_topic'], '" />';
	elseif (!empty($context['current_board']))
		echo '
										<input type="hidden" name="brd[', $context['current_board'], ']" value="', $context['current_board'], '" />';

	echo '
									</div>
								</form>';
}

function sp_news($parameters, $id, $return_parameters = false)
{
	global $context;

	$block_parameters = array();

	if ($return_parameters)
		return $block_parameters;

	echo '
								<div class="sp_center sp_fullwidth">', $context['random_news_line'], '</div>';
}

function sp_attachmentImage($parameters, $id, $return_parameters = false)
{
	global $db_prefix, $modSettings, $scripturl, $txt, $settings, $user_info;

	$block_parameters = array(
		'limit' => 'int',
		'direction' => 'select',
		'disablePoster' => 'check',
		'disableDownloads' => 'check',
		'disableLink' => 'check',
	);

	if ($return_parameters)
		return $block_parameters;

	$limit = empty($parameters['limit']) ? 5 : (int) $parameters['limit'];
	$direction = empty($parameters['direction']) ? 0 : 1;
	$type = array('jpg', 'png', 'gif', 'bmp');
	$boards = boardsAllowedTo('view_attachments');
	$showPoster = empty($parameters['disablePoster']);
	$showDownloads = empty($parameters['disableDownloads']);
	$showLink = empty($parameters['disableLink']);

	if (empty($boards))
	{
		echo '
								', $txt['error_sp_no_attachments_found'];
		return;
	}
	elseif ($boards[0] == 0)
		$boards = '';
	else
		$boards = ' AND m.ID_BOARD IN (' . implode(',', $boards) . ')';

	$request = db_query("
		SELECT
			att.ID_ATTACH, att.ID_MSG, att.filename, IFNULL(att.size, 0) AS filesize, att.downloads, mem.ID_MEMBER,
			IFNULL(mem.realName, m.posterName) AS posterName, m.ID_TOPIC, m.subject, t.ID_BOARD, m.posterTime,
			att.width, att.height" . (empty($modSettings['attachmentShowImages']) || empty($modSettings['attachmentThumbnails']) ? "" : ", IFNULL(thumb.ID_ATTACH, 0) AS id_thumb, thumb.width AS thumb_width, thumb.height AS thumb_height") . "
		FROM {$db_prefix}attachments AS att
			INNER JOIN {$db_prefix}messages AS m ON (m.ID_MSG = att.ID_MSG)
			INNER JOIN {$db_prefix}topics AS t ON (t.ID_TOPIC = m.ID_TOPIC)
			LEFT JOIN {$db_prefix}members AS mem ON (mem.ID_MEMBER = m.ID_MEMBER)" . (empty($modSettings['attachmentShowImages']) || empty($modSettings['attachmentThumbnails']) ? "" : "
			LEFT JOIN {$db_prefix}attachments AS thumb ON (thumb.ID_ATTACH = att.ID_THUMB)") . "
			LEFT JOIN {$db_prefix}boards AS b ON (b.ID_BOARD = t.ID_BOARD)
		WHERE att.attachmentType = 0
			AND att.width != 0
			AND $user_info[query_see_board]
			$boards
		ORDER BY att.ID_ATTACH DESC
		LIMIT $limit", __FILE__, __LINE__);
	$items = array();
	while ($row = mysql_fetch_assoc($request))
	{
		$filename = preg_replace('~&amp;#(\\d{1,7}|x[0-9a-fA-F]{1,6});~', '&#\\1;', htmlspecialchars($row['filename']));

		$items[$row['ID_ATTACH']] = array(
			'member' => array(
				'id' => $row['ID_MEMBER'],
				'name' => $row['posterName'],
				'link' => empty($row['ID_MEMBER']) ? $row['posterName'] : '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '">' . $row['posterName'] . '</a>',
			),
			'file' => array(
				'filename' => $filename,
				'filesize' => round($row['filesize'] /1024, 2) . $txt['smf211'],
				'downloads' => $row['downloads'],
				'href' => $scripturl . '?action=dlattach;topic=' . $row['ID_TOPIC'] . '.0;attach=' . $row['ID_ATTACH'],
				'link' => '<img src="' . $settings['images_url'] . '/icons/clip.gif" alt="" /> <a href="' . $scripturl . '?action=dlattach;topic=' . $row['ID_TOPIC'] . '.0;attach=' . $row['ID_ATTACH'] . '">' . $filename . '</a>',
				'is_image' => !empty($row['width']) && !empty($row['height']) && !empty($modSettings['attachmentShowImages']),
			),
			'topic' => array(
				'id' => $row['ID_TOPIC'],
				'subject' => $row['subject'],
				'href' => $scripturl . '?topic=' . $row['ID_TOPIC'] . '.msg' . $row['ID_MSG'] . '#msg' . $row['ID_MSG'],
				'link' => '<a href="' . $scripturl . '?topic=' . $row['ID_TOPIC'] . '.msg' . $row['ID_MSG'] . '#msg' . $row['ID_MSG'] . '">' . $row['subject'] . '</a>',
				'time' => timeformat($row['posterTime']),
			),
		);

		if ($items[$row['ID_ATTACH']]['file']['is_image'])
		{
			$id_thumb = empty($row['ID_THUMB']) ? $row['ID_ATTACH'] : $row['ID_THUMB'];
			$items[$row['ID_ATTACH']]['file']['image'] = array(
				'id' => $id_thumb,
				'width' => $row['width'],
				'height' => $row['height'],
				'img' => '<img src="' . $scripturl . '?action=dlattach;topic=' . $row['ID_TOPIC'] . '.0;attach=' . $row['ID_ATTACH'] . ';image" alt="' . $filename . '" />',
				'thumb' => '<img src="' . $scripturl . '?action=dlattach;topic=' . $row['ID_TOPIC'] . '.0;attach=' . $id_thumb . ';image" alt="' . $filename . '" />',
				'href' => $scripturl . '?action=dlattach;topic=' . $row['ID_TOPIC'] . '.0;attach=' . $id_thumb . ';image',
				'link' => '<a href="' . $scripturl . '?action=dlattach;topic=' . $row['ID_TOPIC'] . '.0;attach=' . $row['ID_ATTACH'] . ';image"><img src="' . $scripturl . '?action=dlattach;topic=' . $row['ID_TOPIC'] . '.0;attach=' . $id_thumb . ';image" alt="' . $filename . '"' . (empty($row['ID_THUMB']) ? ' width="100"' : '') . ' /></a>',
			);
		}
	}
	mysql_free_result($request);

	if (empty($items))
	{
		echo '
								', $txt['error_sp_no_attachments_found'];
		return;
	}

	$colorids = array();
	foreach ($items as $item)
		$colorids[] = $item['member']['id'];

	if (!empty($colorids) && sp_loadColors($colorids) !== false)
	{
		foreach ($items as $k => $p)
		{
			if (!empty($color_profile[$p['member']['id']]['link']))
				$items[$k]['member']['link'] = $color_profile[$p['member']['id']]['link'];
		}
	}

	echo '
								<table class="sp_auto_align">', $direction ? '
									<tr>' : '';

	foreach ($items as $item)
	{
	  echo !$direction ? '
									<tr>' : '', '
										<td>
											<div class="sp_image smalltext">', ($showLink ? '
												<a href="' . $item['file']['href'] . '">' . $item['file']['filename'] . '</a><br />' : ''), '
												', $item['file']['image']['link'], '<br />', ($showDownloads ? '
												' . $txt['sp-downloadsCount'] . ': ' . $item['file']['downloads'] . '<br />' : ''), ($showPoster ? '
												' . $txt['sp-downloadsPoster'] . ': ' . $item['member']['link'] : ''), '
											</div>
										</td>', !$direction ? '
									</tr>' : '';
	}

	echo $direction ? '
									</tr>' : '', '
								</table>';
}

function sp_attachmentRecent($parameters, $id, $return_parameters = false)
{
	global $db_prefix, $modSettings, $scripturl, $txt, $settings, $user_info;

	$block_parameters = array(
		'limit' => 'int',
	);

	if ($return_parameters)
		return $block_parameters;

	$limit = empty($parameters['limit']) ? 5 : (int) $parameters['limit'];
	$boards = boardsAllowedTo('view_attachments');

	if (empty($boards))
	{
		echo '
								', $txt['error_sp_no_attachments_found'];
		return;
	}
	elseif ($boards[0] == 0)
		$boards = '';
	else
		$boards = ' AND m.ID_BOARD IN (' . implode(',', $boards) . ')';

	$request = db_query("
		SELECT
			att.ID_ATTACH, att.ID_MSG, att.filename, IFNULL(att.size, 0) AS filesize, att.downloads, mem.ID_MEMBER,
			IFNULL(mem.realName, m.posterName) AS posterName, m.ID_TOPIC, m.subject, t.ID_BOARD, m.posterTime,
			att.width, att.height" . (empty($modSettings['attachmentShowImages']) || empty($modSettings['attachmentThumbnails']) ? "" : ", IFNULL(thumb.ID_ATTACH, 0) AS id_thumb, thumb.width AS thumb_width, thumb.height AS thumb_height") . "
		FROM {$db_prefix}attachments AS att
			INNER JOIN {$db_prefix}messages AS m ON (m.ID_MSG = att.ID_MSG)
			INNER JOIN {$db_prefix}topics AS t ON (t.ID_TOPIC = m.ID_TOPIC)
			LEFT JOIN {$db_prefix}members AS mem ON (mem.ID_MEMBER = m.ID_MEMBER)" . (empty($modSettings['attachmentShowImages']) || empty($modSettings['attachmentThumbnails']) ? "" : "
			LEFT JOIN {$db_prefix}attachments AS thumb ON (thumb.ID_ATTACH = att.ID_THUMB)") . "
			LEFT JOIN {$db_prefix}boards AS b ON (b.ID_BOARD = t.ID_BOARD)
		WHERE att.attachmentType = 0
			AND $user_info[query_see_board]
			$boards
		ORDER BY att.ID_ATTACH DESC
		LIMIT $limit", __FILE__, __LINE__);
	$items = array();
	while ($row = mysql_fetch_assoc($request))
	{
		$filename = preg_replace('~&amp;#(\\d{1,7}|x[0-9a-fA-F]{1,6});~', '&#\\1;', htmlspecialchars($row['filename']));

		$items[$row['ID_ATTACH']] = array(
			'member' => array(
				'id' => $row['ID_MEMBER'],
				'name' => $row['posterName'],
				'link' => empty($row['ID_MEMBER']) ? $row['posterName'] : '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '">' . $row['posterName'] . '</a>',
			),
			'file' => array(
				'filename' => $filename,
				'filesize' => round($row['filesize'] /1024, 2) . $txt['smf211'],
				'downloads' => $row['downloads'],
				'href' => $scripturl . '?action=dlattach;topic=' . $row['ID_TOPIC'] . '.0;attach=' . $row['ID_ATTACH'],
				'link' => '<img src="' . $settings['images_url'] . '/icons/clip.gif" alt="" /> <a href="' . $scripturl . '?action=dlattach;topic=' . $row['ID_TOPIC'] . '.0;attach=' . $row['ID_ATTACH'] . '">' . $filename . '</a>',
				'is_image' => !empty($row['width']) && !empty($row['height']) && !empty($modSettings['attachmentShowImages']),
			),
			'topic' => array(
				'id' => $row['ID_TOPIC'],
				'subject' => $row['subject'],
				'href' => $scripturl . '?topic=' . $row['ID_TOPIC'] . '.msg' . $row['ID_MSG'] . '#msg' . $row['ID_MSG'],
				'link' => '<a href="' . $scripturl . '?topic=' . $row['ID_TOPIC'] . '.msg' . $row['ID_MSG'] . '#msg' . $row['ID_MSG'] . '">' . $row['subject'] . '</a>',
				'time' => timeformat($row['posterTime']),
			),
		);
	}
	mysql_free_result($request);

	if (empty($items))
	{
		echo '
								', $txt['error_sp_no_attachments_found'];
		return;
	}

	echo '
								<ul class="sp_list">';

	foreach ($items as $item)
		echo '
									<li>', sp_embed_image('attach'), ' <a href="', $item['file']['href'], '">', $item['file']['filename'], '</a></li>
									<li class="smalltext">', $txt['sp-downloadsCount'], ': ', $item['file']['downloads'], '</li>
									<li class="smalltext">', $txt['sp-downloadsSize'], ': ', $item['file']['filesize'], '</li>';

	echo '
								</ul>';
}

function sp_calendar($parameters, $id, $return_parameters = false)
{
	global $context, $sourcedir, $modSettings, $options, $scripturl, $txt;

	$block_parameters = array(
		'events' => 'check',
		'birthdays' => 'check',
		'holidays' => 'check',
	);

	if ($return_parameters)
		return $block_parameters;

	require_once($sourcedir . '/Calendar.php');
	$today = array(
		'day' => (int) strftime('%d', forum_time()),
		'month' => (int) strftime('%m', forum_time()),
		'year' => (int) strftime('%Y', forum_time()),
		'date' => strftime('%Y-%m-%d', forum_time()),
	);

	$curPage = array(
		'day' => $today['day'],
		'month' => $today['month'],
		'year' => $today['year']
	);

	$calendarOptions = array(
		'start_day' => !empty($options['calendar_start_day']) ? $options['calendar_start_day'] : 0,
		'show_events' => !empty($parameters['events']),
		'show_birthdays' => !empty($parameters['birthdays']),
		'show_holidays' => !empty($parameters['holidays']),
	);

	$calendar_data = array(
		'week_days' => array(),
		'weeks' => array(),
		'current_month' => $curPage['month'],
		'current_year' => $curPage['year'],
	);

	$month_info = array(
		'first_day' => array(
			'day_of_week' => (int) strftime('%w', mktime(0, 0, 0, $curPage['month'], 1, $curPage['year'])),
			'week_num' => (int) strftime('%U', mktime(0, 0, 0, $curPage['month'], 1, $curPage['year'])),
			'date' => strftime('%Y-%m-%d', mktime(0, 0, 0, $curPage['month'], 1, $curPage['year'])),
		),
		'last_day' => array(
			'day_of_month' => (int) strftime('%d', mktime(0, 0, 0, $curPage['month'] == 12 ? 1 : $curPage['month'] + 1, 0, $curPage['month'] == 12 ? $curPage['year'] + 1 : $curPage['year'])),
			'date' => strftime('%Y-%m-%d', mktime(0, 0, 0, $curPage['month'] == 12 ? 1 : $curPage['month'] + 1, 0, $curPage['month'] == 12 ? $curPage['year'] + 1 : $curPage['year'])),
		),
		'first_day_of_year' => (int) strftime('%w', mktime(0, 0, 0, 1, 1, $curPage['year'])),
	);

	$nShift = $month_info['first_day']['day_of_week'];
	$calendarOptions['start_day'] = empty($calendarOptions['start_day']) ? 0 : (int) $calendarOptions['start_day'];

	if (!empty($calendarOptions['start_day']))
	{
		$nShift -= $calendarOptions['start_day'];
		if ($nShift < 0)
			$nShift = 7 + $nShift;
	}

	$nRows = floor(($month_info['last_day']['day_of_month'] + $nShift) / 7);
	if (($month_info['last_day']['day_of_month'] + $nShift) % 7)
		$nRows++;

	$bday = $calendarOptions['show_birthdays'] ? calendarBirthdayArray($month_info['first_day']['date'], $month_info['last_day']['date']) : array();
	$events = $calendarOptions['show_events'] ? calendarEventArray($month_info['first_day']['date'], $month_info['last_day']['date']) : array();
	$holidays = $calendarOptions['show_holidays'] ? calendarHolidayArray($month_info['first_day']['date'], $month_info['last_day']['date']) : array();

	$count = $calendarOptions['start_day'];
	for ($i = 0; $i < 7; $i++)
	{
		$calendar_data['week_days'][] = $count;
		$count++;
		if ($count == 7)
			$count = 0;
	}

	$nWeekAdjust = 0;
	$calendar_data['weeks'] = array();
	for ($nRow = 0; $nRow < $nRows; $nRow++)
	{
		$calendar_data['weeks'][$nRow] = array(
			'days' => array(),
			'number' => $month_info['first_day']['week_num'] + $nRow + $nWeekAdjust
		);
		if ($calendar_data['weeks'][$nRow]['number'] == 53 && $nShift != 4)
			$calendar_data['weeks'][$nRow]['number'] = 1;

		for ($nCol = 0; $nCol < 7; $nCol++)
		{
			$nDay = ($nRow * 7) + $nCol - $nShift + 1;

			if ($nDay < 1 || $nDay > $month_info['last_day']['day_of_month'])
				$nDay = 0;

			$date = sprintf('%04d-%02d-%02d', $curPage['year'], $curPage['month'], $nDay);

			$calendar_data['weeks'][$nRow]['days'][$nCol] = array(
				'day' => $nDay,
				'date' => $date,
				'is_today' => $date == $today['date'],
				'is_first_day' => !empty($calendarOptions['show_week_num']) && (($month_info['first_day']['day_of_week'] + $nDay - 1) % 7 == $calendarOptions['start_day']),
				'holidays' => !empty($holidays[$date]) ? $holidays[$date] : array(),
				'events' => !empty($events[$date]) ? $events[$date] : array(),
				'birthdays' => !empty($bday[$date]) ? $bday[$date] : array()
			);
		}
	}

	echo '
								<table class="sp_acalendar smalltext">
									<tr>
										<td class="sp_center smalltext" colspan="7">
											', !empty($modSettings['cal_enabled']) ? '<a href="' . $scripturl . '?action=calendar;year=' . $calendar_data['current_year'] . ';month=' . $calendar_data['current_month'] . '">' . $txt['months_titles'][$calendar_data['current_month']] . ' ' . $calendar_data['current_year'] . '</a>' : $txt['months_titles'][$calendar_data['current_month']] . ' ' . $calendar_data['current_year'], '
										</td>
									</tr><tr>';

	foreach ($calendar_data['week_days'] as $day)
		echo '
										<td class="sp_center smalltext">', $txt['days_short'][$day], '</td>';

	echo '
									</tr>';

	foreach ($calendar_data['weeks'] as $week_key => $week)
	{
		echo '<tr>';

		foreach ($week['days'] as $day_key => $day)
		{
			echo '
										<td class="sp_acalendar_day smalltext">';

			if (empty($day['day']))
				unset($calendar_data['weeks'][$week_key]['days'][$day_key]);
			else
			{
				if (!empty($day['holidays']) || !empty($day['birthdays']) || !empty($day['events']))
					echo '<a href="#day" onclick="return sp_collapseCalendar(\'', $day['day'], '\');"><strong>', $day['is_today'] ? '[' : '', $day['day'], $day['is_today'] ? ']' : '', '</strong></a>';
				else
					echo '<a href="#day" onclick="return sp_collapseCalendar(\'0\');">', $day['is_today'] ? '[' : '', $day['day'], $day['is_today'] ? ']' : '', '</a>';
			}

			echo '</td>';
		}

		echo '
									</tr>';
	}

	echo '
								</table>
								<hr class="sp_acalendar_divider" />';

	foreach ($calendar_data['weeks'] as $week)
	{
		foreach ($week['days'] as $day)
		{
			if (empty($day['holidays']) && empty($day['birthdays']) && empty($day['events']) && !$day['is_today'])
				continue;
			elseif (empty($day['holidays']) && empty($day['birthdays']) && empty($day['events']))
			{
				echo '
								<div class="sp_center smalltext" id="sp_calendar_', $day['day'], '">', $txt['error_sp_no_items_day'], '</div>';

				continue;
			}

			echo '
								<ul class="sp_list smalltext" id="sp_calendar_', $day['day'], '" ', !$day['is_today'] ? ' style="display: none;"' : '', '>';

		if (!empty($day['holidays']))
		{
				echo '
									<li class="sp_center"><strong>- ', $txt['sp_calendar_holidays'] ,' -</strong></li>';

			foreach ($day['holidays'] as $key => $holiday)
				echo '
									<li class="sp_list_indent">', sp_embed_image('holiday'), ' ', $holiday ,'</li>';
		}

		if (!empty($day['birthdays']))
		{
				echo '
									<li class="sp_center"><strong>- ', $txt['sp_calendar_birthdays'] ,' -</strong></li>';

			foreach ($day['birthdays'] as $member)
				echo '
									<li class="sp_list_indent">', sp_embed_image('birthday'), ' <a href="', $scripturl, '?action=profile;u=', $member['id'], '">', $member['name'], isset($member['age']) ? ' (' . $member['age'] . ')' : '', '</a></li>';
		}

		if (!empty($day['events']))
		{
				echo '
									<li class="sp_center"><strong>- ', $txt['sp_calendar_events'] ,' -</strong></li>';

			foreach ($day['events'] as $event)
				echo '
									<li class="sp_list_indent">', sp_embed_image('event'), ' ', $event['link'], '</li>';
		}

		echo '
								</ul>';
		}
	}

	echo '
								<div class="sp_center smalltext" id="sp_calendar_0" style="display: none;">', $txt['error_sp_no_items_day'], '</div>
								<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
									var current_day = "sp_calendar_', $curPage['day'], '";
									function sp_collapseCalendar(id)
									{
										new_day = "sp_calendar_" + id;
										if (new_day == current_day)
											return false;
										document.getElementById(current_day).style.display = "none";
										document.getElementById(new_day).style.display = "";
										current_day = new_day;
									}
								// ]]></script>';
}

function sp_calendarInformation($parameters, $id, $return_parameters = false)
{
	global $scripturl, $modSettings, $txt;

	$block_parameters = array(
		'events' => 'check',
		'future' => 'int',
		'birthdays' => 'check',
		'holidays' => 'check',
	);

	if ($return_parameters)
		return $block_parameters;

	$show_event = !empty($parameters['events']);
	$event_future = !empty($parameters['future']) ? (int) $parameters['future'] : 0;
	$event_future = abs($event_future);
	$show_birthday = !empty($parameters['birthdays']);
	$show_holiday = !empty($parameters['holidays']);
	$show_titles = false;

	if (!$show_event && !$show_birthday && !$show_holiday)
	{
		echo '
								', $txt['sp_calendar_noEventsFound'];
		return;
	}

	$now = forum_time();
	$today_date = date("Y-m-d", $now);
	$calendar_array = array(
		'todayEvents' => array(),
		'futureEvents' => array(),
		'todayBirthdays' => array(),
		'todayHolidays' => array()
	);

	if ($show_event)
	{
		if (!empty($event_future))
			$event_future_date = date("Y-m-d", ($now + $event_future * 86400));
		else
			$event_future_date = $today_date;

		$events = sp_loadCalendarData('getEvents', $today_date, $event_future_date);

		ksort($events);

		$displayed = array();
		foreach ($events as $day => $day_events)
			foreach ($day_events as $event_key => $event)
				if (in_array($event['id'], $displayed))
					unset($events[$day][$event_key]);
				else
					$displayed[] = $event['id'];

		if (!empty($events[$today_date]))
		{
			$calendar_array['todayEvents'] = $events[$today_date];
			unset($events[$today_date]);
		}

		if (!empty($events))
		{
			ksort($events);
			$calendar_array['futureEvents'] = $events;
		}
	}

	if ($show_birthday)
	{
		$calendar_array['todayBirthdays'] = current(sp_loadCalendarData('getBirthdays', $today_date));
		$show_titles = !empty($show_event) || !empty($show_holiday);
	}

	if ($show_holiday)
	{
		$calendar_array['todayHolidays'] = current(sp_loadCalendarData('getHolidays', $today_date));
		$show_titles = !empty($show_event) || !empty($show_birthday);
	}

	if (empty($calendar_array['todayEvents']) && empty($calendar_array['futureEvents']) && empty($calendar_array['todayBirthdays']) && empty($calendar_array['todayHolidays']))
	{
		echo '
								', $txt['sp_calendar_noEventsFound'];
		return;
	}
	else
	{
		echo '
								<ul class="sp_list">';

		if (!empty($calendar_array['todayHolidays']))
		{
			if ($show_titles)
				echo '
									<li><strong>', $txt['sp_calendar_holidays'] ,'</strong></li>';

			foreach ($calendar_array['todayHolidays'] as $key => $holiday)
				echo '
									<li>', sp_embed_image('holiday'), ' ', $holiday ,'</li>';
		}

		if (!empty($calendar_array['todayBirthdays']))
		{
			if ($show_titles)
				echo '
									<li><strong>', $txt['sp_calendar_birthdays'] ,'</strong></li>';

			foreach ($calendar_array['todayBirthdays'] as $member)
				echo '
									<li>', sp_embed_image('birthday'), ' <a href="', $scripturl, '?action=profile;u=', $member['id'], '">', $member['name'], isset($member['age']) ? ' (' . $member['age'] . ')' : '', '</a></li>';
		}

		if (!empty($calendar_array['todayEvents']))
		{
			if ($show_titles)
				echo '
									<li><strong>', $txt['sp_calendar_events'] ,'</strong></li>';

			foreach ($calendar_array['todayEvents'] as $event)
				echo '
									<li>', sp_embed_image('event'), ' ', $event['link'], !$show_titles ? ' - ' . timeformat(forum_time(), '%d %b') : '', '</li>';
		}

		if (!empty($calendar_array['futureEvents']))
		{
			if ($show_titles)
				echo '
									<li><strong>', $txt['sp_calendar_upcomingEvents'] ,'</strong></li>';

			foreach ($calendar_array['futureEvents'] as $startdate => $events)
			{
				foreach ($events as $event)
					echo '
									<li>', sp_embed_image('event'), ' ', $event['link'], ' - ', timeformat(strtotime($startdate), '%d %b'), '</li>';
			}
		}

		echo '
								</ul>';
	}
}

function sp_rssFeed($parameters, $id, $return_parameters = false)
{
	global $func, $sourcedir, $context, $txt;

	$block_parameters = array(
		'url' => 'text',
		'show_title' => 'check',
		'show_content' => 'check',
		'show_date' => 'check',
		'strip_preserve' => 'text',
		'count' => 'int',
		'limit' => 'int',
	);

	if ($return_parameters)
		return $block_parameters;

	$feed = !empty($parameters['url']) ? un_htmlspecialchars($parameters['url']) : '';
	$show_title = !empty($parameters['show_title']);
	$show_content = !empty($parameters['show_content']);
	$show_date = !empty($parameters['show_date']);
	$strip_preserve = !empty($parameters['strip_preserve']) ? $parameters['strip_preserve'] : 'br';
	$strip_preserve = preg_match_all('~[A-Za-z0-9]+~', $strip_preserve, $match) ? $match[0] : array();
	$count = !empty($parameters['count']) ? (int) $parameters['count'] : 5;
	$limit = !empty($parameters['limit']) ? (int) $parameters['limit'] : 0;

	if (empty($feed))
	{
		echo '
								', $txt['error_sp_invalid_feed'];
		return;
	}

	$rss = array();

	require_once($sourcedir . '/Subs-Package.php');
	$data = fetch_web_data($feed);

	if (function_exists('mb_convert_encoding'))
	{
		preg_match('~encoding="([^"]*)"~', $data, $charset);

		if (!empty($charset[1]) && $charset != $context['character_set'])
			$data = mb_convert_encoding($data, $context['character_set'], $charset[1]);
	}
	elseif (function_exists('iconv'))
	{
		preg_match('~encoding="([^"]*)"~', $data, $charset);

		if (!empty($charset[1]) && $charset != $context['character_set'])
			$data = iconv($charset[1], $context['character_set'], $data);
	}

	$data = str_replace(array("\n", "\r", "\t"), '', $data);
	$data = preg_replace('~<\!\[CDATA\[(.+?)\]\]>~e' . ($context['utf8'] ? 'u' : ''), '\'#cdata_escape_encode#\' . $func[\'htmlspecialchars\'](\'$1\')', $data);

	preg_match_all('~<item>(.+?)</item>~', $data, $items);

	foreach ($items[1] as $item_id => $item)
	{
		if ($item_id === $count)
			break;

		preg_match_all('~<([A-Za-z]+)>(.+?)</\\1>~', $item, $match);

		foreach ($match[0] as $tag_id => $dummy)
		{
			if ($func['strpos']($match[2][$tag_id], '#cdata_escape_encode#') === 0)
				$match[2][$tag_id] = stripslashes(un_htmlspecialchars($func['substr']($match[2][$tag_id], 21)));

			$rss[$item_id][strtolower($match[1][$tag_id])] = un_htmlspecialchars($match[2][$tag_id]);
		}
	}

	if (empty($rss))
	{
		echo '
								', $txt['error_sp_invalid_feed'];
		return;
	}

	$items = array();
	foreach ($rss as $item)
	{
		$item['title'] = isset($item['title']) ? strip_tags($item['title']) : '';
		$item['description'] = isset($item['description']) ? strip_tags($item['description'], empty($strip_preserve) ? '' : '<' . implode('><', $strip_preserve) . '>') : '';

		$items[] = array(
			'title' => $item['title'],
			'href' => $item['link'],
			'link' => $item['title'] == '' ? '' : ($item['link'] == '' ? $item['title'] : '<a href="' . $item['link'] . '" target="_blank" class="new_win">' . $item['title'] . '</a>'),
			'content' => $func['strlen']($item['description']) > $limit ? $func['substr']($item['description'], 0, $limit) . '...' : $item['description'],
			'date' => !empty($item['pubdate']) ? timeformat(strtotime($item['pubdate']), '%d %B') : '',
		);
	}

	if (empty($items))
	{
		echo '
								', $txt['error_sp_invalid_feed'];
		return;
	}
	else
		$items[count($items) - 1]['is_last'] = true;

	if ($show_content)
	{
		echo '
								<div class="sp_rss_flow">
									<ul class="sp_list">';

		foreach ($items as $item)
		{
			if ($show_title && !empty($item['link']))
			echo '
										<li class="sp_list_top">', sp_embed_image('post'), ' <strong>', $item['link'], '</strong>', ($show_date && !empty($item['date']) ? ' - ' . $item['date'] : ''), '</li>';
			echo '
										<li', empty($item['is_last']) ? ' class="sp_list_divider"' : '', '>', $item['content'], '</li>';
		}

		echo '
									</ul>
								</div>';
	}
	else
	{
		echo '
								<ul class="sp_list">';

		foreach ($items as $item)
			echo '
									<li>', sp_embed_image('dot_feed'), ' ', $item['link'], ($show_date && !empty($item['date']) ? ' - ' . $item['date'] : ''), '</li>';

		echo '
								</ul>';
	}
}

function sp_theme_select($parameters, $id, $return_parameters = false)
{
	global $db_prefix, $func, $context, $modSettings, $user_info, $settings, $language, $txt;

	$block_parameters = array();

	if ($return_parameters)
		return $block_parameters;

	loadLanguage('Profile');
	loadLanguage('Themes');

	if (!empty($_SESSION['ID_THEME']) && (!empty($modSettings['theme_allow']) || allowedTo('admin_forum')))
		$current_theme = (int) $_SESSION['ID_THEME'];
	else
		$current_theme = $user_info['theme'];

	$current_theme = empty($current_theme) ? -1 : $current_theme;
	$available_themes = array();
	if (!empty($modSettings['knownThemes']))
	{
		$knownThemes = implode("', '", explode(',', $modSettings['knownThemes']));

		$request = db_query("
			SELECT ID_THEME, variable, value
			FROM {$db_prefix}themes
			WHERE variable IN ('name', 'theme_url', 'theme_dir', 'images_url')
				AND ID_THEME IN ('$knownThemes')
				AND ID_THEME != 0
			LIMIT " . count(explode(',', $modSettings['knownThemes'])) * 8, __FILE__, __LINE__);
		while ($row = mysql_fetch_assoc($request))
		{
			if (!isset($available_themes[$row['ID_THEME']]))
				$available_themes[$row['ID_THEME']] = array(
					'id' => $row['ID_THEME'],
					'selected' => $current_theme == $row['ID_THEME'],
					'num_users' => 0
				);
			$available_themes[$row['ID_THEME']][$row['variable']] = $row['value'];
		}
		mysql_free_result($request);
	}

	if (!isset($available_themes[$modSettings['theme_guests']]))
		$guest_theme = 0;
	else
		$guest_theme = $modSettings['theme_guests'];

	$current_settings = $settings;

	foreach ($available_themes as $ID_THEME => $theme_data)
	{
		if ($ID_THEME == 0)
			continue;

		$settings = $theme_data;
		$settings['theme_id'] = $ID_THEME;

		if (file_exists($settings['theme_dir'] . '/languages/Settings.' . $user_info['language'] . '.php'))
			include($settings['theme_dir'] . '/languages/Settings.' . $user_info['language'] . '.php');
		elseif (file_exists($settings['theme_dir'] . '/languages/Settings.' . $language . '.php'))
			include($settings['theme_dir'] . '/languages/Settings.' . $language . '.php');
		else
		{
			$txt['theme_thumbnail_href'] = $settings['images_url'] . '/thumbnail.gif';
			$txt['theme_description'] = '';
		}

		$available_themes[$ID_THEME]['thumbnail_href'] = $txt['theme_thumbnail_href'];
		$available_themes[$ID_THEME]['description'] = $txt['theme_description'];

		$available_themes[$ID_THEME]['name'] = preg_replace('~\stheme$~i', '', $theme_data['name']);
		if ($func['strlen']($available_themes[$ID_THEME]['name']) > 18)
			$available_themes[$ID_THEME]['name'] = $func['substr']($available_themes[$ID_THEME]['name'], 0, 18) . '...';
	}

	$settings = $current_settings;

	if ($guest_theme != 0)
		$available_themes[-1] = $available_themes[$guest_theme];

	$available_themes[-1]['id'] = -1;
	$available_themes[-1]['name'] = $txt['theme_forum_default'];
	$available_themes[-1]['selected'] = $current_theme == 0;
	$available_themes[-1]['description'] = $txt['theme_global_description'];

	ksort($available_themes);

	// Validate the selected theme id.
	if (!array_key_exists($current_theme, $available_themes))
	{
		$current_theme = -1;
		$available_themes[-1]['selected'] = true;
	}

	if (!empty($_POST['sp_ts_submit']) && !empty($_POST['sp_ts_permanent']) && !empty($_POST['theme']) && isset($available_themes[$_POST['theme']]) && (!empty($modSettings['theme_allow']) || allowedTo('admin_forum')))
		updateMemberData($context['user']['id'], array('ID_THEME' => $_POST['theme'] == -1 ? 0 : (int) $_POST['theme']));

	echo '
								<form method="post" action="" accept-charset="', $context['character_set'], '">
									<div class="sp_center">
										<select name="theme" onchange="sp_theme_select(this)">';

	foreach ($available_themes as $theme)
		echo '
											<option value="', $theme['id'], '"', $theme['selected'] ? ' selected="selected"' : '', '>', $theme['name'], '</option>';

	echo '
										</select>
										<br /><br />
										<img src="', $available_themes[$current_theme]['thumbnail_href'], '" alt="', $available_themes[$current_theme]['name'], '" id="sp_ts_thumb" />
										<br /><br />
										<input type="checkbox" name="sp_ts_permanent" value="1" /> ', $txt['sp-theme_permanent'], '
										<br />
										<input type="submit" name="sp_ts_submit" value="', $txt['sp-theme_change'], '" />
									</div>
								</form>
								<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
									var sp_ts_thumbs = new Array();';

	foreach ($available_themes as $id => $theme_data)
		echo '
									sp_ts_thumbs[', $id, '] = "', $theme_data['thumbnail_href'], '";';

		echo '
									function sp_theme_select(obj)
									{
										var id = obj.options[obj.selectedIndex].value;
										document.getElementById("sp_ts_thumb").src = sp_ts_thumbs[id];
									}
								// ]]></script>';
}

function sp_staff($parameters, $id, $return_parameters = false)
{
	global $db_prefix, $sourcedir, $scripturl, $modSettings, $color_profile;

	$block_parameters = array(
		'lmod' => 'check',
	);

	if ($return_parameters)
		return $block_parameters;

	require_once($sourcedir . '/Subs-Members.php');

	if (empty($parameters['lmod']))
	{
		$request = db_query("
			SELECT ID_BOARD, ID_MEMBER
			FROM {$db_prefix}moderators", __FILE__, __LINE__);
		$local_mods = array();
		while ($row = mysql_fetch_assoc($request))
			$local_mods[$row['ID_MEMBER']] = $row['ID_MEMBER'];
		mysql_free_result($request);

		if (count($local_mods) > 10)
			$local_mods = array();
	}
	else
		$local_mods = array();

	$global_mods = membersAllowedTo('moderate_board', 0);
	$admins = membersAllowedTo('admin_forum');

	$all_staff = array_merge($local_mods, $global_mods, $admins);
	$all_staff = array_unique($all_staff);

	$request = db_query("
		SELECT
			m.ID_MEMBER, m.realName, m.avatar, mg.groupName,
			a.ID_ATTACH, a.attachmentType, a.filename
		FROM {$db_prefix}members as m
				LEFT JOIN {$db_prefix}attachments AS a ON (a.ID_MEMBER = m.ID_MEMBER)
				LEFT JOIN {$db_prefix}membergroups AS mg ON (mg.ID_GROUP = CASE WHEN m.ID_GROUP = 0 THEN m.ID_POST_GROUP ELSE m.ID_GROUP END)
		WHERE m.ID_MEMBER IN (" . implode(',', $all_staff) . ")", __FILE__, __LINE__);
	$staff_list = array();
	$colorids = array();
	while ($row = mysql_fetch_assoc($request))
	{
		$colorids[$row['ID_MEMBER']] = $row['ID_MEMBER'];

		if (stristr($row['avatar'], 'http://') && !empty($modSettings['avatar_check_size']))
		{
			$sizes = url_image_size($row['avatar']);

			if ($modSettings['avatar_action_too_large'] == 'option_refuse' && is_array($sizes) && (($sizes[0] > $modSettings['avatar_max_width_external'] && !empty($modSettings['avatar_max_width_external'])) || ($sizes[1] > $modSettings['avatar_max_height_external'] && !empty($modSettings['avatar_max_height_external']))))
			{
				$row['avatar'] = '';
				updateMemberData($row['ID_MEMBER'], array('avatar' => '\'\''));
			}
		}

		if (in_array($row['ID_MEMBER'], $admins))
			$row['type'] = 1;
		elseif (in_array($row['ID_MEMBER'], $global_mods))
			$row['type'] = 2;
		else
			$row['type'] = 3;

		$staff_list[$row['type'] . '-' . $row['ID_MEMBER']] = array(
			'id' => $row['ID_MEMBER'],
			'name' => $row['realName'],
			'link' => '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '">' . $row['realName'] . '</a>',
			'group' => $row['groupName'],
			'type' => $row['type'],
			'avatar' => array(
				'name' => $row['avatar'],
				'image' => $row['avatar'] == '' ? ($row['ID_ATTACH'] > 0 ? '<img src="' . (empty($row['attachmentType']) ? $scripturl . '?action=dlattach;attach=' . $row['ID_ATTACH'] . ';type=avatar' : $modSettings['custom_avatar_url'] . '/' . $row['filename']) . '" alt="" class="avatar" border="0" />' : '') : (stristr($row['avatar'], 'http://') ? '<img src="' . $row['avatar'] . '" alt="" class="avatar" border="0" />' : '<img src="' . $modSettings['avatar_url'] . '/' . htmlspecialchars($row['avatar']) . '" alt="" class="avatar" border="0" />'),
				'href' => $row['avatar'] == '' ? ($row['ID_ATTACH'] > 0 ? (empty($row['attachmentType']) ? $scripturl . '?action=dlattach;attach=' . $row['ID_ATTACH'] . ';type=avatar' : $modSettings['custom_avatar_url'] . '/' . $row['filename']) : '') : (stristr($row['avatar'], 'http://') ? $row['avatar'] : $modSettings['avatar_url'] . '/' . $row['avatar']),
				'url' => $row['avatar'] == '' ? '' : (stristr($row['avatar'], 'http://') ? $row['avatar'] : $modSettings['avatar_url'] . '/' . $row['avatar']),
			),
		);
	}
	mysql_free_result($request);

	ksort($staff_list);
	$icons = array(1 => 'admin', 'gmod', 'lmod');

	if (!empty($colorids) && sp_loadColors($colorids) !== false)
	{
		foreach ($staff_list as $k => $p)
		{
			if (!empty($color_profile[$p['id']]['link']))
				$staff_list[$k]['link'] = $color_profile[$p['id']]['link'];
		}
	}

	echo '
								<table class="sp_fullwidth">';

	foreach ($staff_list as $staff)
		echo '
									<tr>
										<td class="sp_staff sp_center">', !empty($staff['avatar']['href']) ? '
											<a href="' . $scripturl . '?action=profile;u=' . $staff['id'] . '"><img src="' . $staff['avatar']['href'] . '" alt="' . $staff['name'] . '" width="40" /></a>' : '', '
										</td>
										<td class="sp_staff_info">
											', sp_embed_image($icons[$staff['type']]), ' ', $staff['link'], '<br />
											', $staff['group'], '
										</td>
									</tr>';

	echo '
								</table>';
}

function sp_articles($parameters, $id, $return_parameters = false)
{
	global $db_prefix, $sourcedir, $modSettings, $scripturl, $user_info, $txt, $color_profile;

	$block_parameters = array(
		'category' => array(0 => $txt['sp_all']),
		'limit' => 'int',
		'type' => 'select',
		'image' => 'select',
	);

	if ($return_parameters)
	{
		require_once($sourcedir . '/Subs-PortalAdmin.php');

		$categories = getCategoryInfo();
		foreach ($categories as $category)
			$block_parameters['category'][$category['id']] = $category['name'];

		return $block_parameters;
	}

	$category = empty($parameters['category']) ? 0 : (int) $parameters['category'];
	$limit = empty($parameters['limit']) ? 5 : (int) $parameters['limit'];
	$type = empty($parameters['type']) ? 0 : 1;
	$image = empty($parameters['image']) ? 0 : (int) $parameters['image'];

	$request = db_query("
		SELECT
			m.ID_TOPIC, m.subject, m.posterName, c.picture, c.name,
			mem.ID_MEMBER, mem.realName, mem.avatar,
			at.ID_ATTACH, at.attachmentType, at.filename
		FROM {$db_prefix}sp_articles AS a
			INNER JOIN {$db_prefix}sp_categories AS c ON (c.id_category = a.id_category)
			INNER JOIN {$db_prefix}messages AS m ON (m.ID_MSG = a.id_message)
			INNER JOIN {$db_prefix}boards AS b ON (b.ID_BOARD = m.ID_BOARD)
			LEFT JOIN {$db_prefix}members AS mem ON (mem.ID_MEMBER = m.ID_MEMBER)
			LEFT JOIN {$db_prefix}attachments AS at ON (at.ID_MEMBER = mem.ID_MEMBER)
		WHERE $user_info[query_see_board]
			AND a.approved = 1" . (!empty($category) ? "
			AND a.ID_CATEGORY = " . $category : "") . "
		ORDER BY " . ($type ? 'RAND()' : 'm.posterTime DESC') . "
		LIMIT $limit", __FILE__, __LINE__);
	$articles = array();
	$colorids = array();
	while ($row = mysql_fetch_assoc($request))
	{
		if (!empty($row['ID_MEMBER']))
			$colorids[$row['ID_MEMBER']] = $row['ID_MEMBER'];

		if (stristr($row['avatar'], 'http://') && !empty($modSettings['avatar_check_size']))
		{
			$sizes = url_image_size($row['avatar']);

			if ($modSettings['avatar_action_too_large'] == 'option_refuse' && is_array($sizes) && (($sizes[0] > $modSettings['avatar_max_width_external'] && !empty($modSettings['avatar_max_width_external'])) || ($sizes[1] > $modSettings['avatar_max_height_external'] && !empty($modSettings['avatar_max_height_external']))))
			{
				$row['avatar'] = '';
				updateMemberData($row['ID_MEMBER'], array('avatar' => '\'\''));
			}
		}

		$articles[] = array(
			'id' => $row['ID_TOPIC'],
			'name' => $row['subject'],
			'href' => $scripturl . '?topic=' . $row['ID_TOPIC'] . '.0',
			'link' => '<a href="' . $scripturl . '?topic=' . $row['ID_TOPIC'] . '.0">' . $row['subject'] . '</a>',
			'poster' => array(
				'id' => $row['ID_MEMBER'],
				'name' => $row['realName'],
				'href' => $scripturl . '?action=profile;u=' . $row['ID_MEMBER'],
				'link' => empty($row['ID_MEMBER']) ? $row['posterName'] : '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '">' . $row['realName'] . '</a>',
			),
			'image' => array(
				'href' => $row['picture'],
				'image' => '<img src="' . $row['picture'] . '" alt="' . $row['name'] . '" />',
			),
			'avatar' => array(
				'name' => $row['avatar'],
				'image' => $row['avatar'] == '' ? ($row['ID_ATTACH'] > 0 ? '<img src="' . (empty($row['attachmentType']) ? $scripturl . '?action=dlattach;attach=' . $row['ID_ATTACH'] . ';type=avatar' : $modSettings['custom_avatar_url'] . '/' . $row['filename']) . '" alt="" class="avatar" border="0" />' : '') : (stristr($row['avatar'], 'http://') ? '<img src="' . $row['avatar'] . '" alt="" class="avatar" border="0" />' : '<img src="' . $modSettings['avatar_url'] . '/' . htmlspecialchars($row['avatar']) . '" alt="" class="avatar" border="0" />'),
				'href' => $row['avatar'] == '' ? ($row['ID_ATTACH'] > 0 ? (empty($row['attachmentType']) ? $scripturl . '?action=dlattach;attach=' . $row['ID_ATTACH'] . ';type=avatar' : $modSettings['custom_avatar_url'] . '/' . $row['filename']) : '') : (stristr($row['avatar'], 'http://') ? $row['avatar'] : $modSettings['avatar_url'] . '/' . $row['avatar']),
				'url' => $row['avatar'] == '' ? '' : (stristr($row['avatar'], 'http://') ? $row['avatar'] : $modSettings['avatar_url'] . '/' . $row['avatar']),
			),
		);
	}
	mysql_free_result($request);

	if (empty($articles))
	{
		echo '
								', $txt['error_sp_no_articles_found'];
		return;
	}

	if (!empty($colorids) && sp_loadColors($colorids) !== false)
	{
		foreach ($articles as $k => $p)
		{
			if (!empty($color_profile[$p['poster']['id']]['link']))
				$articles[$k]['poster']['link'] = $color_profile[$p['poster']['id']]['link'];
		}
	}

	if (empty($image))
	{
		echo '
								<ul class="sp_list">';

		foreach ($articles as $article)
			echo '
									<li>', sp_embed_image('topic'), ' ', $article['link'], '</li>';

		echo '
								</ul>';
	}
	else
	{
		echo '
								<table class="sp_fullwidth sp_articles">';

		foreach ($articles as $article)
		{
			echo '
									<tr>
										<td class="sp_articles sp_center">';

			if (!empty($article['avatar']['href']) && $image == 1)
				echo '<a href="', $scripturl, '?action=profile;u=', $article['poster']['id'], '"><img src="', $article['avatar']['href'], '" alt="', $article['poster']['name'], '" width="40" /></a>';
			elseif (!empty($article['image']['href']) && $image == 2)
				echo '<img src="', $article['image']['href'], '" alt="', $article['name'], '" width="40" />';

			echo '</td>
										<td>
											<span class="sp_articles_title">', $article['poster']['link'], '</span><br />
											', $article['link'], '
										</td>
									</tr>';
		}

		echo '
								</table>';
	}
}

function sp_shoutbox($parameters, $id, $return_parameters = false)
{
	global $db_prefix, $context, $sourcedir, $modSettings, $user_info, $settings, $txt, $func, $scripturl;

	$block_parameters = array(
		'shoutbox' => array(),
	);

	if ($return_parameters)
	{
		$shoutboxes = sportal_get_shoutbox();
		$in_use = array();

		$request = db_query("
			SELECT ID_BLOCK, value
			FROM {$db_prefix}sp_parameters
			WHERE variable = 'shoutbox'", __FILE__, __LINE__);
		while ($row = mysql_fetch_assoc($request))
			if (empty($_REQUEST['block_id']) || $_REQUEST['block_id'] != $row['ID_BLOCK'])
				$in_use[] = $row['value'];
		mysql_free_result($request);

		foreach ($shoutboxes as $shoutbox)
			if (!in_array($shoutbox['id'], $in_use))
				$block_parameters['shoutbox'][$shoutbox['id']] = $shoutbox['name'];

		if (empty($block_parameters['shoutbox']))
			fatal_error(allowedTo(array('sp_admin', 'sp_manage_shoutbox')) ? $txt['error_sp_no_shoutbox'] . '<br />' . sprintf($txt['error_sp_no_shoutbox_sp_moderator'], $scripturl . '?action=manageportal;area=portalshoutbox;sa=add') : $txt['error_sp_no_shoutbox_normaluser'], false);

		return $block_parameters;
	}

	loadTemplate('PortalShoutbox');
	loadLanguage('Post');

	$shoutbox = sportal_get_shoutbox($parameters['shoutbox'], true, true);

	if (empty($shoutbox))
	{
		echo '
								', $txt['error_sp_shoutbox_not_exist'];
		return;
	}

	if (!empty($_POST['new_shout']) && !empty($_POST['submit_shout']) && !empty($_POST['shoutbox_id']) && $_POST['shoutbox_id'] == $shoutbox['id'])
	{
		checkSession();

		is_not_guest();

		if (!($flood = sp_prevent_flood('spsbp', false)))
		{
			require_once($sourcedir . '/Subs-Post.php');

			$_POST['new_shout'] = trim(addslashes($func['htmlspecialchars'](stripslashes($_POST['new_shout']), ENT_QUOTES)));
			preparsecode($_POST['new_shout']);

			if (!empty($_POST['new_shout']))
				sportal_create_shout($shoutbox, $_POST['new_shout']);
		}
		else
			$shoutbox['warning'] = $flood;
	}

	$can_moderate = allowedTo('sp_admin') || allowedTo('sp_manage_shoutbox');
	if (!$can_moderate && !empty($shoutbox['moderator_groups']))
		$can_moderate = count(array_intersect($user_info['groups'], $shoutbox['moderator_groups'])) > 0;

	$shout_parameters = array(
		'limit' => $shoutbox['num_show'],
		'bbc' => $shoutbox['allowed_bbc'],
		'reverse' => $shoutbox['reverse'],
		'cache' => $shoutbox['caching'],
		'can_moderate' => $can_moderate,
	);
	$shoutbox['shouts'] = sportal_get_shouts($shoutbox['id'], $shout_parameters);

	$shoutbox['warning'] = parse_bbc($shoutbox['warning']);
	$context['can_shout'] = $context['user']['is_logged'];

	if ($context['can_shout'])
	{
		$settings['smileys_url'] = $modSettings['smileys_url'] . '/' . $user_info['smiley_set'];
		$shoutbox['smileys'] = array('normal' => array(), 'popup' => array());
		if (empty($modSettings['smiley_enable']))
			$shoutbox['smileys']['normal'] = array(
				array('code' => ':)', 'filename' => 'smiley.gif', 'description' => $txt[287]),
				array('code' => ';)', 'filename' => 'wink.gif', 'description' => $txt[292]),
				array('code' => ':D', 'filename' => 'cheesy.gif', 'description' => $txt[289]),
				array('code' => ';D', 'filename' => 'grin.gif', 'description' => $txt[293]),
				array('code' => '>:(', 'filename' => 'angry.gif', 'description' => $txt[288]),
				array('code' => ':(', 'filename' => 'sad.gif', 'description' => $txt[291]),
				array('code' => ':o', 'filename' => 'shocked.gif', 'description' => $txt[294]),
				array('code' => '8)', 'filename' => 'cool.gif', 'description' => $txt[295]),
				array('code' => '???', 'filename' => 'huh.gif', 'description' => $txt[296]),
				array('code' => '::)', 'filename' => 'rolleyes.gif', 'description' => $txt[450]),
				array('code' => ':P', 'filename' => 'tongue.gif', 'description' => $txt[451]),
				array('code' => ':-[', 'filename' => 'embarrassed.gif', 'description' => $txt[526]),
				array('code' => ':-X', 'filename' => 'lipsrsealed.gif', 'description' => $txt[527]),
				array('code' => ':-\\', 'filename' => 'undecided.gif', 'description' => $txt[528]),
				array('code' => ':-*', 'filename' => 'kiss.gif', 'description' => $txt[529]),
				array('code' => ':\'(', 'filename' => 'cry.gif', 'description' => $txt[530])
			);
		else
		{
			if (($temp = cache_get_data('shoutbox_smileys', 3600)) == null)
			{
				$request = db_query("
					SELECT code, filename, description, smileyRow, hidden
					FROM {$db_prefix}smileys
					WHERE hidden IN (0, 2)
					ORDER BY smileyRow, smileyOrder", __FILE__, __LINE__);
				while ($row = mysql_fetch_assoc($request))
				{
					$row['filename'] = htmlspecialchars($row['filename']);
					$row['description'] = htmlspecialchars($row['description']);
					$row['code'] = htmlspecialchars($row['code']);
					$shoutbox['smileys'][empty($row['hidden']) ? 'normal' : 'popup'][] = $row;
				}
				mysql_free_result($request);

				cache_put_data('shoutbox_smileys', $shoutbox['smileys'], 3600);
			}
			else
				$shoutbox['smileys'] = $temp;
		}

		foreach (array_keys($shoutbox['smileys']) as $location)
		{
			$n = count($shoutbox['smileys'][$location]);
			for ($i = 0; $i < $n; $i++)
			{
				$shoutbox['smileys'][$location][$i]['code'] = addslashes($shoutbox['smileys'][$location][$i]['code']);
				$shoutbox['smileys'][$location][$i]['js_code'] = addslashes($shoutbox['smileys'][$location][$i]['code']);
				$shoutbox['smileys'][$location][$i]['js_description'] = addslashes($shoutbox['smileys'][$location][$i]['description']);
			}

			if (!empty($shoutbox['smileys'][$location]))
				$shoutbox['smileys'][$location][$n - 1]['last'] = true;
		}

		$shoutbox['bbc'] = array(
			'bold' => array('code' => 'b', 'before' => '[b]', 'after' => '[/b]', 'description' => $txt[253]),
			'italicize' => array('code' => 'i', 'before' => '[i]', 'after' => '[/i]', 'description' => $txt[254]),
			'underline' => array('code' => 'u', 'before' => '[u]', 'after' => '[/u]', 'description' => $txt[255]),
			'strike' => array('code' => 's', 'before' => '[s]', 'after' => '[/s]', 'description' => $txt[441]),
			'pre' => array('code' => 'pre', 'before' => '[pre]', 'after' => '[/pre]', 'description' => $txt[444]),
			'flash' => array('code' => 'flash', 'before' => '[flash=200,200]', 'after' => '[/flash]', 'description' => $txt[433]),
			'img' => array('code' => 'img', 'before' => '[img]', 'after' => '[/img]', 'description' => $txt[435]),
			'url' => array('code' => 'url', 'before' => '[url]', 'after' => '[/url]', 'description' => $txt[257]),
			'email' => array('code' => 'email', 'before' => '[email]', 'after' => '[/email]', 'description' => $txt[258]),
			'ftp' => array('code' => 'ftp', 'before' => '[ftp]', 'after' => '[/ftp]', 'description' => $txt[434]),
			'glow' => array('code' => 'glow', 'before' => '[glow=red,2,300]', 'after' => '[/glow]', 'description' => $txt[442]),
			'shadow' => array('code' => 'shadow', 'before' => '[shadow=red,left]', 'after' => '[/shadow]', 'description' => $txt[443]),
			'sup' => array('code' => 'sup', 'before' => '[sup]', 'after' => '[/sup]', 'description' => $txt[447]),
			'sub' => array('code' => 'sub', 'before' => '[sub]', 'after' => '[/sub]', 'description' => $txt[448]),
			'tele' => array('code' => 'tt', 'before' => '[tt]', 'after' => '[/tt]', 'description' => $txt[440]),
			'code' => array('code' => 'code', 'before' => '[code]', 'after' => '[/code]', 'description' => $txt[259]),
			'quote' => array('code' => 'quote', 'before' => '[quote]', 'after' => '[/quote]', 'description' => $txt[260]),
		);
	}

	template_shoutbox_embed($shoutbox);
}

function sp_gallery($parameters, $id, $return_parameters = false)
{
	global $db_prefix, $context, $modSettings, $scripturl;
	global $sourcedir, $txt, $settings, $boardurl, $galurl;
	static $mod, $GD_Installed;

	$block_parameters = array(
		'limit' => 'int',
		'type' => 'select',
		'direction' => 'select',
	);

	if ($return_parameters)
		return $block_parameters;

	$limit = empty($parameters['limit']) ? 1 : (int) $parameters['limit'];
	$type = empty($parameters['type']) ? 0 : 1;
	$direction = empty($parameters['direction']) ? 0 : 1;

	if (!isset($mod))
	{
		if (file_exists($sourcedir . '/Aeva-Media.php'))
			$mod = 'aeva_media';
		elseif (file_exists($sourcedir . '/MGallery.php'))
			$mod = 'smf_media_gallery';
		elseif (file_exists($sourcedir . '/Gallery.php'))
			$mod = 'smf_gallery';
	}

	if (empty($mod))
	{
		echo '
								', $txt['error_sp_no_gallery_found'];
		return;
	}
	elseif ($mod == 'aeva_media')
	{
		require_once($sourcedir . '/Aeva-Subs.php');

		$items = aeva_getMediaItems(0, $limit, $type ? 'RAND()' : 'm.id_media DESC');
	}
	elseif ($mod == 'smf_media_gallery')
	{
		require_once($sourcedir . '/Subs-MGallery.php');

		loadMGal_Settings();
		if (loadLanguage('MGallery', '', false) === false)
			loadLanguage('MGallery', 'english', false);

		$items = getMediaItems(0, $limit, $type ? 'RAND()' : 'm.id_media DESC');
	}
	elseif ($mod == 'smf_gallery')
	{
		if (loadLanguage('Gallery', '', false) === false)
			loadLanguage('Gallery', 'english', false);

		if (!isset($GD_Installed))
			$GD_Installed = function_exists('imagecreate');

		if (empty($modSettings['gallery_url']))
			$modSettings['gallery_url'] = $boardurl . '/gallery/';

		$request = db_query("
			SELECT
				p.ID_PICTURE, p.commenttotal, p.filesize, p.views, p.thumbfilename,
				p.filename, p.height, p.width, p.title, p.ID_MEMBER, m.memberName,
				m.realName, p.date, p.description
			FROM {$db_prefix}gallery_pic AS p
				LEFT JOIN {$db_prefix}members AS m ON (m.ID_MEMBER = p.ID_MEMBER)
			WHERE p.approved = 1
			ORDER BY " . ($type ? 'RAND()' : 'p.ID_PICTURE DESC') . "
			LIMIT $limit", __FILE__, __LINE__);
		$items = array();
		while ($row = mysql_fetch_assoc($request))
		{
			$items[] = array(
				'id' => $row['ID_PICTURE'],
				'title' => $row['title'],
				'views' => $row['views'],
				'poster_id' => $row['ID_MEMBER'],
				'poster_name' => $row['realName'],
				'poster_link' => empty($row['ID_MEMBER']) ? $txt['gallery_guest'] : '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '">' . $row['realName'] . '</a>',
				'thumbfilename' => $row['thumbfilename'],
				'filename' => $row['filename'],
				'src' => $modSettings['gallery_url'] . ($GD_Installed ? $row['thumbfilename'] : $row['filename'] . '" width="120'),
			);
		}
		mysql_free_result($request);
	}

	if (empty($items))
	{
		echo '
								', $txt['error_sp_no_pictures_found'];
		return;
	}

	echo '
								<table class="sp_auto_align">', $direction ? '
									<tr>' : '';

	foreach ($items as $item)
	{
	  echo !$direction ? '
									<tr>' : '', '
										<td>
											<div class="sp_image smalltext">';

		if ($mod == 'aeva_media')
		{
			echo '
												<a href="', $galurl, 'sa=item;in=', $item['id'], '">', $item['title'], '</a><br />
												<a href="', $galurl, 'sa=item;in=', $item['id'], '"><img src="', $galurl, 'sa=media;in=', $item['id'], ';thumb" alt="" /></a><br />
												', $txt['aeva_views'], ': ', $item['views'], '<br />
												', $txt['aeva_posted_by'], ': <a href="', $scripturl, '?action=profile;u=', $item['poster_id'], '">', $item['poster_name'], '</a><br />
												', $txt['aeva_in_album'], ': <a href="', $galurl, 'sa=album;in=', $item['id_album'], '">', $item['album_name'], '</a>', $item['is_new'] ?
												'<br /><img alt="" src="' . $settings['images_url'] . '/' . $context['user']['language'] . '/new.gif" border="0" />' : '';
		}
		elseif ($mod == 'smf_media_gallery')
		{
			echo '
												<a href="', $galurl, 'sa=item;id=', $item['id'], '">', $item['title'], '</a><br />
												<a href="', $galurl, 'sa=item;id=', $item['id'], '"><img src="', $galurl, 'sa=media;id=', $item['id'], ';thumb" alt="" /></a><br />
												', $txt['mgallery_views'], ': ', $item['views'], '<br />
												', $txt['mgallery_posted_by'], ': <a href="', $scripturl, '?action=profile;u=', $item['poster_id'], '">', $item['poster_name'], '</a><br />
												', $txt['mgallery_in_album'], ': <a href="', $galurl, 'sa=album;id=', $item['id_album'], '">', $item['album_name'], '</a>', $item['is_new'] ?
												'<br /><img alt="" src="' . $settings['images_url'] . '/' . $context['user']['language'] . '/new.gif" border="0" />' : '';
		}
		elseif ($mod == 'smf_gallery')
		{
			echo '
												<a href="', $scripturl, '?action=gallery;sa=view;id=', $item['id'], '">', $item['title'], '</a><br />
												<a href="', $scripturl, '?action=gallery;sa=view;id=', $item['id'], '"><img src="', $item['src'], '" alt="" /></a><br />
												', $txt['gallery_text_views'], $item['views'], '<br />
												', $txt['gallery_text_by'], ' ', $item['poster_link'], '<br />';
		}

		echo '
											</div>
										</td>', !$direction ? '
									</tr>' : '';
	}

	echo $direction ? '
									</tr>' : '', '
								</table>';
}

function sp_arcade($parameters, $id, $return_parameters = false)
{
	global $db_prefix, $sourcedir, $scripturl, $settings, $txt, $color_profile;
	static $mod;

	$block_parameters = array(
		'limit' => 'int',
		'type' => 'select',
	);

	if ($return_parameters)
		return $block_parameters;

	$limit = !empty($parameters['limit']) ? (int) $parameters['limit'] : 5;
	$type = !empty($parameters['type']) ? (int) $parameters['type'] : 0;

	if (!isset($mod))
	{
		if (file_exists($sourcedir . '/Arcade.php'))
			$mod = 'smf_arcade';
		else
			$mod = '';
	}

	if (empty($mod))
	{
		echo '
								', $txt['error_sp_no_arcade_found'];
		return;
	}
	elseif ($mod == 'smf_arcade')
	{
		require_once($sourcedir . '/ArcadeStats.php');
		require_once($sourcedir . '/Subs-Arcade.php');

		if (file_exists($sourcedir . '/ArcadeDbConnector.php'))
			require_once($sourcedir . '/ArcadeDbConnector.php');

		loadLanguage('Arcade');

		if (empty($type))
			$stats = ArcadeStats_MostPlayed($limit);
		elseif ($type == 1)
			$stats = ArcadeStats_BestPlayers($limit);
		elseif ($type == 2)
			$stats = ArcadeStats_LongestChampions($limit);

		if (empty($stats))
		{
			echo '
								', $txt['error_sp_no_stats_found'];
			return;
		}
		else
			$stats[count($stats) - 1]['last'] = true;

		echo '
								<ul class="sp_list">';

		if (empty($type))
		{
			foreach ($stats as $stat)
			{
				echo '
									<li class="sp_list_top">', sp_embed_image('game'), ' ', $stat['link'], '</li>
									<li class="', empty($stat['last']) ? 'sp_list_bottom ' : '', 'sp_list_indent smalltext">', $txt['sp-game_plays'], ': ', $stat['plays'], ' | ', $txt['sp-game_rating'], ': ', $stat['rating'], '</li>';
			}
		}
		elseif ($type == 1)
		{
			$types = array(1 => 'gold', 'silver', 'bronze');
			$current = 0;
			foreach ($stats as $stat)
			{
				echo '
									<li class="sp_list_top">', sp_embed_image(isset($types[++$current]) ? $types[$current] : 'user' ), ' ', $stat['link'], '</li>
									<li class="', empty($stat['last']) ? 'sp_list_bottom ' : '', 'sp_list_indent smalltext"><img src="', $settings['images_url'], '/bar.gif" width="', $stat['precent'], '" height="8" alt="" /> ', $stat['champions'], ' ', $txt['sp-games'], '</li>';
			}
		}
		elseif ($type == 2)
		{
			$types = array(1 => 'gold', 'silver', 'bronze');
			$current = 0;
			foreach ($stats as $stat)
			{
				echo '
									<li class="sp_list_top">', sp_embed_image(isset($types[++$current]) ? $types[$current] : 'user' ), ' ', $stat['member_link'], '</li>
									<li class="', empty($stat['last']) ? 'sp_list_bottom ' : '', 'sp_list_indent smalltext">', $stat['game_link'], '<br />', $stat['duration'], '</li>';
			}
		}

		echo '
								</ul>';
	}
}

function sp_shop($parameters, $id, $return_parameters = false)
{
	global $db_prefix, $sourcedir, $scripturl, $txt, $color_profile;
	global $context, $boardurl, $modSettings;
	static $mod;

	$block_parameters = array(
		'style' => 'select',
		'limit' => 'int',
		'type' => 'select',
		'sort' => 'select',
	);

	if ($return_parameters)
		return $block_parameters;

	$style = !empty($parameters['style']);
	$limit = !empty($parameters['limit']) ? (int) $parameters['limit'] : 5;
	$type = !empty($parameters['type']) ? (int) $parameters['type'] : 0;
	$sort = !empty($parameters['sort']);

	if (!isset($mod))
	{
		if (file_exists($sourcedir . '/shop'))
			$mod = 'smf_shop';
		else
			$mod = '';
	}

	if (empty($mod))
	{
		echo '
								', $txt['error_sp_no_shop_found'];
		return;
	}
	elseif ($mod == 'smf_shop')
	{
		require_once($sourcedir . '/shop/Shop-Subs.php');
		loadLanguage('Shop');

		if (empty($style))
		{
			$request = db_query("
				SELECT ID_MEMBER, realName, " . ($type == 0 ? '(money + moneyBank)' : ($type == 1 ? 'money' : 'moneyBank')) . " AS money
				FROM {$db_prefix}members
				ORDER BY money DESC
				LIMIT $limit", __FILE__, __LINE__);
			$members = array();
			$colorids = array();
			while ($row = mysql_fetch_assoc($request))
			{
				if (!empty($row['ID_MEMBER']))
					$colorids[$row['ID_MEMBER']] = $row['ID_MEMBER'];

				$members[] = array(
					'id' => $row['ID_MEMBER'],
					'name' => $row['realName'],
					'href' => $scripturl . '?action=profile;u=' . $row['ID_MEMBER'],
					'link' => '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '">' . $row['realName'] . '</a>',
					'money' => formatMoney($row['money']),
				);
			}
			mysql_free_result($request);

			if (empty($members))
			{
				echo '
								', $txt['error_sp_no_members_found'];
				return;
			}

			if (!empty($colorids) && sp_loadColors($colorids) !== false)
			{
				foreach ($members as $k => $p)
				{
					if (!empty($color_profile[$p['id']]['link']))
						$members[$k]['link'] = $color_profile[$p['id']]['link'];
				}
			}

			echo '
								<ul class="sp_list">';

			foreach ($members as $member)
				echo '
									<li>', sp_embed_image('money'), ' ', $member['link'], ' - ', $member['money'], '</li>';

			echo '
								</ul>';
		}
		else
		{
			$request = db_query("
				SELECT id, name, price, image
				FROM {$db_prefix}shop_items
				WHERE stock > 0
				ORDER BY " . ($sort ? 'RAND()' : 'id DESC') . "
				LIMIT $limit", __FILE__, __LINE__);
			$items = array();
			while ($row = mysql_fetch_assoc($request))
			{
				$items[] = array(
					'id' => $row['id'],
					'name' => $row['name'],
					'href' => $scripturl . '?action=shop;do=buy2;id=' . $row['id'],
					'link' => '<a href="' . $scripturl . '?action=shop;do=buy2;id=' . $row['id'] . '">' . $txt['shop_buynow'] . '</a>',
					'price' => $row['price'],
					'image' => empty($row['image']) ? '' : '<img width="' . $modSettings['shopImageWidth'] . '" height="' . $modSettings['shopImageHeight'] . '" src="' . $boardurl . '/Sources/shop/item_images/' . $row['image'] . '" alt="' . $row['name'] . '" />',
				);
			}
			mysql_free_result($request);

			if (empty($items))
			{
				echo '
								', $txt['error_sp_no_items_found'];
				return;
			}
			else
				$item_count = count($items);

			echo '
								<table class="sp_fullwidth">';

			$count = 0;
			foreach ($items as $item)
				echo '
									<tr>
										<td class="sp_shop">
											', $item['image'], '
										</td>
										<td class="sp_shop_info', $item_count != ++$count ? ' sp_shop_divider' : '', '">
											<strong>', $item['name'], '</strong><br />
											', $txt['shop_price'], ': ', $item['price'], '<br />
											', $context['user']['money'] < $item['price'] ? sprintf($txt['shop_need'], formatMoney($item['price'] - $context['user']['money'])) : $item['link'], '
										</td>
									</tr>';

			echo '
								</table>';
		}
	}
}

function sp_blog($parameters, $id, $return_parameters = false)
{
	global $db_prefix, $scripturl, $user_info, $modSettings;
	global $context, $boarddir, $sourcedir, $txt, $color_profile;
	static $mod;

	$block_parameters = array(
		'limit' => 'int',
		'type' => 'select',
		'sort' => 'select',
	);

	if ($return_parameters)
		return $block_parameters;

	$limit = empty($parameters['limit']) ? 5 : (int) $parameters['limit'];
	$type = empty($parameters['type']) ? 0 : 1;
	$sort = empty($parameters['sort']) ? 0 : 1;

	if (!isset($mod))
	{
		if (file_exists($boarddir . '/zCommunity'))
			$mod = 'zcommunity';
		elseif (file_exists($sourcedir . '/Blog.php'))
			$mod = 'smfblog';
		else
			$mod = '';
	}

	if (empty($mod))
	{
		echo '
								', $txt['error_sp_no_blog_found'];
		return;
	}
	elseif ($mod == 'zcommunity')
	{
		$request = db_query("
			SELECT b.blog_id, b.blog_owner, b.member_groups, bs.users_allowed_access, bs.hideBlog AS hidden
			FROM {$db_prefix}blog_blogs AS b
				LEFT JOIN {$db_prefix}blog_settings AS bs ON (bs.blog_id = b.blog_id)", __FILE__, __LINE__);
		$visible_blogs = array();
		while ($row = mysql_fetch_assoc($request))
		{
			$can_see_this_blog = false;

			if (empty($row['hidden']))
			{
				$allowedGroups = !empty($row['member_groups']) ? explode(',', $row['member_groups']) : array();
				$can_see_this_blog = count(array_intersect($user_info['groups'], $allowedGroups)) > 0;

				if (empty($can_see_this_blog) && !empty($row['users_allowed_access']) && !$user_info['is_guest'])
				{
					$users_allowed = !empty($row['users_allowed_access']) ? explode(',', $row['users_allowed_access']) : array();
					$can_see_this_blog = in_array($context['user']['id'], $users_allowed);
				}
			}

			if ($user_info['is_admin'] || ($context['user']['id'] == $row['blog_owner']))
				$can_see_this_blog = true;

			if ($can_see_this_blog)
				$visible_blogs[] = $row['blog_id'];
		}
		mysql_free_result($request);

		if (empty($visible_blogs))
		{
			echo '
								', $txt['error_sp_no_blogs_found'];
			return;
		}

		if (empty($type))
		{
			$request = db_query("
				SELECT t.article_id, t.subject
				FROM {$db_prefix}blog_articles AS t
					LEFT JOIN {$db_prefix}blog_settings AS bs ON (bs.blog_id = t.blog_id)
				WHERE t.blog_id IN (" . implode(', ', $visible_blogs) . ")
					AND ((t.approved = 1) OR (bs.articles_require_approval = 0))
				ORDER BY " . ($sort ? 'RAND()' : 't.article_id DESC') . "
				LIMIT $limit", __FILE__, __LINE__);
			$articles = array();
			while ($row = mysql_fetch_assoc($request))
			{
				$articles[] = array(
					'id' => $row['article_id'],
					'subject' => strip_tags($row['subject']),
					'link' => '<a href="' . $scripturl . '?article=' . $row['article_id'] . '.0">' . $row['subject'] . '</a>',
				);
			}
			mysql_free_result($request);

			if (empty($articles))
			{
				echo '
								', $txt['error_sp_no_articles_found'];
				return;
			}

			echo '
								<ul class="sp_list">';

			foreach ($articles as $article)
				echo '
									<li>', sp_embed_image('blog'), ' ', $article['link'], '</li>';

			echo '
								</ul>';
		}
		else
		{
			$request = db_query("
				SELECT
					b.blog_id, b.name, t.article_id, t.subject, m.ID_MEMBER, m.realName,
					m.avatar, a.ID_ATTACH, a.attachmentType, a.filename
				FROM {$db_prefix}blog_blogs AS b
					LEFT JOIN {$db_prefix}blog_articles AS t ON (t.article_id = b.last_article_id)
					LEFT JOIN {$db_prefix}members AS m ON (m.ID_MEMBER = b.blog_owner)
					LEFT JOIN {$db_prefix}attachments AS a ON (a.ID_MEMBER = m.ID_MEMBER)
				WHERE b.blog_id IN (" . implode(', ', $visible_blogs) . ")
				ORDER BY " . ($sort ? 'RAND()' : 'b.last_article_id DESC') . "
				LIMIT $limit", __FILE__, __LINE__);
			$blogs = array();
			$colorids = array();
			while ($row = mysql_fetch_assoc($request))
			{
				if (!empty($row['ID_MEMBER']))
					$colorids[$row['ID_MEMBER']] = $row['ID_MEMBER'];

				if (stristr($row['avatar'], 'http://') && !empty($modSettings['avatar_check_size']))
				{
					$sizes = url_image_size($row['avatar']);

					if ($modSettings['avatar_action_too_large'] == 'option_refuse' && is_array($sizes) && (($sizes[0] > $modSettings['avatar_max_width_external'] && !empty($modSettings['avatar_max_width_external'])) || ($sizes[1] > $modSettings['avatar_max_height_external'] && !empty($modSettings['avatar_max_height_external']))))
					{
						$row['avatar'] = '';
						updateMemberData($row['ID_MEMBER'], array('avatar' => '\'\''));
					}
				}

				$blogs[] = array(
					'id' => $row['blog_id'],
					'name' => $row['name'],
					'href' => $scripturl . '?blog=' . $row['blog_id'] . '.0',
					'link' => '<a href="' . $scripturl . '?blog=' . $row['blog_id'] . '.0">' . $row['name'] . '</a>',
					'article' => array(
						'id' => $row['article_id'],
						'subject' => strip_tags($row['subject']),
						'link' => '<a href="' . $scripturl . '?article=' . $row['article_id'] . '.0">' . $row['subject'] . '</a>',
					),
					'owner' => array(
						'id' => $row['ID_MEMBER'],
						'name' => $row['realName'],
						'href' => $scripturl . '?action=profile;u=' . $row['ID_MEMBER'],
						'link' => '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '">' . $row['realName'] . '</a>',
					),
					'avatar' => array(
						'name' => $row['avatar'],
						'image' => $row['avatar'] == '' ? ($row['ID_ATTACH'] > 0 ? '<img src="' . (empty($row['attachmentType']) ? $scripturl . '?action=dlattach;attach=' . $row['ID_ATTACH'] . ';type=avatar' : $modSettings['custom_avatar_url'] . '/' . $row['filename']) . '" alt="" class="avatar" border="0" />' : '') : (stristr($row['avatar'], 'http://') ? '<img src="' . $row['avatar'] . '" alt="" class="avatar" border="0" />' : '<img src="' . $modSettings['avatar_url'] . '/' . htmlspecialchars($row['avatar']) . '" alt="" class="avatar" border="0" />'),
						'href' => $row['avatar'] == '' ? ($row['ID_ATTACH'] > 0 ? (empty($row['attachmentType']) ? $scripturl . '?action=dlattach;attach=' . $row['ID_ATTACH'] . ';type=avatar' : $modSettings['custom_avatar_url'] . '/' . $row['filename']) : '') : (stristr($row['avatar'], 'http://') ? $row['avatar'] : $modSettings['avatar_url'] . '/' . $row['avatar']),
						'url' => $row['avatar'] == '' ? '' : (stristr($row['avatar'], 'http://') ? $row['avatar'] : $modSettings['avatar_url'] . '/' . $row['avatar']),
					),
				);
			}
			mysql_free_result($request);

			if (!empty($colorids) && sp_loadColors($colorids) !== false)
			{
				foreach ($blogs as $k => $p)
				{
					if (!empty($color_profile[$p['owner']['id']]['link']))
						$blogs[$k]['owner']['link'] = $color_profile[$p['owner']['id']]['link'];
				}
			}

			echo '
								<table class="sp_fullwidth sp_blog">';

			foreach ($blogs as $blog)
				echo '
									<tr>
										<td class="sp_blog sp_center">', !empty($blog['avatar']['href']) ? '
											<a href="' . $scripturl . '?action=profile;u=' . $blog['owner']['id'] . '"><img src="' . $blog['avatar']['href'] . '" alt="' . $blog['name'] . '" width="40" /></a>' : '', '
										</td>
										<td>
											<span class="sp_blog_title">', $blog['owner']['link'], '</span><br />
											', $blog['article']['link'], '
										</td>
									</tr>';

			echo '
								</table>';
		}
	}
	elseif ($mod == 'smfblog')
	{
		$request = db_query("
			SELECT b.ID_BOARD
			FROM {$db_prefix}boards AS b
			WHERE $user_info[query_see_board]
				AND b.is_blog = 1", __FILE__, __LINE__);
		$visible_blogs = array();
		while ($row = mysql_fetch_assoc($request))
			$visible_blogs[] = $row['ID_BOARD'];
		mysql_free_result($request);

		if (empty($visible_blogs))
		{
			echo '
								', $txt['error_sp_no_blogs_found'];
			return;
		}

		if (empty($type))
		{
			$request = db_query("
				SELECT t.ID_TOPIC, m.subject
				FROM {$db_prefix}topics AS t
					INNER JOIN {$db_prefix}messages AS m ON (m.ID_MSG = t.ID_FIRST_MSG)
				WHERE t.ID_BOARD IN (" . implode(', ', $visible_blogs) . ")
				ORDER BY " . ($sort ? 'RAND()' : 't.ID_TOPIC DESC') . "
				LIMIT $limit", __FILE__, __LINE__);
			while ($row = mysql_fetch_assoc($request))
			{
				censorText($row['subject']);

				$articles[] = array(
					'id' => $row['ID_TOPIC'],
					'subject' => $row['subject'],
					'link' => '<a href="' . $scripturl . '?action=blog;sa=view_post;id=' . $row['ID_TOPIC'] . '">' . $row['subject'] . '</a>',
				);
			}
			mysql_free_result($request);

			if (empty($articles))
			{
				echo '
								', $txt['error_sp_no_articles_found'];
				return;
			}

			echo '
								<ul class="sp_list">';

			foreach ($articles as $article)
				echo '
									<li>', sp_embed_image('blog'), ' ', $article['link'], '</li>';

			echo '
								</ul>';
		}
		else
		{
			$request = db_query("
				SELECT b.ID_BOARD, b.blog_alias, b.name, MAX(t.ID_TOPIC) AS ID_TOPIC
				FROM {$db_prefix}boards AS b
					INNER JOIN {$db_prefix}topics AS t ON (t.ID_BOARD = b.ID_BOARD)
				WHERE b.ID_BOARD IN (" . implode(', ', $visible_blogs) . ")
				GROUP BY b.ID_BOARD
				ORDER BY " . ($sort ? 'RAND()' : 'b.ID_BOARD DESC') . "
				LIMIT $limit", __FILE__, __LINE__);
			$blogs = array();
			while ($row = mysql_fetch_assoc($request))
			{
				$blogs[$row['ID_BOARD']] = array(
					'id' => $row['ID_BOARD'],
					'alias' => $row['blog_alias'],
					'name' => $row['name'],
					'href' => $scripturl . '?action=blog;sa=view_blog;name=' . $row['blog_alias'],
					'link' => '<a href="' . $scripturl . '?action=blog;sa=view_blog;name=' . $row['blog_alias'] . '">' . $row['name'] . '</a>',
				);
				$articles[] = $row['ID_TOPIC'];
			}
			mysql_free_result($request);

			if (empty($articles))
			{
				echo '
								', $txt['error_sp_no_articles_found'];
				return;
			}

			$request = db_query("
				SELECT
					t.ID_BOARD, t.ID_TOPIC, m.subject, mem.ID_MEMBER, mem.realName,
					mem.avatar, a.ID_ATTACH, a.attachmentType, a.filename
				FROM {$db_prefix}topics AS t
					INNER JOIN {$db_prefix}messages AS m ON (m.ID_MSG = t.ID_FIRST_MSG)
					INNER JOIN {$db_prefix}members AS mem ON (mem.ID_MEMBER = t.ID_MEMBER_STARTED)
					LEFT JOIN {$db_prefix}attachments AS a ON (a.ID_MEMBER = t.ID_MEMBER_STARTED)
				WHERE t.ID_TOPIC IN (" . implode(', ', $articles) . ")
				LIMIT " . count($articles), __FILE__, __LINE__);
			$colorids = array();
			while ($row = mysql_fetch_assoc($request))
			{
				if (!empty($row['ID_MEMBER']))
					$colorids[$row['ID_MEMBER']] = $row['ID_MEMBER'];

				if (stristr($row['avatar'], 'http://') && !empty($modSettings['avatar_check_size']))
				{
					$sizes = url_image_size($row['avatar']);

					if ($modSettings['avatar_action_too_large'] == 'option_refuse' && is_array($sizes) && (($sizes[0] > $modSettings['avatar_max_width_external'] && !empty($modSettings['avatar_max_width_external'])) || ($sizes[1] > $modSettings['avatar_max_height_external'] && !empty($modSettings['avatar_max_height_external']))))
					{
						$row['avatar'] = '';
						updateMemberData($row['ID_MEMBER'], array('avatar' => '\'\''));
					}
				}

				censorText($row['subject']);

				$blogs[$row['ID_BOARD']] += array(
					'article' => array(
						'id' => $row['ID_TOPIC'],
						'subject' => $row['subject'],
						'link' => '<a href="' . $scripturl . '?action=blog;sa=view_post;id=' . $row['ID_TOPIC'] . '">' . $row['subject'] . '</a>',
					),
					'owner' => array(
						'id' => $row['ID_MEMBER'],
						'name' => $row['realName'],
						'href' => $scripturl . '?action=profile;u=' . $row['ID_MEMBER'],
						'link' => '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '">' . $row['realName'] . '</a>',
					),
					'avatar' => array(
						'name' => $row['avatar'],
						'image' => $row['avatar'] == '' ? ($row['ID_ATTACH'] > 0 ? '<img src="' . (empty($row['attachmentType']) ? $scripturl . '?action=dlattach;attach=' . $row['ID_ATTACH'] . ';type=avatar' : $modSettings['custom_avatar_url'] . '/' . $row['filename']) . '" alt="" class="avatar" border="0" />' : '') : (stristr($row['avatar'], 'http://') ? '<img src="' . $row['avatar'] . '" alt="" class="avatar" border="0" />' : '<img src="' . $modSettings['avatar_url'] . '/' . htmlspecialchars($row['avatar']) . '" alt="" class="avatar" border="0" />'),
						'href' => $row['avatar'] == '' ? ($row['ID_ATTACH'] > 0 ? (empty($row['attachmentType']) ? $scripturl . '?action=dlattach;attach=' . $row['ID_ATTACH'] . ';type=avatar' : $modSettings['custom_avatar_url'] . '/' . $row['filename']) : '') : (stristr($row['avatar'], 'http://') ? $row['avatar'] : $modSettings['avatar_url'] . '/' . $row['avatar']),
						'url' => $row['avatar'] == '' ? '' : (stristr($row['avatar'], 'http://') ? $row['avatar'] : $modSettings['avatar_url'] . '/' . $row['avatar']),
					),
				);
			}
			mysql_free_result($request);

			if (!empty($colorids) && sp_loadColors($colorids) !== false)
			{
				foreach ($blogs as $k => $p)
				{
					if (!empty($color_profile[$p['owner']['id']]['link']))
						$blogs[$k]['owner']['link'] = $color_profile[$p['owner']['id']]['link'];
				}
			}

			echo '
								<table class="sp_fullwidth sp_blog">';

			foreach ($blogs as $blog)
				echo '
									<tr>
										<td class="sp_top_poster sp_blog">', !empty($blog['avatar']['href']) ? '
											<a href="' . $scripturl . '?action=profile;u=' . $blog['owner']['id'] . '"><img src="' . $blog['avatar']['href'] . '" alt="' . $blog['name'] . '" width="40" /></a>' : '', '
										</td>
										<td>
											<span class="sp_blog_title">', $blog['owner']['link'], '</span><br />
											', $blog['article']['link'], '
										</td>
									</tr>';

			echo '
								</table>';
		}
	}
}

function sp_bbc($parameters, $id, $return_parameters = false)
{
	$block_parameters = array(
		'content' => 'bbc',
	);

	if ($return_parameters)
		return $block_parameters;

	$content = !empty($parameters['content']) ? $parameters['content'] : '';

	echo '
								', parse_bbc($content);
}

function sp_html($parameters, $id, $return_parameters = false)
{
	$block_parameters = array(
		'content' => 'textarea',
	);

	if ($return_parameters)
		return $block_parameters;

	$content = !empty($parameters['content']) ? $parameters['content'] : '';

	echo '
								', un_htmlspecialchars($content);
}

function sp_php($parameters, $id, $return_parameters = false)
{
	$block_parameters = array(
		'content' => 'textarea',
	);

	if ($return_parameters)
		return $block_parameters;

	$content = !empty($parameters['content']) ? $parameters['content'] : '';

	$content = trim(un_htmlspecialchars($content));
	if (substr($content, 0, 5) == '<?php')
		$content = substr($content, 5);
	if (substr($content, -2) == '?>')
		$content = substr($content, 0, -2);

	eval($content);
}

?>