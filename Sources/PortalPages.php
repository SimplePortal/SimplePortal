<?php
/**********************************************************************************
* PortalPages.php                                                                 *
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

if (!defined('SMF'))
	die('Hacking attempt...');

/*
	void sportal_pages()
		// !!!
*/

function sportal_pages()
{
	global $smcFunc, $context, $txt, $scripturl, $sourcedir, $user_info;

	loadTemplate('PortalPages');

	$page_id = !empty($_REQUEST['page']) ? $_REQUEST['page'] : 0;
	if (is_numeric($page_id))
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