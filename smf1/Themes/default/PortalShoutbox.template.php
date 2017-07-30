<?php

/**
 * @package SimplePortal
 *
 * @author SimplePortal Team
 * @copyright 2014 SimplePortal Team
 * @license BSD 3-clause
 *
 * @version 2.3.7
 */

function template_shoutbox_all()
{
	global $context, $scripturl, $settings, $txt;

	echo '
	<div class="tborder">
		<div class="shoutbox_container">
			<div class="catbg shoutbox_padding">
					', $context['SPortal']['shoutbox']['name'], '
			</div>
			<div class="shoutbox_page_index windowbg smalltext">
					', $txt[139], ': ', $context['page_index'], '
			</div>
			<div class="windowbg shoutbox_body">
				<ul class="shoutbox_list_all" id="shouts">';

	if (!empty($context['SPortal']['shouts_history']))
		foreach ($context['SPortal']['shouts_history'] as $shout)
			echo '
					', !$shout['is_me'] ? '<li class="smalltext"><strong>' . $shout['author']['link'] . ':</strong></li>' : '', '
					<li class="smalltext">', str_replace('ignored_shout', 'history_ignored_shout', $shout['text']), '</li>
					<li class="smalltext shoutbox_time">', $shout['delete_link'], $shout['time'], '</li>';
	else
			echo '
					<li class="smalltext">', $txt['sp_shoutbox_no_shout'], '</li>';

	echo '
				</ul>
			</div>
			<div class="shoutbox_page_index windowbg smalltext">
					', $txt[139], ': ', $context['page_index'], '
			</div>
		</div>
	</div>';
}

