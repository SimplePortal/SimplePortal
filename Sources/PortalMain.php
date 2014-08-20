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
 * Entry point for SimplePortal
 * Passes off processing to the appropriate portal file/function
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
		'articles' => array('PortalArticles.php', 'sportal_articles'),
		'categories' => array('PortalCategories.php', 'sportal_categories'),
		'credits' => array('', 'sportal_credits'),
		'index' => array('', 'sportal_index'),
		'pages' => array('PortalPages.php', 'sportal_pages'),
		'shoutbox' => array('PortalShoutbox.php', 'sportal_shoutbox'),
	);

	if (!isset($_REQUEST['sa']) || !isset($actions[$_REQUEST['sa']]))
		$_REQUEST['sa'] = 'index';

	if (!empty($actions[$_REQUEST['sa']][0]))
		require_once($sourcedir . '/' . $actions[$_REQUEST['sa']][0]);

	$actions[$_REQUEST['sa']][1]();
}

/**
 * Loads article preview for display with the portal index template
 */
function sportal_index()
{
	global $smcFunc, $context, $scripturl, $modSettings, $txt;

	$context['sub_template'] = 'portal_index';

	if (empty($modSettings['sp_articles_index']))
		return;

	$request = $smcFunc['db_query']('','
		SELECT COUNT(*)
		FROM {db_prefix}sp_articles AS spa
			INNER JOIN {db_prefix}sp_categories AS spc ON (spc.id_category = spa.id_category)
		WHERE spa.status = {int:article_status}
			AND spc.status = {int:category_status}
			AND {raw:article_permissions}
			AND {raw:category_permissions}
		LIMIT {int:limit}',
		array(
			'article_status' => 1,
			'category_status' => 1,
			'article_permissions' => sprintf($context['SPortal']['permissions']['query'], 'spa.permissions'),
			'category_permissions' => sprintf($context['SPortal']['permissions']['query'], 'spc.permissions'),
			'limit' => 1,
		)
	);
	list ($total_articles) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	$total = min($total_articles, !empty($modSettings['sp_articles_index_total']) ? $modSettings['sp_articles_index_total'] : 20);
	$per_page = min($total, !empty($modSettings['sp_articles_index_per_page']) ? $modSettings['sp_articles_index_per_page'] : 5);
	$start = !empty($_REQUEST['articles']) ? (int) $_REQUEST['articles'] : 0;
	$total_pages = $per_page > 0 ? ceil($total / $per_page) : 0;
	$current_page = $per_page > 0 ? ceil($start / $per_page) : 0;

	if ($total > $per_page)
	{
		$context['page_index'] = constructPageIndex($context['portal_url'] . '?articles=%1$d', $start, $total, $per_page, true);

		if ($current_page > 0)
			$context['previous_start'] = ($current_page - 1) * $per_page;
		if ($current_page < $total_pages - 1)
			$context['next_start'] = ($current_page + 1) * $per_page;
	}

	$context['articles'] = sportal_get_articles(0, true, true, 'spa.id_article DESC', 0, $per_page, $start);

	foreach ($context['articles'] as $article)
	{
		if (($cutoff = $smcFunc['strpos']($article['body'], '[cutoff]')) !== false)
			$article['body'] = $smcFunc['substr']($article['body'], 0, $cutoff);

		$context['articles'][$article['id']]['preview'] = parse_bbc($article['body']);
		$context['articles'][$article['id']]['date'] = timeformat($article['date']);
	}
}

/**
 * Displays the credit page outside of the admin area
 */
function sportal_credits()
{
	global $sourcedir, $context, $txt;

	require_once($sourcedir . '/PortalAdminMain.php');
	loadLanguage('SPortalAdmin', sp_languageSelect('SPortalAdmin'));

	sportal_information(false);

	$context['page_title'] = $txt['sp-info_title'];
	$context['sub_template'] = 'information';
}