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

function template_pages_list()
{
	global $context, $settings, $scripturl, $txt;

	echo '
	<div id="sp_manage_pages">
		<form action="', $scripturl, '?action=admin;area=portalpages;sa=list" method="post" accept-charset="', $context['character_set'], '" onsubmit="return confirm(\'', $txt['sp_pages_remove_confirm'], '\');">
			<div class="sp_align_left pagesection">
				', $txt['pages'], ': ', $context['page_index'], '
			</div>
			<table class="table_grid" cellspacing="0" width="100%">
				<thead>
					<tr class="catbg">';

	foreach ($context['columns'] as $column)
	{
		if ($column['selected'])
			echo '
						<th scope="col"', isset($column['class']) ? ' class="' . $column['class'] . '"' : '', isset($column['width']) ? ' width="' . $column['width'] . '"' : '', '>
							<a href="', $column['href'], '">', $column['label'], '&nbsp;<img src="', $settings['images_url'], '/sort_', $context['sort_direction'], '.gif" alt="" /></a>
						</th>';
		elseif ($column['sortable'])
			echo '
						<th scope="col"', isset($column['class']) ? ' class="' . $column['class'] . '"' : '', isset($column['width']) ? ' width="' . $column['width'] . '"' : '', '>
							', $column['link'], '
						</th>';
		else
			echo '
						<th scope="col"', isset($column['class']) ? ' class="' . $column['class'] . '"' : '', isset($column['width']) ? ' width="' . $column['width'] . '"' : '', '>
							', $column['label'], '
						</th>';
	}

	echo '
						<th scope="col" class="last_th">
							<input type="checkbox" class="input_check" onclick="invertAll(this, this.form);" />
						</th>
					</tr>
				</thead>
				<tbody>';
	
	if (empty($context['pages']))
	{
		echo '
					<tr class="windowbg2">
						<td class="sp_center" colspan="', count($context['columns']) + 1, '">', $txt['sp_error_no_pages'], '</td>
					</tr>';
	}

	foreach ($context['pages'] as $page)
	{
		echo '
					<tr class="windowbg2">
						<td class="sp_left">', $page['link'], '</td>
						<td class="sp_center">', $page['page_id'], '</td>
						<td class="sp_center">', $page['type_text'], '</td>
						<td class="sp_center">', $page['views'], '</td>
						<td class="sp_center">', $page['status_image'], '</td>
						<td class="sp_center">', implode('&nbsp;', $page['actions']), '</td>
						<td class="sp_center"><input type="checkbox" name="remove[]" value="', $page['id'], '" class="input_check" /></td>
					</tr>';
	}

	echo '
				</tbody>
			</table>
			<div class="sp_align_left pagesection">
				<div class="sp_float_right">
					<input type="submit" name="remove_pages" value="', $txt['sp_admin_pages_remove'], '" class="button_submit" />
				</div>
				', $txt['pages'], ': ', $context['page_index'], '
			</div>
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
		</form>
	</div>';
}

