<?php

/**
 * @package SimplePortal
 *
 * @author SimplePortal Team
 * @copyright 2020 SimplePortal Team
 * @license BSD 3-clause
 *
 * @version 2.3.8
 */

function template_block_list()
{
	global $context, $settings, $scripturl, $txt;

	echo '
	<div id="sp_manage_blocks">';

	if ($context['block_move'])
		echo '
		<div class="information">
			<p>', $context['move_title'], ' [<a href="', $scripturl, '?action=admin;area=portalblocks">', $txt['sp-blocks_cancel_moving'], '</a>]', '</p>
		</div>';

	foreach($context['sides'] as $id => $side)
	{
		echo '
		<div class="cat_bar">
			<h3 class="catbg">
				<a class="sp_float_right" href="', $scripturl, '?action=admin;area=portalblocks;sa=add;col=', $side['id'], '">', sp_embed_image('add', sprintf($txt['sp-blocksCreate'], $side['label'])), '</a>
				<a href="', $scripturl, '?action=helpadmin;help=', $side['help'], '" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" class="icon" /></a>
				<a href="', $scripturl, '?action=admin;area=portalblocks;sa=', $id, '">', $side['label'], '</a>
			</h3>
		</div>
		<table class="table_grid" cellspacing="0" width="100%">
			<thead>
				<tr class="catbg">';

		if ($context['block_move'])
			echo '
				<th scope="col" class="first_th" width="5%">', $txt['sp-adminColumnMove'], '</th>';

		foreach ($context['columns'] as $column)
			echo '
					<th scope="col"', isset($column['class']) ? ' class="' . $column['class'] . '"' : '', isset($column['width']) ? ' width="' . $column['width'] . '"' : '', '>', $column['label'], '</th>';

		echo '
				</tr>
			</thead>
			<tbody>';

		if (empty($context['blocks'][$side['name']]))
		{
			echo '
				<tr class="windowbg2">
					<td class="sp_center" colspan="4">', $txt['error_sp_no_block'], '</td>
				</tr>';
		}

		foreach($context['blocks'][$side['name']] as $block)
		{
			echo '
				<tr class="windowbg2">';

			if ($context['block_move'])
				echo '
				<td class="sp_center">', $block['id'] != $context['block_move'] ? $block['move_insert'] : '', '</td>';

			echo '
					<td>', $block['id'] == $context['block_move'] ? '<strong>' . $block['label'] . '</strong>' : $block['label'], '</td>
					<td>', $block['type_text'], '</td>
					<td class="sp_center">', implode(' ', $block['actions']), '</td>
				</tr>';
		}

		if ($context['block_move'] && (empty($side['last']) || $context['block_move'] != $side['last']))
		{
			echo '
			<tr class="windowbg2">
				<td class="sp_center"><a href="', $scripturl, '?action=admin;area=portalblocks;sa=move;block_id=', $context['block_move'], ';col=', $side['id'], ';', $context['session_var'], '=', $context['session_id'], '">', sp_embed_image('arrow', $txt['sp-blocks_move_here']), '</a></td>
				<td></td>
				<td></td>
				<td></td>
			</tr>';
		}

		echo '
			</tbody>
		</table>';
	}

	echo '
	</div>';
}

