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

function template_view_articles()
{
	global $context, $scripturl, $txt;

	echo '
	<div id="sp_view_articles">
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

	if (!empty($context['page_index']))
	{
		echo '
		<div class="sp_page_index">';

		if (isset($context['previous_start']))
		{
			echo '
			<a class="sp_previous_start" href="', $scripturl . '?action=portal;sa=articles;start=', $context['previous_start'], '">', $txt['previous_next_back'], '</a>';
		}

		if (isset($context['next_start']))
		{
			echo '
			<a class="sp_next_start" href="', $scripturl . '?action=portal;sa=articles;start=', $context['next_start'], '">', $txt['previous_next_forward'], '</a>';
		}

		echo '
			', $txt['pages'], ': ', $context['page_index'], '
		</div>';
	}

	echo '
	</div>';
}

function template_view_article()
{
	global $context, $txt;

	echo '
	<div id="sp_view_article">';

	if (empty($context['article']['style']['no_title']))
	{
		echo '
		<div', strpos($context['article']['style']['title']['class'], 'custom') === false ? ' class="' . (strpos($context['article']['style']['title']['class'], 'titlebg') !== false ? 'title_bar' : 'cat_bar') . '"' : '', !empty($context['article']['style']['title']['style']) ? ' style="' . $context['article']['style']['title']['style'] . '"' : '', '>
			<h3 class="', $context['article']['style']['title']['class'], '">
				', $context['article']['title'], '
			</h3>
		</div>';
	}

	if (strpos($context['article']['style']['body']['class'], 'roundframe') !== false)
	{
		echo '
		<span class="upperframe"><span></span></span>';
	}

	echo '
		<div class="', $context['article']['style']['body']['class'], '">';

	if (empty($context['article']['style']['no_body']))
	{
		echo '
			<span class="topslice"><span></span></span>';
	}

	echo '
			<div class="sp_content_padding"', !empty($context['article']['style']['body']['style']) ? ' style="' . $context['article']['style']['body']['style'] . '"' : '', '>
				<div class="sp_article_detail">';

		if (!empty($context['article']['author']['avatar']['image']))
			echo $context['article']['author']['avatar']['image'];

		echo '
					<span>
						', sprintf($txt['sp_posted_in_on_by'], $context['article']['category']['link'], $context['article']['date'], $context['article']['author']['link']);

		if (!empty($context['article']['author']['avatar']['image']))
			echo '
						<br />';
		else
			echo '
					</span>
					<span class="sp_float_right">';

		echo '
						', sprintf($context['article']['views'] == 1 ? $txt['sp_viewed_time'] : $txt['sp_viewed_times'], $context['article']['views']) ,', ', sprintf($context['article']['comments'] == 1 ? $txt['sp_commented_on_time'] : $txt['sp_commented_on_times'], $context['article']['comments']), '
					</span>
				</div>
				<hr />
				<div>';

	sportal_parse_content($context['article']['body'], $context['article']['type']);

	echo '
				</div>
			</div>';

	if (empty($context['article']['style']['no_body']))
	{
		echo '
			<span class="botslice"><span></span></span>';
	}

	echo '
		</div>';

	if (strpos($context['article']['style']['body']['class'], 'roundframe') !== false)
	{
		echo '
		<span class="lowerframe"><span></span></span>';
	}

	if (empty($context['preview']))
	{
		echo '
		<div id="sp_view_comments">
			<div class="cat_bar">
				<h3 class="catbg">
					', $txt['sp-comments'], '
				</h3>
			</div>';

		if (empty($context['article']['comments']))
		{
			echo '
			<div class="windowbg">
				<span class="topslice"><span></span></span>
				<div class="sp_content_padding">
					', $txt['error_sp_no_comments'], '
				</div>
				<span class="botslice"><span></span></span>
			</div>';
		}

		foreach ($context['article']['comments'] as $comment)
		{
			echo '
			<div id="comment', $comment['id'], '" class="windowbg">
				<span class="topslice"><span></span></span>
				<div class="sp_content_padding flow_auto">
					<div class="sp_comment_detail">';

			if (!empty($comment['author']['avatar']['image']))
				echo $comment['author']['avatar']['image'];

			if ($comment['can_moderate'])
				echo '
						<div class="sp_float_right">
							<a href="', $context['article']['href'], ';modify=', $comment['id'], ';', $context['session_var'], '=', $context['session_id'], '">', sp_embed_image('modify'), '</a>
							<a href="', $context['article']['href'], ';delete=', $comment['id'], ';', $context['session_var'], '=', $context['session_id'], '">', sp_embed_image('delete'), '</a>
						</div>';

			echo '
						<span>', sprintf($txt['sp_posted_on_by'], $comment['time'], $comment['author']['link']), '</span>
					</div>
					<hr />
					<p>
						', $comment['body'], '
					</p>
				</div>
				<span class="botslice"><span></span></span>
			</div>';
		}

		if (!empty($context['page_index']))
		{
			echo '
			<div class="sp_page_index">';

			if (isset($context['previous_start']))
			{
				echo '
				<a class="sp_previous_start" href="', $context['article']['href'], ';comments=', $context['previous_start'], '">', $txt['previous_next_back'], '</a>';
			}

			if (isset($context['next_start']))
			{
				echo '
				<a class="sp_next_start" href="', $context['article']['href'], ';comments=', $context['next_start'], '">', $txt['previous_next_forward'], '</a>';
			}

			echo '
				', $txt['pages'], ': ', $context['page_index'], '
			</div>';
		}

		if ($context['article']['can_comment'])
		{
			echo '
			<div class="windowbg2">
				<span class="topslice"><span></span></span>
				<div class="sp_content_padding">
					<form action="', $context['article']['href'], '" method="post" accept-charset="', $context['character_set'], '">
						<textarea name="body" rows="5" cols="50" style="', $context['browser']['is_ie8'] ? 'width: 635px; max-width: 99%; min-width: 99%' : 'width: 99%', ';">', !empty($context['article']['comment']['body']) ? $context['article']['comment']['body'] : '', '</textarea>
						<div class="sp_center">
							<input type="submit" name="submit" value="', !empty($context['article']['comment']) ? $txt['sp_modify'] : $txt['sp_submit'], '" class="button_submit" />
						</div>
						<input type="hidden" name="comment" value="', !empty($context['article']['comment']['id']) ? $context['article']['comment']['id'] : 0, '" />
						<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
					</form>
				</div>
				<span class="botslice"><span></span></span>
			</div>';
		}

		echo '
		</div>';
	}

	echo '
	</div>';
}