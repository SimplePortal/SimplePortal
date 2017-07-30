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
	void sportal_main()
		// !!!

	void sportal_credits()
		// !!!
*/

function sportal_main()
{
	global $smcFunc, $context, $sourcedir;

	if (WIRELESS)
		redirectexit('action=forum');

	$context['page_title'] = $context['forum_name'];

	if (isset($context['page_title_html_safe']))
		$context['page_title_html_safe'] = $smcFunc['htmlspecialchars'](un_htmlspecialchars($context['page_title']));

	if (!empty($context['standalone']))
		setupMenuContext();

	$actions = array(
		'addarticle' => array('PortalArticles.php', 'sportal_add_article'),
		'articles' => array('PortalArticles.php', 'sportal_articles'),
		'credits' => array('', 'sportal_credits'),
		'pages' => array('PortalPages.php', 'sportal_pages'),
		'removearticle' => array('PortalArticles.php', 'sportal_remove_article'),
		'shoutbox' => array('PortalShoutbox.php', 'sportal_shoutbox'),
	);

	if (!isset($_REQUEST['sa']) || !isset($actions[$_REQUEST['sa']]))
		$_REQUEST['sa'] = 'articles';

	if (!empty($actions[$_REQUEST['sa']][0]))
		require_once($sourcedir . '/' . $actions[$_REQUEST['sa']][0]);

	$actions[$_REQUEST['sa']][1]();
}

function sportal_credits()
{
	global $sourcedir, $context, $txt;

	require_once($sourcedir . '/PortalAdminMain.php');
	loadLanguage('SPortalAdmin', sp_languageSelect('SPortalAdmin'));

	sportal_information(false);

	$context['page_title'] = $txt['sp-info_title'];
	$context['sub_template'] = 'information';
}

?>