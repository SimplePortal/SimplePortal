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

function template_categories_list()
{
	global $context, $scripturl, $settings, $txt;

	echo '
	<div id="sp_manage_categories">
		<form action="', $scripturl, '?action=admin;area=portalcategories;sa=list" method="post" accept-charset="', $context['character_set'], '" onsubmit="return confirm(\'', $txt['sp_categories_remove_confirm'], '\');">
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
	
	if (empty($context['categories']))
	{
		echo '
					<tr class="windowbg2">
						<td class="sp_center" colspan="', count($context['columns']) + 1, '">', $txt['error_sp_no_categories'], '</td>
					</tr>';
	}

	foreach ($context['categories'] as $category)
	{
		echo '
					<tr class="windowbg2">
						<td class="sp_left">', $category['link'], '</td>
						<td class="sp_center">', $category['category_id'], '</td>
						<td class="sp_center">', $category['articles'], '</td>
						<td class="sp_center">', $category['status_image'], '</td>
						<td class="sp_center">', implode('&nbsp;', $category['actions']), '</td>
						<td class="sp_center"><input type="checkbox" name="remove[]" value="', $category['id'], '" class="input_check" /></td>
					</tr>';
	}

	echo '
				</tbody>
			</table>
			<div class="sp_align_left pagesection">
				<div class="sp_float_right">
					<input type="submit" name="remove_categories" value="', $txt['sp_admin_categories_remove'], '" class="button_submit" />
				</div>
				', $txt['pages'], ': ', $context['page_index'], '
			</div>
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
		</form>
	</div>';
}

function template_categories_edit()
{
	global $context, $scripturl, $settings, $txt, $helptxt;

	echo '
	<div id="sp_edit_category">
		<form action="', $scripturl, '?action=admin;area=portalcategories;sa=edit" method="post" accept-charset="', $context['character_set'], '" onsubmit="submitonce(this);">
			<div class="cat_bar">
				<h3 class="catbg">
					', $context['page_title'], '
				</h3>
			</div>
			<div class="windowbg2">
				<span class="topslice"><span></span></span>
				<div class="sp_content_padding">
					<dl class="sp_form">
						<dt>
							<label for="category_name">', $txt['sp_admin_categories_col_name'], ':</label>
						</dt>
						<dd>
						<input type="text" name="name" id="category_name" value="', $context['category']['name'], '" class="input_text" />
						</dd>
						<dt>
							<label for="category_namespace">', $txt['sp_admin_categories_col_namespace'], ':</label>
						</dt>
						<dd>
							<input type="text" name="namespace" id="category_namespace" value="', $context['category']['category_id'], '" class="input_text" />
						</dd>
						<dt>
							<label for="category_description">', $txt['sp_admin_categories_col_description'], ':</label>
						</dt>
						<dd>
							<textarea name="description" id="category_description" rows="5" cols="45">', $context['category']['description'], '</textarea>
						</dd>
						<dt>
							<a href="', $scripturl, '?action=helpadmin;help=sp_permissions" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" class="icon" /></a>
							<label for="category_permission_set">', $txt['sp_admin_categories_col_permissions'], ':</label>
						</dt>
						<dd>
							<select name="permission_set" id="category_permission_set" onchange="sp_update_permissions();">';

	$permission_sets = array(1 => 'guests', 2 => 'members', 3 => 'everyone', 0 => 'custom');
	foreach ($permission_sets as $id => $label)
		echo '
								<option value="', $id, '"', $id == $context['category']['permission_set'] ? ' selected="selected"' : '', '>', $txt['sp_admin_categories_permissions_set_' . $label], '</option>';

	echo '
							</select>
						</dd>
						<dt id="category_custom_permissions_label">
							', $txt['sp_admin_categories_col_custom_permissions'], ':
						</dt>
						<dd id="category_custom_permissions_input">
							<table>
								<tr>
									<th>', $txt['sp_admin_categories_custom_permissions_membergroup'], '</td>
									<th title="', $txt['sp_admin_categories_custom_permissions_allowed'], '">', $txt['sp_admin_categories_custom_permissions_allowed_short'], '</th>
									<th title="', $txt['sp_admin_categories_custom_permissions_disallowed'], '">', $txt['sp_admin_categories_custom_permissions_disallowed_short'], '</th>
									<th title="', $txt['sp_admin_categories_custom_permissions_denied'], '">', $txt['sp_admin_categories_custom_permissions_denied_short'], '</th>
								</tr>';

	foreach ($context['category']['groups'] as $id => $label)
	{
		$current = 0;
		if (in_array($id, $context['category']['groups_allowed']))
			$current = 1;
		elseif (in_array($id, $context['category']['groups_denied']))
			$current = -1;

		echo '
								<tr>
									<td>', $label, '</td>
									<td><input type="radio" name="membergroups[', $id, ']" value="1"', $current == 1 ? ' checked="checked"' : '', ' class="input_radio"></td>
									<td><input type="radio" name="membergroups[', $id, ']" value="0"', $current == 0 ? ' checked="checked"' : '', ' class="input_radio"></td>
									<td><input type="radio" name="membergroups[', $id, ']" value="-1"', $current == -1 ? ' checked="checked"' : '', ' class="input_radio"></td>
								</tr>';
	}

	echo '
							</table>
						</dd>
						<dt>
							<label for="category_status">', $txt['sp_admin_categories_col_status'], ':</label>
						</dt>
						<dd>
							<input type="checkbox" name="status" id="category_status" value="1"', $context['category']['status'] ? ' checked="checked"' : '', ' class="input_check" />
						</dd>
					</dl>
					<div class="sp_button_container">
						<input type="submit" name="submit" value="', $context['page_title'], '" class="button_submit" />
					</div>
				</div>
				<span class="botslice"><span></span></span>
			</div>
			<input type="hidden" name="category_id" value="', $context['category']['id'], '" />
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
		</form>
	</div>
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		sp_update_permissions();

		function sp_update_permissions()
		{
			var new_state = document.getElementById("category_permission_set").value;
			document.getElementById("category_custom_permissions_label").style.display = new_state != 0 ? "none" : "";
			document.getElementById("category_custom_permissions_input").style.display = new_state != 0 ? "none" : "";
		}
	// ]]></script>';
}

?>