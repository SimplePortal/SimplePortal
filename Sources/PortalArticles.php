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

function sportal_articles()
{
	global $smcFunc, $context, $scripturl, $txt;

	loadTemplate('PortalArticles');

	$context['articles'] = sportal_get_articles(0, true, true, 'spa.id_article DESC');

	foreach ($context['articles'] as $article)
	{
		if (($cutoff = $smcFunc['strpos']($article['body'], '[cutoff]')) !== false)
			$article['body'] = $smcFunc['substr']($article['body'], 0, $cutoff);

		$context['articles'][$article['id']]['preview'] = parse_bbc($article['body']);
		$context['articles'][$article['id']]['date'] = timeformat($article['date']);
	}

	$context['linktree'][] = array(
		'url' => $scripturl . '?action=portal;sa=articles',
		'name' => $txt['sp-articles'],
	);

	$context['page_title'] = $txt['sp-articles'];
	$context['sub_template'] = 'view_articles';
}

function sportal_article()
{
	global $smcFunc, $context, $scripturl, $txt;

	loadTemplate('PortalArticles');

	$article_id = !empty($_REQUEST['article']) ? $_REQUEST['article'] : 0;

	if (is_int($article_id))
		$article_id = (int) $article_id;
	else
		$article_id = $smcFunc['htmlspecialchars']($article_id, ENT_QUOTES);

	$context['article'] = sportal_get_articles($article_id, true, true);

	if (empty($context['article']['id']))
		fatal_lang_error('error_sp_article_not_found', false);

	$context['article']['date'] = timeformat($context['article']['date']);
	
	if (empty($_SESSION['last_viewed_article']) || $_SESSION['last_viewed_article'] != $context['article']['id'])
	{
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}sp_articles
			SET views = views + 1
			WHERE id_article = {int:current_article}',
			array(
				'current_article' => $context['article']['id'],
			)
		);

		$_SESSION['last_viewed_article'] = $context['article']['id'];
	}

	$context['linktree'] = array_merge($context['linktree'], array(
		array(
			'url' => $scripturl . '?category=' . $context['article']['category']['category_id'],
			'name' => $context['article']['category']['name'],
		),
		array(
			'url' => $scripturl . '?article=' . $context['article']['article_id'],
			'name' => $context['article']['title'],
		)
	));

	$context['page_title'] = $context['article']['title'];
	$context['sub_template'] = 'view_article';
}

?>