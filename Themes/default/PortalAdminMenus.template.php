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

function template_menus_custom_menu_list()
{
	global $context, $scripturl, $settings, $txt;

	echo '
	<div id="sp_manage_custom_menus">
		<form action="', $scripturl, '?action=admin;area=portalmenus;sa=listcustommenu" method="post" accept-charset="', $context['character_set'], '" onsubmit="return confirm(\'', $txt['sp_menus_remove_confirm'], '\');">
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
	
	if (empty($context['menus']))
	{
		echo '
					<tr class="windowbg2">
						<td class="sp_center" colspan="', count($context['columns']) + 1, '">', $txt['error_sp_no_custom_menus'], '</td>
					</tr>';
	}

	foreach ($context['menus'] as $menu)
	{
		echo '
					<tr class="windowbg2">
						<td class="sp_left">', $menu['name'], '</td>
						<td class="sp_center">', $menu['items'], '</td>
						<td class="sp_center">', implode('&nbsp;', $menu['actions']), '</td>
						<td class="sp_center"><input type="checkbox" name="remove[]" value="', $menu['id'], '" class="input_check" /></td>
					</tr>';
	}

	echo '
				</tbody>
			</table>
			<div class="sp_align_left pagesection">
				<div class="sp_float_right">
					<input type="submit" name="remove_menus" value="', $txt['sp_admin_menus_remove'], '" class="button_submit" />
				</div>
				', $txt['pages'], ': ', $context['page_index'], '
			</div>
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
		</form>
	</div>';
}

function template_menus_custom_menu_edit()
{
	global $context, $scripturl, $txt;

	echo '
	<div id="sp_edit_custom_menu">
		<form action="', $scripturl, '?action=admin;area=portalmenus;sa=editcustommenu" method="post" accept-charset="', $context['character_set'], '" onsubmit="submitonce(this);">
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
							<label for="menu_name">', $txt['sp_admin_menus_col_name'], ':</label>
						</dt>
						<dd>
							<input type="text" name="name" id="menu_name" value="', $context['menu']['name'], '" class="input_text" />
						</dd>
					</dl>
					<div class="sp_button_container">
						<input type="submit" name="submit" value="', $context['page_title'], '" class="button_submit" />
					</div>
				</div>
				<span class="botslice"><span></span></span>
			</div>
			<input type="hidden" name="menu_id" value="', $context['menu']['id'], '" />
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
		</form>
	</div>';
}

