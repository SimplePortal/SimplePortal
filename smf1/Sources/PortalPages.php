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
	void sportal_pages()
		// !!!
*/

function sportal_pages()
{
	global $db_prefix, $func, $context, $txt, $scripturl, $sourcedir, $user_info;

	loadTemplate('PortalPages');

	$page_id = !empty($_REQUEST['page']) ? $_REQUEST['page'] : 0;

	$context['SPortal']['page'] = sportal_get_pages($page_id, true, true);

	if (empty($context['SPortal']['page']['id']))
		fatal_lang_error('error_sp_page_not_found', false);

	$context['SPortal']['page']['style'] = sportal_parse_style('explode', $context['SPortal']['page']['style'], true);

	if (empty($_SESSION['last_viewed_page']) || $_SESSION['last_viewed_page'] != $context['SPortal']['page']['id'])
	{
		$page_id = $context['SPortal']['page']['id'];
		db_query("
			UPDATE {$db_prefix}sp_pages
			SET views = views + 1
			WHERE ID_PAGE = $page_id
			LIMIT 1", __FILE__, __LINE__);

		$_SESSION['last_viewed_page'] = $context['SPortal']['page']['id'];
	}

	$context['linktree'][] = array(
		'url' => $scripturl . '?page=' . $page_id,
		'name' => $context['SPortal']['page']['title'],
	);

	$context['page_title'] = $context['SPortal']['page']['title'];
	$context['sub_template'] = 'view_page';
}

?>