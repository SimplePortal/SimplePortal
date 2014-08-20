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

function template_portal_index()
{
	global $context, $txt;

	if (empty($context['articles']))
		return;

	echo '
	<div id="sp_index">';

	foreach ($context['articles'] as $article)
	{
		echo '
		<div class="cat_bar">
			<h3 class="catbg">
				', $article['link'], '
			</h3>
		</div>
		<div class="windowbg2">
			<span class="topslice"><span></span></span>
			<div class="sp_content_padding">
				<div class="sp_article_detail">';

		if (!empty($article['author']['avatar']['image']))
			echo $article['author']['avatar']['image'];

		echo '
					<span>
						', sprintf($txt['sp_posted_in_on_by'], $article['category']['link'], $article['date'], $article['author']['link']);

		if (!empty($article['author']['avatar']['image']))
			echo '
						<br />';
		else
			echo '
					</span>
					<span class="sp_float_right">';

		echo '
						', sprintf($article['views'] == 1 ? $txt['sp_viewed_time'] : $txt['sp_viewed_times'], $article['views']) ,', ', sprintf($article['comments'] == 1 ? $txt['sp_commented_on_time'] : $txt['sp_commented_on_times'], $article['comments']), '
					</span>
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
			<a class="sp_previous_start" href="', $context['portal_url'], '?articles=', $context['previous_start'], '">', $txt['previous_next_back'], '</a>';
		}

		if (isset($context['next_start']))
		{
			echo '
			<a class="sp_next_start" href="', $context['portal_url'], '?articles=', $context['next_start'], '">', $txt['previous_next_forward'], '</a>';
		}

		echo '
			', $txt['pages'], ': ', $context['page_index'], '
		</div>';
	}

	echo '
	</div>';
}

function template_portal_above()
{
	global $context, $modSettings;

	if (empty($modSettings['sp_disable_side_collapse']) && ($context['SPortal']['sides'][1]['active'] || $context['SPortal']['sides'][4]['active']))
	{
		echo '
	<div class="sp_right sp_fullwidth">';

		if ($context['SPortal']['sides'][1]['active'])
			echo '
		<a href="#side" onclick="return sp_collapseSide(1)">', sp_embed_image($context['SPortal']['sides'][1]['collapsed'] ? 'expand' : 'collapse', '', null, null, true, 'sp_collapse_side1'), '</a>';

		if ($context['SPortal']['sides'][4]['active'])
			echo '
		<a href="#side" onclick="return sp_collapseSide(4)">', sp_embed_image($context['SPortal']['sides'][4]['collapsed'] ? 'expand' : 'collapse', '', null, null, true, 'sp_collapse_side4'), '</a>';

		echo '
	</div>';
	}

	if (!empty($context['SPortal']['blocks'][5]))
	{
		echo '
	<div id="sp_header">';

		foreach ($context['SPortal']['blocks'][5] as $block)
			template_block($block);

		echo '
	</div>';
	}

	echo '
	<table id="sp_main">
		<tr>';

	if (!empty($modSettings['showleft']) && !empty($context['SPortal']['blocks'][1]))
	{
		echo '
			<td id="sp_left"', !empty($modSettings['leftwidth']) ? ' width="' . $modSettings['leftwidth'] . '"' : '', $context['SPortal']['sides'][1]['collapsed'] && empty($modSettings['sp_disable_side_collapse']) ? ' style="display: none;"' : '', '>';

		foreach ($context['SPortal']['blocks'][1] as $block)
			template_block($block);

		echo '
			</td>';
	}

	echo '
			<td id="sp_center">';

	if (!empty($context['SPortal']['blocks'][2]))
	{
		foreach ($context['SPortal']['blocks'][2] as $block)
			template_block($block);

		if (empty($context['SPortal']['on_portal']))
			echo '
				<br class="sp_side_clear" />';
	}
}

function template_portal_below()
{
	global $context, $modSettings;

	if (!empty($context['SPortal']['blocks'][3]))
	{
		if (empty($context['SPortal']['on_portal']) || !empty($context['SPortal']['blocks'][2]) || !empty($modSettings['articleactive']))
			echo '
				<br class="sp_side_clear" />';

		foreach ($context['SPortal']['blocks'][3] as $block)
			template_block($block);
	}

	echo '
			</td>';

	if (!empty($modSettings['showright']) && !empty($context['SPortal']['blocks'][4]))
	{
		echo '
			<td id="sp_right"', !empty($modSettings['rightwidth']) ? ' width="' . $modSettings['rightwidth'] . '"' : '', $context['SPortal']['sides'][4]['collapsed'] && empty($modSettings['sp_disable_side_collapse']) ? ' style="display: none;"' : '', '>';

		foreach ($context['SPortal']['blocks'][4] as $block)
			template_block($block);

		echo '
			</td>';
	}
	echo '
		</tr>
	</table>';

	if (!empty($context['SPortal']['blocks'][6]))
	{
		echo '
	<div id="sp_footer">';

		foreach ($context['SPortal']['blocks'][6] as $block)
			template_block($block);

		echo '
	</div>
	<br />';
	}
}