function template_pages_edit()
{
	global $context, $settings, $scripturl, $txt;

	if (!empty($context['SPortal']['preview']))
	{
		echo '
	<div class="sp_auto_align" style="width: 90%; padding-bottom: 1em;">';

		template_view_page();

		echo '
	</div>';
	}

	echo '
	<div id="sp_edit_page">
		<form action="', $scripturl, '?action=admin;area=portalpages;sa=edit" method="post" accept-charset="', $context['character_set'], '" onsubmit="submitonce(this);">
			<div class="cat_bar">
				<h3 class="catbg">
					', $txt['sp_admin_pages_general'], '
				</h3>
			</div>
			<div class="windowbg2">
				<span class="topslice"><span></span></span>
				<div class="sp_content_padding">
					<dl class="sp_form">
						<dt>
							<label for="page_title">', $txt['sp_admin_pages_col_title'], ':</label>
						</dt>
						<dd>
						<input type="text" name="title" id="page_title" value="', $context['SPortal']['page']['title'], '" class="input_text" />
						</dd>
						<dt>
							<label for="page_namespace">', $txt['sp_admin_pages_col_namespace'], ':</label>
						</dt>
						<dd>
							<input type="text" name="namespace" id="page_namespace" value="', $context['SPortal']['page']['page_id'], '" class="input_text" />
						</dd>
						<dt>
							<label for="page_type">', $txt['sp_admin_pages_col_type'], ':</label>
						</dt>
						<dd>
							<select name="type" id="page_type" onchange="sp_update_editor();">';

	$content_types = array('bbc', 'html', 'php');
	foreach ($content_types as $type)
		echo '
								<option value="', $type, '"', $context['SPortal']['page']['type'] == $type ? ' selected="selected"' : '', '>', $txt['sp_pages_type_' . $type], '</option>';

	echo '
							</select>
						</dd>
						<dt>
							<a href="', $scripturl, '?action=helpadmin;help=sp_permissions" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" class="icon" /></a>
							<label for="page_permission_set">', $txt['sp_admin_pages_col_permissions'], ':</label>
						</dt>
						<dd>
							<select name="permission_set" id="page_permission_set" onchange="sp_update_permissions();">';

	$permission_sets = array(1 => 'guests', 2 => 'members', 3 => 'everyone', 0 => 'custom');
	foreach ($permission_sets as $id => $label)
		echo '
								<option value="', $id, '"', $id == $context['SPortal']['page']['permission_set'] ? ' selected="selected"' : '', '>', $txt['sp_admin_pages_permissions_set_' . $label], '</option>';

	echo '
							</select>
						</dd>
						<dt id="page_custom_permissions_label">
							', $txt['sp_admin_pages_col_custom_permissions'], ':
						</dt>
						<dd id="page_custom_permissions_input">
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
						</dd>
						<dt>
							<label for="page_blocks">', $txt['sp_admin_pages_col_blocks'], ':</label>
						</dt>
						<dd>
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
						</dd>
						<dt>
							<label for="page_status">', $txt['sp_admin_pages_col_status'], ':</label>
						</dt>
						<dd>
							<input type="checkbox" name="status" id="page_status" value="1"', $context['SPortal']['page']['status'] ? ' checked="checked"' : '', ' class="input_check" /></dd>
						<dt>
							', $txt['sp_admin_pages_col_body'], ':
						</dt>
						<dd>
						</dd>
					</dl>
					<div id="sp_rich_editor">
						<div id="sp_rich_bbc"', $context['SPortal']['page']['type'] != 'bbc' ? ' style="display: none;"' : '', '></div>
						<div id="sp_rich_smileys"', $context['SPortal']['page']['type'] != 'bbc' ? ' style="display: none;"' : '', '></div>
						<div>', template_control_richedit($context['post_box_name'], 'sp_rich_smileys', 'sp_rich_bbc'), '</div>
					</div>
					<div class="sp_button_container">
						<input type="submit" name="preview" value="', $txt['sp_admin_pages_preview'], '" class="button_submit" /> <input type="submit" name="submit" value="', $context['page_title'], '" class="button_submit" />
					</div>
				</div>
				<span class="botslice"><span></span></span>
			</div>';

	$style_sections = array('title' => 'left', 'body' => 'right');
	$style_types = array('default' => 'DefaultClass', 'class' => 'CustomClass', 'style' => 'CustomStyle');
	$style_parameters = array(
		'title' => array('catbg', 'catbg2', 'catbg3', 'titlebg', 'titlebg2'),
		'body' => array('windowbg',  'windowbg2', 'windowbg3', 'information', 'roundframe'),
	);

	echo '
			<br />
			<div class="cat_bar">
				<h3 class="catbg">
					', $txt['sp_admin_pages_style'], '
				</h3>
			</div>
			<div class="windowbg2">
				<span class="topslice"><span></span></span>
				<div class="sp_content_padding">';

	foreach ($style_sections as $section => $float)
	{
		echo '
					<dl id="sp_edit_style_', $section, '" class="sp_form sp_float_', $float, '">';

		foreach ($style_types as $type => $label)
		{
			echo '
						<dt>
							', $txt['sp-blocks' . ucfirst($section) . $label], ':
						</dt>
						<dd>';

			if ($type == 'default')
			{
				echo '
							<select name="', $section, '_default_class" id="', $section, '_default_class">';

				foreach ($style_parameters[$section] as $class)
					echo '
								<option value="', $class, '"', $context['SPortal']['page']['style'][$section . '_default_class'] == $class ? ' selected="selected"' : '', '>', $class, '</option>';

				echo '
							</select>';
			}
			else
				echo '
							<input type="text" name="', $section, '_custom_', $type, '" id="', $section, '_custom_', $type, '" value="', $context['SPortal']['page']['style'][$section . '_custom_' . $type], '" class="input_text" />';

			echo '
						</dd>';
		}

		echo '
						<dt>
							', $txt['sp-blocksNo' . ucfirst($section)], ':
						</dt>
						<dd>
							<input type="checkbox" name="no_', $section, '" id="no_', $section, '" value="1"', !empty($context['SPortal']['page']['style']['no_' . $section]) ? ' checked="checked"' : '', ' onclick="document.getElementById(\'', $section, '_default_class\').disabled', $section == 'title' ? ' = document.getElementById(\'title_custom_class\').disabled = document.getElementById(\'title_custom_style\').disabled' : '', ' = this.checked;" class="input_check" />
						</dd>
					</dl>';
	}

	echo '
					<div class="sp_button_container">
						<input type="submit" name="preview" value="', $txt['sp_admin_pages_preview'], '" class="button_submit" /> <input type="submit" name="submit" value="', $context['page_title'], '" class="button_submit" />
					</div>
				</div>
				<span class="botslice"><span></span></span>
			</div>
			<input type="hidden" name="page_id" value="', $context['SPortal']['page']['id'], '" />
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
		</form>
	</div>
	<script type="text/javascript"><!-- // --><![CDATA[
		document.getElementById("title_default_class").disabled = document.getElementById("no_title").checked;
		document.getElementById("title_custom_class").disabled = document.getElementById("no_title").checked;
		document.getElementById("title_custom_style").disabled = document.getElementById("no_title").checked;
		document.getElementById("body_default_class").disabled = document.getElementById("no_body").checked;

		sp_update_permissions();

		function sp_update_editor()
		{
			var new_state = document.getElementById("page_type").value;
			if (new_state == "bbc")
			{
				document.getElementById("sp_rich_bbc").style.display = "";
				document.getElementById("sp_rich_smileys").style.display = "";
			}
			else
			{
				if (oEditorHandle_content.bRichTextEnabled)
					oEditorHandle_content.toggleView();

				document.getElementById("sp_rich_bbc").style.display = "none";
				document.getElementById("sp_rich_smileys").style.display = "none";
			}
		}

		function sp_update_permissions()
		{
			var new_state = document.getElementById("page_permission_set").value;
			document.getElementById("page_custom_permissions_label").style.display = new_state != 0 ? "none" : "";
			document.getElementById("page_custom_permissions_input").style.display = new_state != 0 ? "none" : "";
		}
	// ]]></script>';
}

?>