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
	global $context, $txt;

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
				<h4>', $article['link'], '</h4>
				<span>', sprintf($txt['sp_posted_in_on_by'], $article['category']['link'], $article['date'], $article['author']['link']), '</span>
				<p>', $article['preview'], '<a href="', $article['href'], '">...</a></p>
				<span>', sprintf($article['views'] == 1 ? $txt['sp_viewed_time'] : $txt['sp_viewed_times'], $article['views']) ,', ', sprintf($article['comments'] == 1 ? $txt['sp_commented_on_time'] : $txt['sp_commented_on_times'], $article['comments']), '</span>
			</div>
			<span class="botslice"><span></span></span>
		</div>';
	}

	echo '
	</div>';
}

function template_view_article()
{
	global $context, $txt;


	echo '
	<div id="sp_view_article">
		<div class="cat_bar">
			<h3 class="catbg">
				', $context['article']['title'], '
			</h3>
		</div>
		<div class="windowbg">
			<span class="topslice"><span></span></span>
			<div class="sp_content_padding">
				<span>', sprintf($txt['sp_posted_in_on_by'], $context['article']['category']['link'], $context['article']['date'], $context['article']['author']['link']), '</span>
				<div>';

	sportal_parse_content($context['article']['body'], $context['article']['type']);

	echo '
				</div>
			</div>
			<span class="botslice"><span></span></span>
		</div>';

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
					<span>', sprintf($txt['sp_posted_on_by'], $comment['time'], $comment['author']['link']), '</span>
					<div>
						', $comment['body'], '
					</div>';

			if ($comment['can_moderate'])
				echo '
					<div class="sp_float_right">
						<a href="', $context['article']['href'], ';modify=', $comment['id'], ';', $context['session_var'], '=', $context['session_id'], '">', sp_embed_image('modify'), '</a>
						<a href="', $context['article']['href'], ';delete=', $comment['id'], ';', $context['session_var'], '=', $context['session_id'], '">', sp_embed_image('delete'), '</a>
					</div>';

			echo '
				</div>
				<span class="botslice"><span></span></span>
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

?>