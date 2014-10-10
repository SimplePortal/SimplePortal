<?php

/**
 * @package SimplePortal
 *
 * @author SimplePortal Team
 * @copyright 2014 SimplePortal Team
 * @license BSD 3-clause
 *
 * @version 2.3.6
 */

function template_pages_list()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	echo '
	<form action="', $scripturl, '?action=manageportal;area=portalpages;sa=list" method="post" accept-charset="', $context['character_set'], '" onsubmit="return confirm(\'', $txt['sp_pages_remove_confirm'], '\');">
		<table cellspacing="1" cellpadding="4" class="sp_fullwidth sp_auto_align bordercolor">
			<tr class="catbg3">
				<td colspan="', count($context['columns']) + 1, '"><strong>', $txt[139], ':</strong> ', $context['page_index'], '</td>
			</tr>
			<tr class="titlebg">';

	foreach ($context['columns'] as $column)
	{
		if ($column['selected'])
			echo '
				<th', isset($column['width']) ? ' width="' . $column['width'] . '"' : '', '>
					<a href="', $column['href'], '">', $column['label'], '&nbsp;<img src="', $settings['images_url'], '/sort_', $context['sort_direction'], '.gif" alt="" /></a>
				</th>';
		elseif ($column['sortable'])
			echo '
				<th', isset($column['width']) ? ' width="' . $column['width'] . '"' : '', '>
					', $column['link'], '
				</th>';
		else
			echo '
				<th', isset($column['width']) ? ' width="' . $column['width'] . '"' : '', '>
					', $column['label'], '
				</th>';
	}

	echo '
				<th><input type="checkbox" class="check" onclick="invertAll(this, this.form);" /></th>
			</tr>';
	
	if (empty($context['pages']))
		echo '
			<tr>
				<td colspan="', count($context['columns']) + 1, '" class="sp_center windowbg">', $txt['sp_error_no_pages'], '</td>
			</tr>';

	foreach ($context['pages'] as $page)
	{
		echo '
			<tr>
				<td class="sp_left windowbg">', $page['link'], '</td>
				<td class="sp_center windowbg">', $page['page_id'], '</td>
				<td class="sp_center windowbg">', $page['type_text'], '</td>
				<td class="sp_center windowbg">', $page['views'], '</td>
				<td class="sp_center windowbg">', $page['status_image'], '</td>
				<td class="sp_center windowbg">', implode('&nbsp;', $page['actions']), '</td>
				<td class="sp_center windowbg2"><input type="checkbox" name="remove[]" value="', $page['id'], '" class="check" /></td>
			</tr>';
	}

	echo '
			<tr class="catbg3">
				<td colspan="', count($context['columns']) + 1, '" class="sp_left">
					<div class="sp_float_right">
						<input type="submit" name="remove_pages" value="', $txt['sp_admin_pages_remove'], '" />
					</div>
					<strong>', $txt[139], ':</strong> ', $context['page_index'], '
				</td>
			</tr>
		</table>
		<input type="hidden" name="sc" value="', $context['session_id'], '" />
	</form>';
}