function template_block_edit()
{
	global $context, $settings, $options, $scripturl, $txt, $helptxt, $modSettings;

	if (!empty($context['SPortal']['preview']))
	{
		echo '
	<div class="sp_auto_align" style="width: ', $context['widths'][$context['SPortal']['block']['column']], ';">';

		template_block($context['SPortal']['block']);

		echo '
	</div>';
	}

	echo '
	<div id="sp_edit_block">
		<form name="sp_edit_block_form" id="sp_edit_block_form" action="', $scripturl, '?action=admin;area=portalblocks;sa=edit" method="post" accept-charset="', $context['character_set'], '" onsubmit="submitonce(this);">
			<div class="cat_bar">
				<h3 class="catbg">
					<a href="', $scripturl, '?action=helpadmin;help=sp-blocks', $context['SPortal']['is_new'] ? 'Add' : 'Edit', '" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" class="icon" /></a>
					', $context['SPortal']['is_new'] ? $txt['sp-blocksAdd'] : $txt['sp-blocksEdit'], '
				</h3>
			</div>
			<div class="windowbg2">
				<span class="topslice"><span></span></span>
				<div class="sp_content_padding">
					<dl class="sp_form">
						<dt>
							', $txt['sp-adminColumnType'], ':
						</dt>
						<dd>
							', $context['SPortal']['block']['type_text'], '
						</dd>
						<dt>
							<label for="block_name">', $txt['sp-adminColumnName'], ':</label>
						</dt>
						<dd>
							<input type="text" name="block_name" id="block_name" value="', $context['SPortal']['block']['label'], '" size="30" class="input_text" />
						</dd>
						<dt>
							<a href="', $scripturl, '?action=helpadmin;help=sp_permissions" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" class="icon" /></a>
							', $txt['sp_admin_blocks_col_permissions'], ':
						</dt>
						<dd>
							<select name="permission_set" id="block_permission_set" onchange="sp_update_permissions();">';

	$permission_sets = array(1 => 'guests', 2 => 'members', 3 => 'everyone', 0 => 'custom');
	foreach ($permission_sets as $id => $label)
		echo '
								<option value="', $id, '"', $id == $context['SPortal']['block']['permission_set'] ? ' selected="selected"' : '', '>', $txt['sp_admin_blocks_permissions_set_' . $label], '</option>';

	echo '
							</select>
						</dd>
						<dt id="block_custom_permissions_label">
							', $txt['sp_admin_blocks_col_custom_permissions'], ':
						</dt>
						<dd id="block_custom_permissions_input">
							<table>
								<tr>
									<th>', $txt['sp_admin_blocks_custom_permissions_membergroup'], '</th>
									<th title="', $txt['sp_admin_blocks_custom_permissions_allowed'], '">', $txt['sp_admin_blocks_custom_permissions_allowed_short'], '</th>
									<th title="', $txt['sp_admin_blocks_custom_permissions_disallowed'], '">', $txt['sp_admin_blocks_custom_permissions_disallowed_short'], '</th>
									<th title="', $txt['sp_admin_blocks_custom_permissions_denied'], '">', $txt['sp_admin_blocks_custom_permissions_denied_short'], '</th>
								</tr>';

	foreach ($context['SPortal']['block']['groups'] as $id => $label)
	{
		$current = 0;
		if (in_array($id, $context['SPortal']['block']['groups_allowed']))
			$current = 1;
		elseif (in_array($id, $context['SPortal']['block']['groups_denied']))
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
						</dd>';

	foreach ($context['SPortal']['block']['options'] as $name => $type)
	{
		if (empty($context['SPortal']['block']['parameters'][$name]))
			$context['SPortal']['block']['parameters'][$name] = '';

		echo '
						<dt>';

		if (!empty($helptxt['sp_param_' . $context['SPortal']['block']['type'] . '_' . $name]))
			echo '
							<a href="', $scripturl, '?action=helpadmin;help=sp_param_', $context['SPortal']['block']['type'] , '_' , $name, '" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" class="icon" /></a>';

		echo '
							<label for="', $type == 'bbc' ? 'bbc_content' : $name, '">', $txt['sp_param_' . $context['SPortal']['block']['type'] . '_' . $name], ':</label>
						</dt>
						<dd>';

		if ($type == 'bbc')
		{
			echo '
						</dd>
					</dl>
					<div id="sp_rich_editor">
						<div id="sp_rich_bbc"></div>
						<div id="sp_rich_smileys"></div>
						', template_control_richedit($context['SPortal']['bbc'], 'sp_rich_smileys', 'sp_rich_bbc'), '
						<input type="hidden" name="bbc_name" value="', $name, '" />
						<input type="hidden" name="bbc_parameter" value="', $context['SPortal']['bbc'], '" />
					</div>
					<dl class="sp_form">';
		}
		elseif ($type == 'boards' || $type == 'board_select')
		{
					echo '
							<input type="hidden" name="parameters[', $name, ']" value="" />';

				if ($type == 'boards')
					echo '
							<select name="parameters[', $name, '][]" id="', $name, '" size="7" multiple="multiple">';
				else
					echo '
							<select name="parameters[', $name, '][]" id="', $name, '">';

				foreach ($context['SPortal']['block']['board_options'][$name] as $option)
					echo '
								<option value="', $option['value'], '"', ($option['selected'] ? ' selected="selected"' : ''), ' >', $option['text'], '</option>';
				echo '
							</select>';
		}
		elseif ($type == 'int')
			echo '
							<input type="text" name="parameters[', $name, ']" id="', $name, '" value="', $context['SPortal']['block']['parameters'][$name],'" size="7" class="input_text" />';
		elseif ($type == 'text')
			echo '
							<input type="text" name="parameters[', $name, ']" id="', $name, '" value="', $context['SPortal']['block']['parameters'][$name],'" size="25" class="input_text" />';
		elseif ($type == 'check')
				echo '
							<input type="checkbox" name="parameters[', $name, ']" id="', $name, '"', !empty($context['SPortal']['block']['parameters'][$name]) ? ' checked="checked"' : '', ' class="input_check" />';
		elseif ($type == 'select')
		{
				$options = explode('|', $txt['sp_param_' . $context['SPortal']['block']['type'] . '_' . $name . '_options']);

				echo '
							<select name="parameters[', $name, ']" id="', $name, '">';

				foreach ($options as $key => $option)
					echo '
								<option value="', $key, '"', $context['SPortal']['block']['parameters'][$name] == $key ? ' selected="selected"' : '', '>', $option, '</option>';

				echo '
							</select>';
		}
		elseif (is_array($type))
		{
				echo '
							<select name="parameters[', $name, ']" id="', $name, '">';

				foreach ($type as $key => $option)
					echo '
								<option value="', $key, '"', $context['SPortal']['block']['parameters'][$name] == $key ? ' selected="selected"' : '', '>', $option, '</option>';

				echo '
							</select>';
		}
		elseif ($type == 'textarea')
		{
			echo '
						</dd>
					</dl>
					<div id="sp_text_editor">
						<textarea name="parameters[', $name, ']" id="', $name, '" cols="45" rows="10">', $context['SPortal']['block']['parameters'][$name], '</textarea>
						<input type="button" class="button_submit" value="-" onclick="document.getElementById(\'', $name, '\').rows -= 10" />
						<input type="button" class="button_submit" value="+" onclick="document.getElementById(\'', $name, '\').rows += 10" />
					</div>
					<dl class="sp_form">';
		}

		if ($type != 'bbc' && $type != 'textarea')
			echo '
						</dd>';
	}

	if (empty($context['SPortal']['block']['column']))
	{
		echo '
						<dt>
							<label for="block_column">', $txt['sp-blocksColumn'], ':</label>
						</dt>
						<dd>
							<select id="block_column" name="block_column">';

		$block_sides = array(5 => 'Header', 1 => 'Left', 2 => 'Top', 3 => 'Bottom', 4 => 'Right', 6 => 'Footer');
		foreach ($block_sides as $id => $side)
			echo '
								<option value="', $id, '">', $txt['sp-position' . $side], '</option>';

		echo '
							</select>
						</dd>';
	}

	if (count($context['SPortal']['block']['list_blocks']) > 1)
	{
		echo '
						<dt>
							', $txt['sp-blocksRow'], ':
						</dt>
						<dd>
							<select id="order" name="placement"', !$context['SPortal']['is_new'] ? ' onchange="this.form.block_row.disabled = this.options[this.selectedIndex].value == \'\';"' : '', '>
								', !$context['SPortal']['is_new'] ? '<option value="nochange">' . $txt['sp-placementUnchanged'] . '</option>' : '', '
								<option value="before">', $txt['sp-placementBefore'], '...</option>
								<option value="after">', $txt['sp-placementAfter'], '...</option>
							</select>
							<select id="block_row" name="block_row"', !$context['SPortal']['is_new'] ? ' disabled="disabled"' : '', '>';

		foreach ($context['SPortal']['block']['list_blocks'] as $block)
		{
			if ($block['id'] != $context['SPortal']['block']['id'])
				echo '
								<option value="', $block['row'], '">', $block['label'], '</option>';
		}

		echo '
							</select>
						</dd>';
	}

	if ($context['SPortal']['block']['type'] != 'sp_boardNews')
	{
		echo '
						<dt>
							<label for="block_force">', $txt['sp-blocksForce'], ':</label>
						</dt>
						<dd>
							<input type="checkbox" name="block_force" id="block_force" value="1"', $context['SPortal']['block']['force_view'] ? ' checked="checked"' : '', ' class="input_check" />
						</dd>';
	}

	echo '
						<dt>
							<label for="block_active">', $txt['sp-blocksActive'], ':</label>
						</dt>
						<dd>
							<input type="checkbox" name="block_active" id="block_active" value="1"', $context['SPortal']['block']['state'] ? ' checked="checked"' : '', ' class="input_check" />
						</dd>
					</dl>
					<div class="sp_button_container">
						<input type="submit" name="preview_block" value="', $txt['sp-blocksPreview'], '" class="button_submit" /> <input type="submit" name="add_block" value="', !$context['SPortal']['is_new'] ? $txt['sp-blocksEdit'] : $txt['sp-blocksAdd'], '" class="button_submit" />
					</div>
				</div>
				<span class="botslice"><span></span></span>
			</div>';

	if (!empty($context['SPortal']['block']['column']))
		echo '
			<input type="hidden" name="block_column" value="', $context['SPortal']['block']['column'], '" />';

	echo '
			<input type="hidden" name="block_type" value="', $context['SPortal']['block']['type'], '" />
			<input type="hidden" name="block_id" value="', $context['SPortal']['block']['id'], '" />
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />';

	if (!empty($modSettings['sp_enableIntegration']))
	{
		echo '
			<br />
			<div class="cat_bar">
				<h3 class="catbg">
					<a href="', $scripturl, '?action=helpadmin;help=sp-blocksDisplayOptions" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" class="icon" /></a>
					', $txt['sp-blocksDisplayOptions'], '
				</h3>
			</div>
			<div class="windowbg2">
				<span class="topslice"><span></span></span>
				<div class="sp_content_padding">
					<span class="sp_float_right">', $txt['sp-blocksAdvancedOptions'], '<input type="checkbox" name="display_advanced" id="display_advanced" onclick="document.getElementById(\'sp_display_advanced\').style.display = this.checked ? \'block\' : \'none\'; document.getElementById(\'display_simple\').disabled = this.checked;" ', empty($context['SPortal']['block']['display_type']) ? '' : ' checked="checked"', ' class="input_check" /></span>
					', $txt['sp-blocksShowBlock'], '
					<select name="display_simple" id="display_simple"', empty($context['SPortal']['block']['display_type']) ? '' : ' disabled="disabled"', '>';

		foreach ($context['simple_actions'] as $action => $label)
			echo '
						<option value="', $action, '"', in_array($action, $context['SPortal']['block']['display']) ? ' selected="selected"' : '', '>', $label, '</option>';

		echo '
					</select>
					<div id="sp_display_advanced"', empty($context['SPortal']['block']['display_type']) ? ' style="display: none;"' : '', '>';

		$display_types = array('actions', 'boards', 'pages');
		foreach ($display_types as $type)
		{
			if (empty($context['display_' . $type]))
				continue;

			echo '
						<a href="javascript:void(0);" onclick="sp_collapseObject(\'', $type, '\')"><img id="sp_collapse_', $type, '" src="', $settings['images_url'], '/expand.gif" alt="*" /></a> ', $txt['sp-blocksSelect' . ucfirst($type)], '
						<ul id="sp_object_', $type, '" class="reset sp_display_list" style="display: none;">';

			foreach ($context['display_' . $type] as $index => $action)
			{
				echo '
							<li><input type="checkbox" name="display_', $type, '[]" id="', $type, $index, '" value="', $index, '"', in_array($index, $context['SPortal']['block']['display']) ? ' checked="checked"' : '', ' class="input_check" /> <label for="', $type, $index, '">', $action, '</label></li>';
		}

			echo '
							<li><input type="checkbox" onclick="invertAll(this, this.form, \'display_', $type, '[]\');" class="input_check" /> <em>', $txt['check_all'], '</em></li>
						</ul>
						<br />';
		}

		echo '
						<a href="', $scripturl, '?action=helpadmin;help=sp-blocksCustomDisplayOptions" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" class="icon" /></a> <label for="display_custom">', $txt['sp_display_custom'], ':</label> <input type="text" name="display_custom" id="display_custom" value="', $context['SPortal']['block']['display_custom'], '" class="input_text" />
					</div>
					<div class="sp_button_container">
						<input type="submit" name="add_block" value="', !$context['SPortal']['is_new'] ? $txt['sp-blocksEdit'] : $txt['sp-blocksAdd'], '" class="button_submit" />
					</div>
				</div>
				<span class="botslice"><span></span></span>
			</div>';
	}

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
					<a href="', $scripturl, '?action=helpadmin;help=sp-blocksStyleOptions" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" class="icon" /></a>
					', $txt['sp-blocksStyleOptions'], '
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
								<option value="', $class, '"', $context['SPortal']['block']['style'][$section . '_default_class'] == $class ? ' selected="selected"' : '', '>', $class, '</option>';

				echo '
							</select>';
			}
			else
				echo '
							<input type="text" name="', $section, '_custom_', $type, '" id="', $section, '_custom_', $type, '" value="', $context['SPortal']['block']['style'][$section . '_custom_' . $type], '" class="input_text" />';

			echo '
						</dd>';
		}

		echo '
						<dt>
							', $txt['sp-blocksNo' . ucfirst($section)], ':
						</dt>
						<dd>
							<input type="checkbox" name="no_', $section, '" id="no_', $section, '" value="1"', !empty($context['SPortal']['block']['style']['no_' . $section]) ? ' checked="checked"' : '', ' onclick="document.getElementById(\'', $section, '_default_class\').disabled', $section == 'title' ? ' = document.getElementById(\'title_custom_class\').disabled = document.getElementById(\'title_custom_style\').disabled' : '', ' = this.checked;" class="input_check" />
						</dd>
					</dl>';
	}

	echo '
					<script type="text/javascript"><!-- // --><![CDATA[
						document.getElementById("title_default_class").disabled = document.getElementById("no_title").checked;
						document.getElementById("title_custom_class").disabled = document.getElementById("no_title").checked;
						document.getElementById("title_custom_style").disabled = document.getElementById("no_title").checked;
						document.getElementById("body_default_class").disabled = document.getElementById("no_body").checked;
					// ]]></script>
					<div class="sp_button_container">
						<input type="submit" name="add_block" value="', !$context['SPortal']['is_new'] ? $txt['sp-blocksEdit'] : $txt['sp-blocksAdd'], '" class="button_submit" />
					</div>
				</div>
				<span class="botslice"><span></span></span>
			</div>
		</form>
	</div>
	<script type="text/javascript"><!-- // --><![CDATA[
		sp_update_permissions();

		function sp_update_permissions()
		{
			var new_state = document.getElementById("block_permission_set").value;
			document.getElementById("block_custom_permissions_label").style.display = new_state != 0 ? "none" : "";
			document.getElementById("block_custom_permissions_input").style.display = new_state != 0 ? "none" : "";
		}
	// ]]></script>';
}

