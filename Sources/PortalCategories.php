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

function sportal_categories()
{
	global $context, $scripturl, $txt;

	loadTemplate('PortalCategories');

	$context['SPortal']['categories'] = sportal_get_categories(0, true, true);

	$context['linktree'][] = array(
		'url' => $scripturl . '?action=portal;sa=categories',
		'name' => $txt['sp-categories'],
	);

	$context['page_title'] = $txt['sp-categories'];
	$context['sub_template'] = 'view_categories';
}

?>