function template_menus_custom_item_list()
{
	global $context, $scripturl, $settings, $txt;

	echo '
	<div id="sp_manage_menu_items">
		<form action="', $scripturl, '?action=admin;area=portalmenus;sa=listcustomitem;menu_id=', $context['menu']['id'], '" method="post" accept-charset="', $context['character_set'], '" onsubmit="return confirm(\'', $txt['sp_items_remove_confirm'], '\');">
			<table class="table_grid" cellspacing="0" width="100%">
				<thead>
					<tr class="catbg">';

	foreach ($context['columns'] as $column)
	{
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
	
	if (empty($context['items']))
	{
		echo '
					<tr class="windowbg2">
						<td class="sp_center" colspan="', count($context['columns']) + 1, '">', $txt['error_sp_no_menu_items'], '</td>
					</tr>';
	}

	foreach ($context['items'] as $item)
	{
		echo '
					<tr class="windowbg2">
						<td class="sp_left">', $item['title'], '</td>
						<td class="sp_center">', $item['namespace'], '</td>
						<td class="sp_center">', $item['target'], '</td>
						<td class="sp_center">', implode('&nbsp;', $item['actions']), '</td>
						<td class="sp_center"><input type="checkbox" name="remove[]" value="', $item['id'], '" class="input_check" /></td>
					</tr>';
	}

	echo '
				</tbody>
			</table>
			<div class="sp_right pagesection">
				<input type="submit" name="remove_items" value="', $txt['sp_admin_items_remove'], '" class="button_submit" />
			</div>
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
		</form>
	</div>';
}

function template_menus_custom_item_edit()
{
	global $context, $scripturl, $txt;

	echo '
	<div id="sp_edit_custom_item">
		<form action="', $scripturl, '?action=admin;area=portalmenus;sa=editcustomitem;menu_id=', $context['menu']['id'], '" method="post" accept-charset="', $context['character_set'], '" onsubmit="submitonce(this);">
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
							<label for="item_title">', $txt['sp_admin_menus_col_title'], ':</label>
						</dt>
						<dd>
							<input type="text" name="title" id="item_title" value="', $context['item']['title'], '" class="input_text" />
						</dd>
						<dt>
							<label for="item_namespace">', $txt['sp_admin_menus_col_namespace'], ':</label>
						</dt>
						<dd>
							<input type="text" name="namespace" id="item_namespace" value="', $context['item']['namespace'], '" class="input_text" />
						</dd>
						<dt>
							<label for="item_link_type">', $txt['sp_admin_menus_col_link_type'], ':</label>
						</dt>
						<dd>
							<select name="link_type" id="item_link_type" onchange="sp_update_link();">
								<option value="custom">', $txt['sp_admin_menus_link_type_custom'], '</option>';

	foreach ($context['items'] as $type => $items)
	{
		if (empty($items))
			continue;

		echo '
								<option value="', $type, '">', $txt['sp_admin_menus_link_type_' . $type], '</option>';
	}

	echo '
							</select>
						</dd>
						<dt id="item_link_dt">
							<label for="item_link_item">', $txt['sp_admin_menus_col_link_item'], ':</label>
						</dt>
						<dd id="item_link_dd">
							<select name="link_item" id="item_link_item">
							</select>
						</dd>
						<dt id="item_url_dt">
							<label for="item_url">', $txt['sp_admin_menus_col_url'], ':</label>
						</dt>
						<dd id="item_url_dd">
							<input type="text" name="url" id="item_url" value="', $context['item']['url'], '" class="input_text" size="40" />
						</dd>
						<dt>
							<label for="item_target">', $txt['sp_admin_menus_col_target'], ':</label>
						</dt>
						<dd>
							<select name="target" id="item_target">
								<option value="0"', $context['item']['target'] == 0 ? ' selected="selected"' : '', '>', $txt['sp_admin_menus_link_target_0'], '</option>
								<option value="1"', $context['item']['target'] == 1 ? ' selected="selected"' : '', '>', $txt['sp_admin_menus_link_target_1'], '</option>
							</select>
						</dd>
					</dl>
					<div class="sp_button_container">
						<input type="submit" name="submit" value="', $context['page_title'], '" class="button_submit" />
					</div>
				</div>
				<span class="botslice"><span></span></span>
			</div>
			<input type="hidden" name="item_id" value="', $context['item']['id'], '" />
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
		</form>
	</div>
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		var sp_link_items = {';

	$sets = array();
	foreach ($context['items'] as $type => $items)
	{
		if (empty($items))
			continue;

		$set = array();
		foreach ($items as $id => $title)
		{
			$set[] = JavaScriptEscape($id) . ': ' . JavaScriptEscape($title);
		}

		$sets[] = JavaScriptEscape($type) . ': {
				' . implode(',
				', $set) . '
			}';
	}

	echo '
			', implode(',
			', $sets), '
		};
		function sp_update_link()
		{
			var type_select = document.getElementById("item_link_type");
			var item_select = document.getElementById("item_link_item");
			var new_value = type_select.options[type_select.selectedIndex].value;

			while (item_select.options.length)
				item_select.options[0] = null;
			for (var key in sp_link_items[new_value])
				item_select.options[item_select.length] = new Option(sp_link_items[new_value][key], key);

			document.getElementById("item_link_dt").style.display = document.getElementById("item_link_dd").style.display = new_value == "custom" ? "none" : "block";
			document.getElementById("item_url_dt").style.display = document.getElementById("item_url_dd").style.display = new_value != "custom" ? "none" : "block";
		}
	// ]]></script>';
}