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
	void sportal_shoutbox()
		// !!!
*/

function sportal_shoutbox()
{
	global $smcFunc, $context, $scripturl, $sourcedir, $user_info;

	$shoutbox_id = !empty($_REQUEST['shoutbox_id']) ? (int) $_REQUEST['shoutbox_id'] : 0;
	$request_time = !empty($_REQUEST['time']) ? (int) $_REQUEST['time'] : 0;

	$context['SPortal']['shoutbox'] = sportal_get_shoutbox($shoutbox_id, true, true);

	if (empty($context['SPortal']['shoutbox']))
		fatal_lang_error('error_sp_shoutbox_not_exist', false);

	$context['SPortal']['shoutbox']['warning'] = parse_bbc($context['SPortal']['shoutbox']['warning']);

	$can_moderate = allowedTo('sp_admin') || allowedTo('sp_manage_shoutbox');
	if (!$can_moderate && !empty($context['SPortal']['shoutbox']['moderator_groups']))
		$can_moderate = count(array_intersect($user_info['groups'], $context['SPortal']['shoutbox']['moderator_groups'])) > 0;

	if (!empty($_REQUEST['shout']))
	{
		checkSession('request');

		is_not_guest();

		if (!($flood = sp_prevent_flood('spsbp', false)))
		{
			require_once($sourcedir . '/Subs-Post.php');

			$_REQUEST['shout'] = $smcFunc['htmlspecialchars'](trim($_REQUEST['shout']));
			preparsecode($_REQUEST['shout']);

			if (!empty($_REQUEST['shout']))
				sportal_create_shout($context['SPortal']['shoutbox'], $_REQUEST['shout']);
		}
		else
			$context['SPortal']['shoutbox']['warning'] = $flood;
	}

	if (!empty($_REQUEST['delete']))
	{
		checkSession('request');

		if (!$can_moderate)
			fatal_lang_error('error_sp_cannot_shoutbox_moderate', false);

		$_REQUEST['delete'] = (int) $_REQUEST['delete'];

		if (!empty($_REQUEST['delete']))
			sportal_delete_shout($shoutbox_id, $_REQUEST['delete']);
	}

	loadTemplate('PortalShoutbox');

	if (isset($_REQUEST['xml']))
	{
		$shout_parameters = array(
			'limit' => $context['SPortal']['shoutbox']['num_show'],
			'bbc' => $context['SPortal']['shoutbox']['allowed_bbc'],
			'reverse' => $context['SPortal']['shoutbox']['reverse'],
			'cache' => $context['SPortal']['shoutbox']['caching'],
			'can_moderate' => $can_moderate,
		);
		$context['SPortal']['shouts'] = sportal_get_shouts($shoutbox_id, $shout_parameters);

		$context['sub_template'] = 'shoutbox_xml';
		$context['SPortal']['updated'] = empty($context['SPortal']['shoutbox']['last_update']) || $context['SPortal']['shoutbox']['last_update'] > $request_time;

		return;
	}

	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*)
		FROM {db_prefix}sp_shouts
		WHERE id_shoutbox = {int:current}',
		array(
			'current' => $shoutbox_id,
		)
	);
	list ($total_shouts) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	$context['per_page'] = $context['SPortal']['shoutbox']['num_show'];
	$context['start'] = !empty($_REQUEST['start']) ? (int) $_REQUEST['start'] : 0;
	$context['page_index'] = constructPageIndex($scripturl . '?action=portal;sa=shoutbox;shoutbox_id=' . $shoutbox_id, $context['start'], $total_shouts, $context['per_page']);

	$shout_parameters = array(
		'start' => $context['start'],
		'limit' => $context['per_page'],
		'bbc' => $context['SPortal']['shoutbox']['allowed_bbc'],
		'cache' => $context['SPortal']['shoutbox']['caching'],
		'can_moderate' => $can_moderate,
	);
	$context['SPortal']['shouts_history'] = sportal_get_shouts($shoutbox_id, $shout_parameters);

	$context['SPortal']['shoutbox_id'] = $shoutbox_id;
	$context['sub_template'] = 'shoutbox_all';
	$context['page_title'] = $context['SPortal']['shoutbox']['name'];
}

?>