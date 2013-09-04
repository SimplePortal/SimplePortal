<?php

/**
 * @package SimplePortal
 *
 * @author SimplePortal Team
 * @copyright 2013 SimplePortal Team
 * @license BSD 3-clause
 *
 * @version 2.4
 */

if (!defined('SMF'))
	die('Hacking attempt...');

/**
 * List the available pages in the system
 */
function sportal_pages()
{
	global $context, $scripturl, $txt;

	loadTemplate('PortalPages');

	$context['SPortal']['pages'] = sportal_get_pages(0, true, true);

	$context['linktree'][] = array(
		'url' => $scripturl . '?action=portal;sa=pages',
		'name' => $txt['sp-pages'],
	);

	$context['page_title'] = $txt['sp-pages'];
	$context['sub_template'] = 'view_pages';
}

/**
 * View a specific page in the system
 */
function sportal_page()
{
	global $smcFunc, $context, $scripturl, $txt;

	loadTemplate('PortalPages');

	$page_id = !empty($_REQUEST['page']) ? $_REQUEST['page'] : 0;

	if (is_int($page_id))
		$page_id = (int) $page_id;
	else
		$page_id = $smcFunc['htmlspecialchars']($page_id, ENT_QUOTES);

	$context['SPortal']['page'] = sportal_get_pages($page_id, true, true);

	if (empty($context['SPortal']['page']['id']))
		fatal_lang_error('error_sp_page_not_found', false);

	$context['SPortal']['page']['style'] = sportal_parse_style('explode', $context['SPortal']['page']['style'], true);

	if (empty($_SESSION['last_viewed_page']) || $_SESSION['last_viewed_page'] != $context['SPortal']['page']['id'])
	{
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}sp_pages
			SET views = views + 1
			WHERE id_page = {int:current_page}',
			array(
				'current_page' => $context['SPortal']['page']['id'],
			)
		);

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