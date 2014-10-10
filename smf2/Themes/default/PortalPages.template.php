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

function template_view_pages()
{
	global $context, $txt;

	echo '
	<div id="sp_view_pages">
		<div class="cat_bar">
			<h3 class="catbg">
				', $context['page_title'], '
			</h3>
		</div>';

	if (empty($context['SPortal']['pages']))
	{
		echo '
		<div class="windowbg2">
			<span class="topslice"><span></span></span>
			<div class="sp_content_padding">', $txt['error_sp_no_pages'], '</div>
			<span class="botslice"><span></span></span>
		</div>';
	}

	foreach ($context['SPortal']['pages'] as $page)
	{
		echo '
		<div class="windowbg2">
			<span class="topslice"><span></span></span>
			<div class="sp_content_padding">', $page['link'], ' - ', sprintf($page['views'] == 1 ? $txt['sp_viewed_time'] : $txt['sp_viewed_times'], $page['views']) ,'</div>
			<span class="botslice"><span></span></span>
		</div>';
	}

	echo '
	</div>';
}

function template_view_page()
{
	global $context;

	if ($context['SPortal']['core_compat'])
		template_view_page_core();
	else
		template_view_page_curve();
}

function template_view_page_core()
{
	global $context;

	echo '
				<div', !empty($context['SPortal']['page']['style']['no_body']) ? '' : ' class="tborder"', '>
					<table class="sp_block">';

	if (empty($context['SPortal']['page']['style']['no_title']))
		echo '
						<tr>
							<td class="sp_block_padding ', $context['SPortal']['page']['style']['title']['class'], '"', !empty($context['SPortal']['page']['style']['title']['style']) ? ' style="' . $context['SPortal']['page']['style']['title']['style'] . '"' : '', '>
								', $context['SPortal']['page']['title'], '
							</td>
						</tr>';

	echo '
						<tr>
							<td class="sp_block_padding', empty($context['SPortal']['page']['style']['body']['class']) ? '' : ' ' . $context['SPortal']['page']['style']['body']['class'], '"', !empty($context['SPortal']['page']['style']['body']['style']) ? ' style="' . $context['SPortal']['page']['style']['body']['style'] . '"' : '', '>';

	sportal_parse_page($context['SPortal']['page']['body'], $context['SPortal']['page']['type']);

	echo '
							</td>
						</tr>
					</table>
				</div>';
}

function template_view_page_curve()
{
	global $context;

	if (empty($context['SPortal']['page']['style']['no_title']))
	{
		echo '
			<div class="', in_array($context['SPortal']['page']['style']['title']['class'], array('titlebg', 'titlebg2')) ? 'title_bar' : 'cat_bar', '"', !empty($context['SPortal']['page']['style']['title']['style']) ? ' style="' . $context['SPortal']['page']['style']['title']['style'] . '"' : '', '>
				<h3 class="', $context['SPortal']['page']['style']['title']['class'], '">
					', $context['SPortal']['page']['title'], '
				</h3>
			</div>';
	}

	if (strpos($context['SPortal']['page']['style']['body']['class'], 'roundframe') !== false)
	{
		echo '
				<span class="upperframe"><span></span></span>';
	}

	echo '
				<div class="', $context['SPortal']['page']['style']['body']['class'], '">';

	if (empty($context['SPortal']['page']['style']['no_body']))
	{
		echo '
					<span class="topslice"><span></span></span>';
	}

	echo '
					<div class="sp_content_padding"', !empty($context['SPortal']['page']['style']['body']['style']) ? ' style="' . $context['SPortal']['page']['style']['body']['style'] . '"' : '', '>';

	sportal_parse_content($context['SPortal']['page']['body'], $context['SPortal']['page']['type']);

	echo '
					</div>';

	if (empty($context['SPortal']['page']['style']['no_body']))
	{
		echo '
					<span class="botslice"><span></span></span>';
	}

	echo '
				</div>';

	if (strpos($context['SPortal']['page']['style']['body']['class'], 'roundframe') !== false)
	{
		echo '
				<span class="lowerframe"><span></span></span>';
	}
}

?>