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

function template_view_categories()
{
	global $context, $txt;

	echo '
	<div id="sp_view_categories">
		<div class="cat_bar">
			<h3 class="catbg">
				', $context['page_title'], '
			</h3>
		</div>';

	if (empty($context['categories']))
	{
		echo '
		<div class="windowbg2">
			<span class="topslice"><span></span></span>
			<div class="sp_content_padding">', $txt['error_sp_no_categories'], '</div>
			<span class="botslice"><span></span></span>
		</div>';
	}

	foreach ($context['categories'] as $category)
	{
		echo '
		<div class="windowbg2">
			<span class="topslice"><span></span></span>
			<div class="sp_content_padding">
				<h4>', $category['link'], '</h4>
				<p>', $category['description'], '</p>
				<span>', sprintf($category['articles'] == 1 ? $txt['sp_has_article'] : $txt['sp_has_articles'], $category['articles']) ,'</span>
			</div>
			<span class="botslice"><span></span></span>
		</div>';
	}

	echo '
	</div>';
}

function template_view_category()
{
	global $context, $txt;

	echo '
	<div id="sp_view_category">
		<div class="cat_bar">
			<h3 class="catbg">
				', $context['page_title'], '
			</h3>
		</div>';

	if (empty($context['articles']))
	{
		echo '
		<div class="windowbg2">
			<span class="topslice"><span></span></span>
			<div class="sp_content_padding">', $txt['error_sp_no_articles'], '</div>
			<span class="botslice"><span></span></span>
		</div>';
	}

	foreach ($context['articles'] as $article)
	{
		echo '
		<div class="windowbg2">
			<span class="topslice"><span></span></span>
			<div class="sp_content_padding">
				<div class="sp_article_detail">';

		if (!empty($article['author']['avatar']['image']))
			echo $article['author']['avatar']['image'];

		echo '
					<span style="text-align: right; float: right;">
						', sprintf($txt['sp_posted_in_on_by'], $article['category']['link'], $article['date'], $article['author']['link']), '
						<br />
						', sprintf($article['views'] == 1 ? $txt['sp_viewed_time'] : $txt['sp_viewed_times'], $article['views']) ,', ', sprintf($article['comments'] == 1 ? $txt['sp_commented_on_time'] : $txt['sp_commented_on_times'], $article['comments']), '
					</span>
					<h4>', $article['link'], '</h4>
				</div>
				<hr />
				<p>', $article['preview'], '<a href="', $article['href'], '">...</a></p>
				<div class="sp_article_extra">
					<a href="', $article['href'], '">', $txt['sp_read_more'], '</a> | <a href="', $article['href'], '#sp_view_comments">', $txt['sp_write_comment'], '</a>
				</div>
			</div>
			<span class="botslice"><span></span></span>
		</div>';
	}

	echo '
	</div>';
}