function template_shoutbox_embed($shoutbox)
{
	global $context, $scripturl, $settings, $txt;

	echo '
	<form action="" method="post">
		<div class="shoutbox_container">
			<div class="shoutbox_info">
				<div id="shoutbox_load_', $shoutbox['id'], '" style="float: right; display: none;"><img src="', $settings['sp_images_url'], '/loading.gif" alt="" /></div>
				<a href="', $scripturl, '?action=portal;sa=shoutbox;shoutbox_id=', $shoutbox['id'], '" onclick="sp_refresh_shout(', $shoutbox['id'], ', last_refresh_', $shoutbox['id'], '); return false;">', sp_embed_image('refresh'), '</a> <a href="', $scripturl, '?action=portal;sa=shoutbox;shoutbox_id=', $shoutbox['id'], '">', sp_embed_image('history'), '</a>';

	if ($context['can_shout'])
		echo ' <a href="#smiley" onclick="sp_collapse_object(\'sb_smiley_', $shoutbox['id'], '\', false); return false;">', sp_embed_image('smiley'), '</a> <a href="#style" onclick="sp_collapse_object(\'sb_style_', $shoutbox['id'], '\', false); return false;">', sp_embed_image('style'), '</a>';

	echo '
			</div>';

	if ($context['can_shout'])
	{
		echo '
			<div id="sp_object_sb_smiley_', $shoutbox['id'], '" style="display: none;">';

		foreach ($shoutbox['smileys']['normal'] as $smiley)
			echo '
				<a href="javascript:void(0);" onclick="replaceText(\' ', $smiley['code'], '\', document.getElementById(\'new_shout_', $shoutbox['id'], '\')); return false;"><img src="', $settings['smileys_url'], '/', $smiley['filename'], '" alt="', $smiley['description'], '" title="', $smiley['description'], '" /></a>';

		if (!empty($shoutbox['smileys']['popup']))
			echo '
				<a onclick="sp_compat_showMoreSmileys(\'', $shoutbox['id'], '\', \'', $txt['more_smileys_title'], '\', \'', $txt['more_smileys_pick'], '\', \'', $txt['more_smileys_close_window'], '\', \'', $settings['theme_url'], '\', \'', $settings['smileys_url'], '\'); return false;" href="javascript:void(0);">[', $txt['more_smileys'], ']</a>';

		echo '
			</div>
			<div id="sp_object_sb_style_', $shoutbox['id'], '" style="display: none;">';

		foreach ($shoutbox['bbc'] as $image => $tag)
		{
			if (!in_array($tag['code'], $shoutbox['allowed_bbc']))
				continue;

			if (!isset($tag['after']))
				echo '<a href="javascript:void(0);" onclick="replaceText(\'', $tag['before'], '\', document.getElementById(\'new_shout_', $shoutbox['id'], '\')); return false;">';
			else
				echo '<a href="javascript:void(0);" onclick="surroundText(\'', $tag['before'], '\', \'', $tag['after'], '\', document.getElementById(\'new_shout_', $shoutbox['id'], '\')); return false;">';

			echo '<img onmouseover="style_highlight(this, true);" onmouseout="if (window.style_highlight) style_highlight(this, false);" src="', $settings['images_url'], '/bbc/', $image, '.gif" align="bottom" width="23" height="22" alt="', $tag['description'], '" title="', $tag['description'], '" style="background-image: url(', $settings['images_url'], '/bbc/bbc_bg.gif); margin: 1px 2px 1px 1px;" /></a>';
		}

		echo '
			</div>';
	}

	echo '
			<div class="shoutbox_body">
				<ul class="shoutbox_list_compact" id="shouts_', $shoutbox['id'], '"', !empty($shoutbox['height']) ? ' style="height: ' . $shoutbox['height'] . 'px;"' : '', '>';

	if (!empty($shoutbox['warning']))
		echo '
					<li class="shoutbox_warning smalltext">', $shoutbox['warning'], '</li>';

	if (!empty($shoutbox['shouts']))
		foreach ($shoutbox['shouts'] as $shout)
			echo '
					<li class="smalltext">', !$shout['is_me'] ? '<strong>' . $shout['author']['link'] . ':</strong> ' : '', $shout['text'], '<br />', !empty($shout['delete_link_js']) ? '<span class="shoutbox_delete">' . $shout['delete_link_js'] . '</span>' : '' , '<span class="smalltext shoutbox_time">', $shout['time'], '</span></li>';
	else
			echo '
					<li class="smalltext">', $txt['sp_shoutbox_no_shout'], '</li>';

	echo '
				</ul>
			</div>';

	if ($context['can_shout'])
		echo '
			<div class="shoutbox_input smalltext">
				<input type="text" name="new_shout" id="new_shout_', $shoutbox['id'], '" class="shoutbox_input sp_float_left"', $context['browser']['is_ie'] ? ' onkeypress="if (sp_catch_enter(event)) { sp_submit_shout(' . $shoutbox['id'] . ', \'sc\', \'' . $context['session_id'] . '\'); return false; }"' : '', ' />
				<input type="submit" name="submit_shout" value="', $txt['sp_shoutbox_button'], '" class="sp_float_right" onclick="sp_submit_shout(', $shoutbox['id'], ', \'sc\', \'', $context['session_id'], '\'); return false;" />
			</div>';

	echo '
		</div>
		<input type="hidden" name="shoutbox_id" value="', $shoutbox['id'], '" />
		<input type="hidden" name="sc" value="', $context['session_id'], '" />
	</form>
	<script language="Javascript" type="text/javascript"><!-- // --><![CDATA[
		var last_refresh_', $shoutbox['id'], ' = ', time(), ';';

	if ($shoutbox['reverse'])
		echo '
		var objDiv = document.getElementById("shouts_', $shoutbox['id'], '");
		objDiv.scrollTop = objDiv.scrollHeight;'; 

	if (!empty($shoutbox['refresh']))
		echo '
			var interval_id_', $shoutbox['id'], ' = setInterval( "sp_auto_refresh_', $shoutbox['id'], '()", ', $shoutbox['refresh'], ' * 1000);
			function sp_auto_refresh_', $shoutbox['id'], '()
			{
				if (window.XMLHttpRequest)
				{
					sp_refresh_shout(', $shoutbox['id'], ', last_refresh_', $shoutbox['id'], ');
					last_refresh_', $shoutbox['id'], ' += ', $shoutbox['refresh'], ';
				}
				else
					clearInterval(interval_id_', $shoutbox['id'], ');
			}';

	// Setup the data for the popup smileys.
	if (!empty($shoutbox['smileys']['popup']))
	{
		echo '
		if (sp_smileys == undefined)
			var sp_smileys = [';
		foreach ($shoutbox['smileys']['popup'] as $smiley)
		{
			echo '
					["', $smiley['js_code'], '","', $smiley['filename'], '","', $smiley['js_description'], '"]';
			if (empty($smiley['last']))
				echo ',';
		}
		echo ']';
	}

	echo '
	// ]]></script>';
}

function template_shoutbox_xml()
{
	global $context, $txt, $func;

	echo '<', '?xml version="1.0" encoding="', $context['character_set'], '"?', '>
<smf>
	<shoutbox>', $context['SPortal']['shoutbox']['id'], '</shoutbox>';

	if ($context['SPortal']['updated'])
	{
		echo '
	<updated>1</updated>
	<error>', empty($context['SPortal']['shouts']) ? $func['htmlspecialchars']($txt['sp_shoutbox_no_shout']) : 0, '</error>
	<warning>', !empty($context['SPortal']['shoutbox']['warning']) ? $func['htmlspecialchars']($context['SPortal']['shoutbox']['warning']) : 0, '</warning>
	<reverse>', !empty($context['SPortal']['shoutbox']['reverse']) ? 1 : 0, '</reverse>';

	foreach ($context['SPortal']['shouts'] as $shout)
		echo '
	<shout>
		<id>', $shout['id'], '</id>
		<author>', $func['htmlspecialchars']($shout['author']['link']), '</author>
		<time>', $func['htmlspecialchars']($shout['time']), '</time>
		<timeclean>', $func['htmlspecialchars'](strip_tags($shout['time'])), '</timeclean>
		<delete>', !empty($shout['delete_link_js']) ? $func['htmlspecialchars']($shout['delete_link_js']) : 0, '</delete>
		<content>', $func['htmlspecialchars']($shout['text']), '</content>
		<is_me>', $shout['is_me'] ? 1 : 0, '</is_me>
	</shout>';
	}
	else
		echo '
	<updated>0</updated>';

	echo '
</smf>';
}

?>