function template_pages_edit()
{
	global $context, $settings, $options, $scripturl, $txt, $helptxt, $modSettings;

	if (!empty($context['SPortal']['preview']))
	{
		echo '
<div style="width: 960px; margin: 0 auto; padding-bottom: 15px;">';

		template_view_page();

		echo '
</div>';
	}

	echo '
<form action="', $scripturl, '?action=manageportal;area=portalpages;sa=edit" method="post" accept-charset="', $context['character_set'], '" name="editpage">
	<div class="tborder" style="width: 85%; margin: 0 auto;">
		<table class="sp_table">
			<tr>
				<td colspan="2" class="sp_regular_padding catbg">', $txt['sp_admin_pages_general'], '</td>
			</tr>
			<tr>
				<th class="sp_regular_padding sp_top sp_right windowbg2">', $txt['sp_admin_pages_col_title'], ':</th>
				<td class="sp_regular_padding sp_top windowbg2"><input type="text" name="title" value="', $context['SPortal']['page']['title'], '" /></td>
			</tr>
			<tr>
				<th class="sp_regular_padding sp_top sp_right windowbg2">', $txt['sp_admin_pages_col_namespace'], ':</th>
				<td class="sp_regular_padding sp_top windowbg2"><input type="text" name="namespace" value="', $context['SPortal']['page']['page_id'], '" /></td>
			</tr>
			<tr>
				<th class="sp_regular_padding sp_top sp_right windowbg2">', $txt['sp_admin_pages_col_type'], ':</th>
				<td class="sp_regular_padding sp_top windowbg2">
					<select name="type" id="type" onchange="sp_update_editor();">
						<option value="bbc"', $context['SPortal']['page']['type'] == 'bbc' ? ' selected="selected"' : '', '>', $txt['sp_pages_type_bbc'], '</option>
						<option value="html"', $context['SPortal']['page']['type'] == 'html' ? ' selected="selected"' : '', '>', $txt['sp_pages_type_html'], '</option>
						<option value="php"', $context['SPortal']['page']['type'] == 'php' ? ' selected="selected"' : '', '>', $txt['sp_pages_type_php'], '</option>
					</select>
				</td>
			</tr>
			<tr>
				<th class="sp_regular_padding sp_top sp_right windowbg2"><a href="', $scripturl, '?action=helpadmin;help=sp_permissions" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt[119], '" border="0" align="top" /></a> ', $txt['sp_admin_pages_col_permissions'], ':</th>
				<td class="sp_regular_padding sp_top windowbg2">
					<select name="permission_set" id="page_permission_set" onchange="sp_update_permissions();">';

	$permission_sets = array(1 => 'guests', 2 => 'members', 3 => 'everyone', 0 => 'custom');
	foreach ($permission_sets as $id => $label)
		echo '
						<option value="', $id, '"', $id == $context['SPortal']['page']['permission_set'] ? ' selected="selected"' : '', '>', $txt['sp_admin_pages_permissions_set_' . $label], '</option>';

	echo '
					</select>
				</td>
			</tr>
			<tr>
				<th class="sp_regular_padding sp_top sp_right windowbg2" id="page_custom_permissions_label">', $txt['sp_admin_pages_col_custom_permissions'], ':</th>
				<td class="sp_regular_padding sp_top windowbg2" id="page_custom_permissions_input">
					<table>
						<tr>
							<th>', $txt['sp_admin_pages_custom_permissions_membergroup'], '</th>
							<th title="', $txt['sp_admin_pages_custom_permissions_allowed'], '">', $txt['sp_admin_pages_custom_permissions_allowed_short'], '</th>
							<th title="', $txt['sp_admin_pages_custom_permissions_disallowed'], '">', $txt['sp_admin_pages_custom_permissions_disallowed_short'], '</th>
							<th title="', $txt['sp_admin_pages_custom_permissions_denied'], '">', $txt['sp_admin_pages_custom_permissions_denied_short'], '</th>
						</tr>';

	foreach ($context['SPortal']['page']['groups'] as $id => $label)
	{
		$current = 0;
		if (in_array($id, $context['SPortal']['page']['groups_allowed']))
			$current = 1;
		elseif (in_array($id, $context['SPortal']['page']['groups_denied']))
			$current = -1;

		echo '
						<tr>
							<td>', $label, '</td>
							<td><input type="radio" name="membergroups[', $id, ']" value="1"', $current == 1 ? ' checked="checked"' : '', ' class="input_radio" /></td>
							<td><input type="radio" name="membergroups[', $id, ']" value="0"', $current == 0 ? ' checked="checked"' : '', ' class="input_radio" /></td>
							<td><input type="radio" name="membergroups[', $id, ']" value="-1"', $current == -1 ? ' checked="checked"' : '', ' class="input_radio" /></td>
						</tr>';
	}

	echo '
					</table>
				</td>
			</tr>
			<tr>
				<th class="sp_regular_padding sp_top sp_right windowbg2">', $txt['sp_admin_pages_col_blocks'], ':</th>
				<td class="sp_regular_padding sp_top windowbg2">
					<select name="blocks[]" id="page_blocks" size="7" multiple="multiple">';

	foreach ($context['sides'] as $side => $label)
	{
		if (empty($context['page_blocks'][$side]))
			continue;

		echo '
						<optgroup label="', $label, '">';

		foreach ($context['page_blocks'][$side] as $block)
		{
			echo '
							<option value="', $block['id'], '"', $block['shown'] ? ' selected="selected"' : '', '>', $block['label'], '</option>';
		}

		echo '
						</optgroup>';
	}

	echo '
					</select>
				</td>
			</tr>
			<tr>
				<th class="sp_regular_padding sp_top sp_right windowbg2">', $txt['sp_admin_pages_col_status'], ':</th>
				<td class="sp_regular_padding sp_top windowbg2"><input type="checkbox" name="status" value="1"', $context['SPortal']['page']['status'] ? ' checked="checked"' : '', ' class="check" /></td>
			</tr>
			<tr>
				<th class="sp_regular_padding sp_top sp_right windowbg2">', $txt['sp_admin_pages_col_body'], ':</th>
				<td class="sp_regular_padding sp_top windowbg2">
					<table style="width: 90%;">
						', theme_postbox($context['SPortal']['page']['body']), '
					</table>
					<div class="sp_fullwidth sp_right">
						<input type="button" value="-" onclick="document.forms.editpage.content.rows -= 10" />
						<input type="button" value="+" onclick="document.forms.editpage.content.rows += 10" />
					</div>
				</td>
			</tr>
			<tr>
				<td colspan="2" class="sp_regular_padding sp_center windowbg2"><input type="submit" name="preview" value="', $txt['sp_admin_pages_preview'], '" /> <input type="submit" name="submit" value="', $context['page_title'], '" /></td>
			</tr>
		</table>
	</div>
	<br />
	<div class="tborder" style="width: 85%; margin: 0 auto;">
		<table class="sp_table">
			<tr>
				<td colspan="4" class="sp_regular_padding catbg">', $txt['sp_admin_pages_style'], '</td>
			</tr>
			<tr>
				<th class="sp_regular_padding sp_top sp_right windowbg2">', $txt['sp-blocksTitleDefaultClass'], ':</th>
				<td class="sp_regular_padding sp_top windowbg2">
					<select name="title_default_class" id="title_default_class">
						<option value="catbg"', $context['SPortal']['page']['style']['title_default_class'] == 'catbg' ? ' selected="selected"' : '', '>catbg</option>
						<option value="catbg2"', $context['SPortal']['page']['style']['title_default_class'] == 'catbg2' ? ' selected="selected"' : '', '>catbg2</option>
						<option value="catbg3"', $context['SPortal']['page']['style']['title_default_class'] == 'catbg3' ? ' selected="selected"' : '', '>catbg3</option>
						<option value="titlebg"', $context['SPortal']['page']['style']['title_default_class'] == 'titlebg' ? ' selected="selected"' : '', '>titlebg</option>
						<option value="titlebg2"', $context['SPortal']['page']['style']['title_default_class'] == 'titlebg2' ? ' selected="selected"' : '', '>titlebg2</option>
					</select>
				</td>
				<th class="sp_regular_padding sp_top sp_right windowbg2">', $txt['sp-blocksBodyDefaultClass'], ':</th>
				<td class="sp_regular_padding sp_top windowbg2">
					<select name="body_default_class" id="body_default_class">
						<option value="windowbg"', $context['SPortal']['page']['style']['body_default_class'] == 'windowbg' ? ' selected="selected"' : '', '>windowbg</option>
						<option value="windowbg2"', $context['SPortal']['page']['style']['body_default_class'] == 'windowbg2' ? ' selected="selected"' : '', '>windowbg2</option>
						<option value="windowbg3"', $context['SPortal']['page']['style']['body_default_class'] == 'windowbg3' ? ' selected="selected"' : '', '>windowbg3</option>
					</select>
				</td>
			</tr>
			<tr>
				<th class="sp_regular_padding sp_top sp_right windowbg2">', $txt['sp-blocksTitleCustomClass'], ':</th>
				<td class="sp_regular_padding sp_top windowbg2">
					<input type="text" name="title_custom_class" id="title_custom_class" value="', $context['SPortal']['page']['style']['title_custom_class'], '" />
				</td>
				<th class="sp_regular_padding sp_top sp_right windowbg2">', $txt['sp-blocksBodyCustomClass'], ':</th>
				<td class="sp_regular_padding sp_top windowbg2">
					<input type="text" name="body_custom_class" id="body_custom_class" value="', $context['SPortal']['page']['style']['body_custom_class'], '" />
				</td>

			</tr>
			<tr>
				<th class="sp_regular_padding sp_top sp_right windowbg2">', $txt['sp-blocksTitleCustomStyle'], ':</th>
				<td class="sp_regular_padding sp_top windowbg2">
					<input type="text" name="title_custom_style" id="title_custom_style" value="', $context['SPortal']['page']['style']['title_custom_style'], '" />
				</td>
				<th class="sp_regular_padding sp_top sp_right windowbg2">', $txt['sp-blocksBodyCustomStyle'], ':</th>
				<td class="sp_regular_padding sp_top windowbg2">
					<input type="text" name="body_custom_style" id="body_custom_style" value="', $context['SPortal']['page']['style']['body_custom_style'], '" />
				</td>
			</tr>
			<tr>
				<th class="sp_regular_padding sp_top sp_right windowbg2">', $txt['sp-blocksNoTitle'], ':</th>
				<td class="sp_regular_padding sp_top windowbg2">
					<input type="checkbox" name="no_title" id="no_title" value="1"', $context['SPortal']['page']['style']['no_title'] ? ' checked="checked"' : '', ' onclick="document.getElementById(\'title_default_class\').disabled = document.getElementById(\'title_custom_class\').disabled = document.getElementById(\'title_custom_style\').disabled = this.checked;" />
				</td>
				<th class="sp_regular_padding sp_top sp_right windowbg2">', $txt['sp-blocksNoBody'], ':</th>
				<td class="sp_regular_padding sp_top windowbg2">
					<input type="checkbox" name="no_body" id="no_body" value="1"', $context['SPortal']['page']['style']['no_body'] ? ' checked="checked"' : '', ' onclick="document.getElementById(\'body_default_class\').disabled = this.checked;" />
				</td>
			</tr>
			<tr>
				<td colspan="4" class="sp_regular_padding sp_center windowbg2"><input type="submit" name="submit" value="', $context['page_title'], '" /> <input type="submit" name="preview" value="', $txt['sp_admin_pages_preview'], '" /></td>
			</tr>
		</table>
	</div>
	<input type="hidden" name="page_id" value="', $context['SPortal']['page']['id'], '" />
	<input type="hidden" name="sc" value="', $context['session_id'], '" />
</form>
<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
	document.getElementById("title_default_class").disabled = document.getElementById("no_title").checked;
	document.getElementById("title_custom_class").disabled = document.getElementById("no_title").checked;
	document.getElementById("title_custom_style").disabled = document.getElementById("no_title").checked;
	document.getElementById("body_default_class").disabled = document.getElementById("no_body").checked;

	function sp_update_editor()
	{
		var new_state = document.getElementById("type").value;
		if (new_state == "bbc")
		{
			document.getElementById("sp_editor_a").style.display = "";
			document.getElementById("sp_editor_s").style.display = "";
		}
		else
		{
			document.getElementById("sp_editor_a").style.display = "none";
			document.getElementById("sp_editor_s").style.display = "none";
		}
	}
	sp_update_editor();

	function sp_update_permissions()
	{
		var new_state = document.getElementById("page_permission_set").value;
		document.getElementById("page_custom_permissions_label").style.display = new_state != 0 ? "none" : "";
		document.getElementById("page_custom_permissions_input").style.display = new_state != 0 ? "none" : "";
	}
	sp_update_permissions();
// ]]></script>';
}

?>