function template_block($block)
{
	global $context, $modSettings, $txt;

	if (empty($block) || empty($block['type']))
		return;

	if ($block['type'] == 'sp_boardNews')
	{
		echo '
			<div class="sp_block_section', isset($context['SPortal']['sides'][$block['column']]['last']) && $context['SPortal']['sides'][$block['column']]['last'] == $block['id'] && ($block['column'] != 2 || empty($modSettings['articleactive'])) ? '_last' : '', '">';

		$block['type']($block['parameters'], $block['id']);

		echo '
			</div>';

		return;
	}

	if (isset($txt['sp_custom_block_title_' . $block['id']]))
		$block['label'] = $txt['sp_custom_block_title_' . $block['id']];

	if ($context['SPortal']['core_compat'])
		template_block_core($block);
	else
		template_block_curve($block);
}

function template_block_core($block)
{
	global $context, $modSettings, $settings;

	echo '
			<div class="sp_block_section', isset($context['SPortal']['sides'][$block['column']]['last']) && $context['SPortal']['sides'][$block['column']]['last'] == $block['id'] && ($block['column'] != 2 || empty($modSettings['articleactive'])) ? '_last' : '', '">
				<div class="', !empty($block['style']['no_body']) ? '' : ' tborder', '">
					<table class="sp_block">';

	if (empty($block['style']['no_title']))
	{
		echo '
						<tr>
							<td class="sp_block_padding ', $block['style']['title']['class'], '"', !empty($block['style']['title']['style']) ? ' style="' . $block['style']['title']['style'] . '"' : '', '>';

		if (empty($block['force_view']))
			echo '
								<a class="sp_float_right" href="javascript:void(0);" onclick="sp_collapseBlock(\'', $block['id'], '\')"><img id="sp_collapse_', $block['id'], '" src="', $settings['images_url'], $block['collapsed'] ? '/expand.gif' : '/collapse.gif', '" alt="*" /></a>';

		echo '
								', parse_bbc($block['label']), '
							</td>
						</tr>';
	}

	echo '
						<tr', (empty($block['force_view']) ? ' id="sp_block_' . $block['id'] . '"' : '') , $block['collapsed'] && empty($block['force_view']) ? ' style="display: none;"' : '', '>
							<td class="sp_block_padding', ($block['type'] == 'sp_menu') ? '' : ' sp_block', empty($block['style']['body']['class']) ? '' : ' ' . $block['style']['body']['class'], '"', !empty($block['style']['body']['style']) ? ' style="' . $block['style']['body']['style'] . '"' : '', '>';

	$block['type']($block['parameters'], $block['id']);

	echo '
							</td>
						</tr>
					</table>
				</div>
			</div>';
}

function template_block_curve($block)
{
	global $context, $modSettings, $settings;

	if (empty($block['style']['no_title']))
	{
		echo '
	<div class="', in_array($block['style']['title']['class'], array('titlebg', 'titlebg2')) ? 'title_bar' : 'cat_bar', '"', !empty($block['style']['title']['style']) ? ' style="' . $block['style']['title']['style'] . '"' : '', '>
		<h3 class="', $block['style']['title']['class'], '">';

		if (empty($block['force_view']))
			echo '
			<a class="sp_float_right" style="padding-top: 7px;" href="javascript:void(0);" onclick="sp_collapseBlock(\'', $block['id'], '\')"><img id="sp_collapse_', $block['id'], '" src="', $settings['images_url'], $block['collapsed'] ? '/expand.gif' : '/collapse.gif', '" alt="*" /></a>';

		echo '
			', parse_bbc($block['label']), '
		</h3>
	</div>';
	}

	echo '
	<div id="sp_block_' . $block['id'] . '" class="sp_block_section', isset($context['SPortal']['sides'][$block['column']]['last']) && $context['SPortal']['sides'][$block['column']]['last'] == $block['id'] && ($block['column'] != 2 || empty($modSettings['articleactive'])) ? '_last' : '', '" ', $block['collapsed'] && empty($block['force_view']) ? ' style="display: none;"' : '', '>';

	if (strpos($block['style']['body']['class'], 'roundframe') !== false)
	{
		echo '
		<span class="upperframe"><span></span></span>';
	}

	echo '
		<div', empty($block['style']['body']['class']) ? '' : ' class="' . $block['style']['body']['class'] . '"', '>'; 

	if (empty($block['style']['no_body']))
	{
		echo '
			<span class="topslice"><span></span></span>';
	}

	echo '
			<div class="', $block['type'] != 'sp_menu' ? 'sp_block' : 'sp_content_padding', '"', !empty($block['style']['body']['style']) ? ' style="' . $block['style']['body']['style'] . '"' : '', '>';

	$block['type']($block['parameters'], $block['id']);

	echo '
			</div>';

	if (empty($block['style']['no_body']))
	{
		echo '
			<span class="botslice"><span></span></span>';
	}

	echo '
		</div>';

	if (strpos($block['style']['body']['class'], 'roundframe') !== false)
	{
		echo '
		<span class="lowerframe"><span></span></span>';
	}

	echo '
	</div>';
}