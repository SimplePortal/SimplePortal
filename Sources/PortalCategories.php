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
	global $smcFunc, $context, $scripturl, $txt;

	loadTemplate('PortalCategories');

	$category_id = !empty($_REQUEST['category']) ? $_REQUEST['category'] : 0;

	if (is_int($category_id))
		$category_id = (int) $category_id;
	else
		$category_id = $smcFunc['htmlspecialchars']($category_id, ENT_QUOTES);

	$context['category'] = sportal_get_categories($category_id, true, true);

	if (empty($context['category']['id']))
		fatal_lang_error('error_sp_category_not_found', false);

	$context['articles'] = sportal_get_articles(0, true, true, 'spa.id_article DESC', $context['category']['id']);

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