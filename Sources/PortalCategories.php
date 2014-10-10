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
 * Display a list of categories for selection
 */
function sportal_categories()
{
	global $context, $scripturl, $txt;

	loadTemplate('PortalCategories');

	$context['categories'] = sportal_get_categories(0, true, true);

	$context['linktree'][] = array(
		'url' => $scripturl . '?action=portal;sa=categories',
		'name' => $txt['sp-categories'],
	);

	$context['page_title'] = $txt['sp-categories'];
	$context['sub_template'] = 'view_categories';
}

/**
 * View a specific category, showing all articles it contains
 */
function sportal_category()
{
	global $smcFunc, $context, $scripturl, $modSettings, $txt;

	loadTemplate('PortalCategories');

	$category_id = !empty($_REQUEST['category']) ? $_REQUEST['category'] : 0;

	$context['category'] = sportal_get_categories($category_id, true, true);

	if (empty($context['category']['id']))
		fatal_lang_error('error_sp_category_not_found', false);

	$request = $smcFunc['db_query']('','
		SELECT COUNT(*)
		FROM {db_prefix}sp_articles
		WHERE status = {int:article_status}
			AND {raw:article_permissions}
			AND id_category = {int:current_category}
		LIMIT {int:limit}',
		array(
			'article_status' => 1,
			'article_permissions' => sprintf($context['SPortal']['permissions']['query'], 'permissions'),
			'current_category' => $context['category']['id'],
			'limit' => 1,
		)
	);
	list ($total_articles) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	$per_page = min($total_articles, !empty($modSettings['sp_articles_per_page']) ? $modSettings['sp_articles_per_page'] : 10);
	$start = !empty($_REQUEST['start']) ? (int) $_REQUEST['start'] : 0;
	$total_pages = $per_page > 0 ? ceil($total_articles / $per_page) : 0;
	$current_page = $per_page > 0 ? ceil($start / $per_page) : 0;

	if ($total_articles > $per_page)
	{
		$context['page_index'] = constructPageIndex($context['category']['href'] . ';start=%1$d', $start, $total_articles, $per_page, true);

		if ($current_page > 0)
			$context['previous_start'] = ($current_page - 1) * $per_page;
		if ($current_page < $total_pages - 1)
			$context['next_start'] = ($current_page + 1) * $per_page;
	}

	$context['articles'] = sportal_get_articles(0, true, true, 'spa.id_article DESC', $context['category']['id'], $per_page, $start);

	foreach ($context['articles'] as $article)
	{
		if (($cutoff = $smcFunc['strpos']($article['body'], '[cutoff]')) !== false)
			$article['body'] = $smcFunc['substr']($article['body'], 0, $cutoff);

		$context['articles'][$article['id']]['preview'] = parse_bbc($article['body']);
		$context['articles'][$article['id']]['date'] = timeformat($article['date']);
	}

	$context['linktree'][] = array(
		'url' => $scripturl . '?category=' . $context['category']['category_id'],
		'name' => $context['category']['name'],
	);

	$context['page_title'] = $context['category']['name'];
	$context['sub_template'] = 'view_category';
}