function template_block_select_type()
{
	global $context, $scripturl, $settings, $txt;

	echo '
	<div id="sp_select_block_type">
		<div class="cat_bar">
			<h3 class="catbg">
				<a href="', $scripturl, '?action=helpadmin;help=sp-blocksSelectType" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" class="icon" /></a>
				', $txt['sp-blocksSelectType'], '
			</h3>
		</div>
		<form action="', $scripturl, '?action=admin;area=portalblocks;sa=add" method="post" accept-charset="', $context['character_set'], '">
			<table>
				<tr>';

	foreach($context['SPortal']['block_types'] as $index => $type)
	{
		if ($index != 0 && $index % 3 == 0)
		{
			echo '
				</tr>
				<tr>';
		}

		echo '
					<td>
						<div class="windowbg">
							<span class="topslice"><span></span></span>
							<div class="sp_content_padding">
								<input type="radio" name="selected_type[]" id="block_', $type['function'], '" value="', $type['function'], '" class="input_radio" /> <label for="block_', $type['function'], '"><strong>', $txt['sp_function_' . $type['function'] . '_label'], '</strong></label>
								<p class="smalltext">', $txt['sp_function_' . $type['function'] . '_desc'], '</p>
							</div>
							<span class="botslice"><span></span></span>
						</div>
					</td>';
	}

	echo '
				</tr>
			</table>
			<div class="windowbg2">
				<span class="topslice"><span></span></span>
				<div class="sp_center">
					<input type="submit" name="select_type" value="', $txt['sp-blocksSelectType'], '" class="button_submit" />
				</div>
				<span class="botslice"><span></span></span>
			</div>';

	if (!empty($context['SPortal']['block']['column']))
		echo '
			<input type="hidden" name="block_column" value="', $context['SPortal']['block']['column'], '" />';

	echo '
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
		</form>
	</div>';
}

?>