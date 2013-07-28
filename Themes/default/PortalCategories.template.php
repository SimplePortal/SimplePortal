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

	if (empty($context['SPortal']['categories']))
	{
		echo '
		<div class="windowbg2">
			<span class="topslice"><span></span></span>
			<div class="sp_content_padding">', $txt['error_sp_no_categories'], '</div>
			<span class="botslice"><span></span></span>
		</div>';
	}

	foreach ($context['SPortal']['categories'] as $category)
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

?>